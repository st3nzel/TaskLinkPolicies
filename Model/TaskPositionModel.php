<?php

namespace Kanboard\Plugin\TaskLinkPolicies\Model;

class TaskPositionModel extends \Kanboard\Model\TaskPositionModel
{
    public function movePosition($project_id, $task_id, $column_id, $position, $swimlane_id)
    {
        $this->logger->debug('TLP: movePosition project='.$project_id.' task='.$task_id.' dst_column='.$column_id.' pos='.$position.' swimlane='.$swimlane_id);
        $policy = new PolicyModel($this->container);
        $reason = '';
        if (!$policy->canMoveOutOfBacklog($task_id, $column_id, $reason)) {
            if (isset($this->container['flash'])) {
                $this->container['flash']->failure($reason ?: t('Policy violation: cannot move task out of Backlog.'));
            }
            $this->logger->info('TLP: prevented move of task '.$task_id.' - '.$reason);
            return false;
        }

        return parent::movePosition($project_id, $task_id, $column_id, $position, $swimlane_id);
    }
}
