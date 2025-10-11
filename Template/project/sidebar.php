<li <?= $this->app->checkMenuSelection('ProjectPolicyController','index') ? 'class="active"' : '' ?>>
  <?= $this->url->icon('gavel', t('Project Policies'),
        'ProjectPolicyController', 'index',
        ['project_id' => $project['id'], 'plugin' => 'TaskLinkPolicies']) ?>
</li>
