<fieldset>
    <legend><i class="fa fa-gavel"></i>&nbsp;<?= t('Project Policies') ?></legend>
    <p><?= $this->url->link(t('Open Project Policies'), 'ProjectPolicyController', 'index', array('plugin' => 'TaskLinkPolicies', 'project_id' => $project['id']), false, 'btn') ?></p>
</fieldset>
