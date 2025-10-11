<?php if (! isset($project) || empty($project)): ?>
    <p class="alert alert-error"><?= t('Project not found.') ?></p>
<?php else: ?>
<div class="page-header">
    <h2><i class="fa fa-gavel"></i> <?= t('Project Policies') ?></h2>
</div>

<div class="panel">
    <div class="panel-heading"><?= t('Task Link Policies') ?></div>
    <div class="panel-body">
        <form method="post" action="<?= $this->url->href('ProjectPolicyController', 'save', array('plugin' => 'TaskLinkPolicies', 'project_id' => $project['id'])) ?>" autocomplete="off">
            <?= $this->form->csrf() ?>

            <fieldset>
                <legend><?= t('Blocking & Dependencies') ?></legend>
                <?= $this->form->checkbox('tlp_enforce_blocker_move_out_backlog', t('Blockers must leave Backlog before blocked task can leave Backlog'), 1, (bool) $this->projectMetadataModel->get($project['id'], 'tlp_enforce_blocker_move_out_backlog', 1)) ?>
                <?= $this->form->checkbox('tlp_enforce_blocker_close', t('Blockers must be closed before blocked task can be closed'), 1, (bool) $this->projectMetadataModel->get($project['id'], 'tlp_enforce_blocker_close', 1)) ?>
                <?= $this->form->checkbox('tlp_parent_requires_children_closed', t('Parent tasks require all children to be closed before closing'), 1, (bool) $this->projectMetadataModel->get($project['id'], 'tlp_parent_requires_children_closed', 1)) ?>
                <?= $this->form->checkbox('tlp_milestone_requires_targets_closed', t('Milestones require all target tasks to be closed before closing'), 1, (bool) $this->projectMetadataModel->get($project['id'], 'tlp_milestone_requires_targets_closed', 1)) ?>
                <?= $this->form->checkbox('tlp_fix_requires_fix_task_closed', t('“Is fixed by” tasks must be closed before closing the linked issue'), 1, (bool) $this->projectMetadataModel->get($project['id'], 'tlp_fix_requires_fix_task_closed', 1)) ?>
            </fieldset>

            <fieldset>
                <legend><?= t('Duplicates & Backlog') ?></legend>
                <?= $this->form->label(t('Duplicates policy'), 'tlp_duplicates_policy') ?>
                <?= $this->form->select('tlp_duplicates_policy', array(
                    'allow' => t('Allow'),
                    'disallow_both_active' => t('Disallow both active (only one may leave Backlog)'),
                    'close_together' => t('Auto-close duplicates together'),
                ), $this->projectMetadataModel->get($project['id'], 'tlp_duplicates_policy', 'allow')) ?>

                <?= $this->form->label(t('Backlog column'), 'tlp_backlog_column_mode') ?>
                <?= $this->form->select('tlp_backlog_column_mode', array(
                    'first' => t('First column (default)'),
                    'custom' => t('Custom column id'),
                ), $this->projectMetadataModel->get($project['id'], 'tlp_backlog_column_mode', 'first')) ?>
                <?= $this->form->number('tlp_backlog_column_id', array('value' => (int) $this->projectMetadataModel->get($project['id'], 'tlp_backlog_column_id', 0))) ?>
                <p class="form-help"><?= t('If custom, enter the numeric Column ID to treat as “Backlog”.') ?></p>

                <?= $this->form->checkbox('tlp_admin_can_override', t('Allow application managers to bypass policies'), 1, (bool) $this->projectMetadataModel->get($project['id'], 'tlp_admin_can_override', 0)) ?>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="btn btn-blue"><?= t('Save') ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
