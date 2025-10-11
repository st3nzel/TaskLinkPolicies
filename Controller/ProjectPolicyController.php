<?php
namespace Kanboard\Plugin\TaskLinkPolicies\Controller;

use Kanboard\Controller\BaseController;

class ProjectPolicyController extends BaseController
{
    public function index()
    {
        $project = $this->getProject();

        // Werte VORAB laden (keine Model-Aufrufe im Template)
        $settings = [
            'tlp_enforce_blocker_move_out_backlog' => (int) $this->projectMetadataModel->get($project['id'], 'tlp_enforce_blocker_move_out_backlog', 1),
            'tlp_enforce_blocker_close'            => (int) $this->projectMetadataModel->get($project['id'], 'tlp_enforce_blocker_close', 1),
            'tlp_parent_requires_children_closed'  => (int) $this->projectMetadataModel->get($project['id'], 'tlp_parent_requires_children_closed', 1),
            'tlp_milestone_requires_targets_closed'=> (int) $this->projectMetadataModel->get($project['id'], 'tlp_milestone_requires_targets_closed', 1),
            'tlp_fix_requires_fix_task_closed'     => (int) $this->projectMetadataModel->get($project['id'], 'tlp_fix_requires_fix_task_closed', 1),
            'tlp_duplicates_policy'                => (string) $this->projectMetadataModel->get($project['id'], 'tlp_duplicates_policy', 'allow'),
            'tlp_backlog_column_mode'              => (string) $this->projectMetadataModel->get($project['id'], 'tlp_backlog_column_mode', 'first'),
            'tlp_backlog_column_id'                => (int) $this->projectMetadataModel->get($project['id'], 'tlp_backlog_column_id', 0),
            'tlp_admin_can_override'               => (int) $this->projectMetadataModel->get($project['id'], 'tlp_admin_can_override', 0),
        ];

        // >>> WICHTIG: Projekt-Layout, nicht raw rendern <<<
        $this->response->html(
            $this->helper->layout->project(
                'taskLinkPolicies:project/policies',
                [
                    'project'  => $project,
                    'settings' => $settings,
                    'title'    => t('Project Policies'),
                ],
                'project/sidebar' // ← Sidebar explizit (Default ist zwar auch 'project/sidebar')
            )
        );

    }

    public function save()
    {
        $project = $this->getProject();
        $v = $this->request->getValues();

        $toSave = [
            'tlp_enforce_blocker_move_out_backlog' => empty($v['tlp_enforce_blocker_move_out_backlog']) ? 0 : 1,
            'tlp_enforce_blocker_close'            => empty($v['tlp_enforce_blocker_close']) ? 0 : 1,
            'tlp_parent_requires_children_closed'  => empty($v['tlp_parent_requires_children_closed']) ? 0 : 1,
            'tlp_milestone_requires_targets_closed'=> empty($v['tlp_milestone_requires_targets_closed']) ? 0 : 1,
            'tlp_fix_requires_fix_task_closed'     => empty($v['tlp_fix_requires_fix_task_closed']) ? 0 : 1,
            'tlp_duplicates_policy'                => $v['tlp_duplicates_policy'] ?? 'allow',
            'tlp_backlog_column_mode'              => $v['tlp_backlog_column_mode'] ?? 'first',
            'tlp_backlog_column_id'                => (int) ($v['tlp_backlog_column_id'] ?? 0),
            'tlp_admin_can_override'               => empty($v['tlp_admin_can_override']) ? 0 : 1,
        ];

        $this->projectMetadataModel->save($project['id'], $toSave);
        $this->flash->success(t('Settings saved successfully.'));

        return $this->response->redirect($this->helper->url->to(
            'ProjectPolicyController', 'index',
            ['project_id' => $project['id'], 'plugin' => 'TaskLinkPolicies']
        ));
    }
}
