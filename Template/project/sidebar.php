<li <?= $this->app->checkMenuSelection('ProjectPolicyController', 'index') ? 'class="active"' : '' ?>>
    <?= $this->url->icon('gavel', t('Project Policies'), 'ProjectPolicyController', 'index', array('plugin' => 'TaskLinkPolicies', 'project_id' => $project['id'])) ?>
</li>
