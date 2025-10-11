<?php

namespace Kanboard\Plugin\TaskLinkPolicies\Model;

class TaskModel extends \Kanboard\Model\TaskModel
{
    public function close($task_id, $force_close = false)
    {
        $policy = new PolicyModel($this->container);
        $reason = '';
        if (!$policy->canClose($task_id, $reason)) {
            // Show a friendly error to the user
            if (isset($this->container['flash'])) {
                $this->container['flash']->failure($reason ?: t('Policy violation: cannot close task.'));
            }
            $this->logger->info('TaskLinkPolicies: prevented closing task '.$task_id.' - '.$reason);
            return false;
        }

        $res = parent::close($task_id, $force_close);

        if ($res) {
            // Optionally auto-close duplicates
            $policy->handleDuplicatesOnClose($task_id);
        }

        return $res;
    }
}
