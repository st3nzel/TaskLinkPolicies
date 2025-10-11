<?php

namespace Kanboard\Plugin\TaskLinkPolicies\Controller;

use Kanboard\Controller\BaseController;

class ProjectPolicyController extends BaseController
{
    public function save()
    {
        $project = $this->getProject();
        $project_id = $project['id'];

        $values = $this->request->getValues();

        $settings = array(
            'tlp_enforce_blocker_move_out_backlog' => empty($values['tlp_enforce_blocker_move_out_backlog']) ? 0 : 1,
            'tlp_enforce_blocker_close'            => empty($values['tlp_enforce_blocker_close']) ? 0 : 1,
            'tlp_parent_requires_children_closed'  => empty($values['tlp_parent_requires_children_closed']) ? 0 : 1,
            'tlp_milestone_requires_targets_closed'=> empty($values['tlp_milestone_requires_targets_closed']) ? 0 : 1,
            'tlp_fix_requires_fix_task_closed'     => empty($values['tlp_fix_requires_fix_task_closed']) ? 0 : 1,
            'tlp_duplicates_policy'                => isset($values['tlp_duplicates_policy']) ? $values['tlp_duplicates_policy'] : 'allow',
            'tlp_backlog_column_mode'              => isset($values['tlp_backlog_column_mode']) ? $values['tlp_backlog_column_mode'] : 'first',
            'tlp_backlog_column_id'                => isset($values['tlp_backlog_column_id']) ? (int) $values['tlp_backlog_column_id'] : 0,
            'tlp_admin_can_override'               => empty($values['tlp_admin_can_override']) ? 0 : 1,
        );

        if ($this->projectMetadataModel->save($project_id, $settings)) {
            $this->flash->success(t('Task Link Policies saved.'));
        } else {
            $this->flash->failure(t('Unable to save Task Link Policies.'));
        }

        return $this->response->redirect($this->helper->url->to('ProjectViewController', 'integrations', array('project_id' => $project_id), false, '', 'integrations'), true);
    }
}
