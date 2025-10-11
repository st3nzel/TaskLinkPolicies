<?php

namespace Kanboard\Plugin\TaskLinkPolicies\Model;

use Kanboard\Core\Base;
use Kanboard\Model\TaskModel as CoreTaskModel;

class PolicyModel extends Base
{

    protected function canBypass($project_id)
    {
        $allow = (int)$this->getProjectSetting($project_id, 'tlp_admin_can_override', 0) === 1;
        if (!$allow) return false;
        // Application admins or project managers can bypass
        if ($this->userSession->isAdmin()) return true;
        $user_id = $this->userSession->getId();
        return $this->projectPermissionModel->isManager($project_id, $user_id);
    }

    // Discover link ids by label/opposite label at runtime (labels are not translated in DB)
    public function getLinkTypeIds()
    {
        $map = array(
            'blocks' => null,
            'is blocked by' => null,
            'duplicates' => null,
            'is duplicated by' => null,
            'is a child of' => null,
            'is a parent of' => null,
            'targets milestone' => null,
            'is a milestone of' => null,
            'fixes' => null,
            'is fixed by' => null,
            'relates to' => null,
        );

        foreach ($this->linkModel->getAll() as $link) {
            $label = strtolower(trim($link['label']));
            $opp   = strtolower(trim($link['opposite_label']));
            foreach ($map as $wanted => $v) {
                if ($label === $wanted || $opp === $wanted) {
                    $map[$wanted] = (int) $link['id'];
                }
            }
        }
        return $map;
    }

    public function getProjectSetting($project_id, $key, $default = null)
    {
        $val = $this->projectMetadataModel->get($project_id, $key);
        return $val === null ? $default : $val;
    }

    public function getBacklogColumnId($project_id)
    {
        $mode = $this->getProjectSetting($project_id, 'tlp_backlog_column_mode', 'first');
        if ($mode === 'custom') {
            $cid = (int) $this->getProjectSetting($project_id, 'tlp_backlog_column_id', 0);
            if ($cid > 0) return $cid;
        }

        $columns = $this->columnModel->getAll($project_id);
        $first = null;
        foreach ($columns as $c) {
            if ($first === null || (int)$c['position'] < (int)$first['position']) {
                $first = $c;
            }
        }
        return $first ? (int) $first['id'] : 0;
    }

    public function isTaskClosed(array $task)
    {
        // is_active: 1=open, 0=closed
        return (int)$task['is_active'] === CoreTaskModel::STATUS_CLOSED || (int)$task['is_active'] === 0;
    }

    public function getCurrentTask($task_id)
    {
        return $this->taskModel->getById($task_id);
    }

    public function getLinkedTasksByType($task_id)
    {
        // Returns array keyed by link id with arrays of linked task rows
        $links = $this->taskLinkModel->getAll($task_id);
        $out = array();
        foreach ($links as $l) {
            $lid = (int)$l['link_id'];
            $other_task_id = (int)(($l['task_id'] == $task_id) ? $l['opposite_task_id'] : $l['task_id']);
            $other = $this->taskModel->getById($other_task_id);
            if ($other) {
                if (!isset($out[$lid])) $out[$lid] = array();
                $out[$lid][] = $other;
            }
        }
        return $out;
    }

    // --- Policy checks ------------------------------------------------------

    public function canMoveOutOfBacklog($task_id, $dst_column_id, &$reason = '')
    {
        $task = $this->getCurrentTask($task_id);
        if (empty($task)) return true;

        $project_id = (int)$task['project_id'];
        $backlog_id = $this->getBacklogColumnId($project_id);

        if ((int)$task['column_id'] === $backlog_id && (int)$dst_column_id !== $backlog_id) {
            if ($this->canBypass($project_id)) { return true; }
            // evaluate blockers
            if ((int)$this->getProjectSetting($project_id, 'tlp_enforce_blocker_move_out_backlog', 1) === 1) {
                $ids = $this->getLinkTypeIds();
                $block_lids = array_filter([$ids['blocks'], $ids['is blocked by']]);
                $linked = $this->getLinkedTasksByType($task_id);
                foreach ($block_lids as $lid) {
                    if (isset($linked[$lid])) {
                        foreach ($linked[$lid] as $other) {
                            // If "other" is still in backlog, block the move
                            if ((int)$other['column_id'] === $backlog_id) {
                                $reason = t('Task %s is blocked by %s still in Backlog.', '#'.$task_id, '#'.$other['id']);
                                return false;
                            }
                        }
                    }
                }
            }

            // Duplicates policy: disallow both active out of backlog
            $dup_policy = $this->getProjectSetting($project_id, 'tlp_duplicates_policy', 'allow');
            if ($dup_policy === 'disallow_both_active') {
                $ids = $this->getLinkTypeIds();
                $dup_lids = array_filter([$ids['duplicates'], $ids['is duplicated by']]);
                $linked = $this->getLinkedTasksByType($task_id);
                foreach ($dup_lids as $lid) {
                    if (isset($linked[$lid])) {
                        foreach ($linked[$lid] as $other) {
                            if ((int)$other['column_id'] !== $backlog_id && !$this->isTaskClosed($other)) {
                                $reason = t('Duplicate %s is already active; cannot activate both.', '#'.$other['id']);
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function canClose($task_id, &$reason = '')
    {
        $task = $this->getCurrentTask($task_id);
        if (empty($task)) return true;

        $project_id = (int)$task['project_id'];
        $ids = $this->getLinkTypeIds();
        $linked = $this->getLinkedTasksByType($task_id);

        if ($this->canBypass($project_id)) { return true; }

        // Blockers must be closed before the blocked task can be closed
        if ((int)$this->getProjectSetting($project_id, 'tlp_enforce_blocker_close', 1) === 1) {
            foreach (array_filter([$ids['blocks'], $ids['is blocked by']]) as $lid) {
                if (isset($linked[$lid])) {
                    foreach ($linked[$lid] as $other) {
                        if (!$this->isTaskClosed($other)) {
                            $reason = t('Task %s is blocked by %s not closed yet.', '#'.$task_id, '#'.$other['id']);
                            return false;
                        }
                    }
                }
            }
        }

        // Parent requires children closed
        if ((int)$this->getProjectSetting($project_id, 'tlp_parent_requires_children_closed', 1) === 1) {
            foreach (array_filter([$ids['is a parent of']]) as $lid) {
                if (isset($linked[$lid])) {
                    foreach ($linked[$lid] as $child) {
                        if (!$this->isTaskClosed($child)) {
                            $reason = t('Parent cannot be closed: child %s still open.', '#'.$child['id']);
                            return false;
                        }
                    }
                }
            }
        }

        // Milestone requires targets closed
        if ((int)$this->getProjectSetting($project_id, 'tlp_milestone_requires_targets_closed', 1) === 1) {
            foreach (array_filter([$ids['is a milestone of']]) as $lid) {
                if (isset($linked[$lid])) {
                    foreach ($linked[$lid] as $target) {
                        if (!$this->isTaskClosed($target)) {
                            $reason = t('Milestone cannot be closed: target %s still open.', '#'.$target['id']);
                            return false;
                        }
                    }
                }
            }
        }

        // "is fixed by" must be closed
        if ((int)$this->getProjectSetting($project_id, 'tlp_fix_requires_fix_task_closed', 1) === 1) {
            foreach (array_filter([$ids['is fixed by']]) as $lid) {
                if (isset($linked[$lid])) {
                    foreach ($linked[$lid] as $fix) {
                        if (!$this->isTaskClosed($fix)) {
                            $reason = t('Cannot close: linked fix %s is not closed.', '#'.$fix['id']);
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function handleDuplicatesOnClose($task_id)
    {
        $task = $this->getCurrentTask($task_id);
        if (empty($task)) return;

        $policy = $this->getProjectSetting($task['project_id'], 'tlp_duplicates_policy', 'allow');
        if ($policy !== 'close_together') {
            return;
        }

        $ids = $this->getLinkTypeIds();
        $linked = $this->getLinkedTasksByType($task_id);

        if ($this->canBypass($project_id)) { return true; }
        foreach (array_filter([$ids['duplicates'], $ids['is duplicated by']]) as $lid) {
            if (isset($linked[$lid])) {
                foreach ($linked[$lid] as $other) {
                    if (!$this->isTaskClosed($other)) {
                        // silently close the duplicate
                        $this->taskModel->close($other['id']);
                    }
                }
            }
        }
    }
}
