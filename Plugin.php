<?php

namespace Kanboard\Plugin\TaskLinkPolicies;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;
use Kanboard\Core\Security\Role;

class Plugin extends Base
{
    public function initialize()
    {
        // Add our own Project Settings tab entry
        $this->template->hook->attach('template:project:sidebar', 'taskLinkPolicies:project/sidebar');
        // Keep the old Integrations hook as a fallback (some users expect it there)
        $this->template->hook->attach('template:project:integrations', 'taskLinkPolicies:project/integration');

        // Routes
        $this->route->addRoute('/project/:project_id/policies', 'ProjectPolicyController', 'index', 'TaskLinkPolicies');
        $this->route->addRoute('/project/:project_id/tasklink-policies/save', 'ProjectPolicyController', 'save', 'TaskLinkPolicies');

        // ACL: only project managers can change settings
        $this->projectAccessMap->add('ProjectPolicyController', 'index', Role::PROJECT_MANAGER);
        $this->projectAccessMap->add('ProjectPolicyController', 'save', Role::PROJECT_MANAGER);

        // Override models
        $this->container['taskModel'] = $this->container->factory(function ($c) {
            return new \Kanboard\Plugin\TaskLinkPolicies\Model\TaskModel($c);
        });
        $this->container['taskPositionModel'] = $this->container->factory(function ($c) {
            return new \Kanboard\Plugin\TaskLinkPolicies\Model\TaskPositionModel($c);
        });

        // Small CSS to make our panel tidy (optional)
        //$this->template->hook->attach('template:layout:head', 'taskLinkPolicies:assets/head');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'TaskLinkPolicies';
    }

    public function getPluginDescription()
    {
        return t('Enforce dependency logic based on internal task links (blocks, duplicates, parent/child, milestones, fixes) with per-project settings and a dedicated Project Policies tab.');
    }

    public function getPluginAuthor()
    {
        return 'Kanboard AI Assistant';
    }

    public function getPluginVersion()
    {
        return '0.3.0';
    }

    public function getCompatibleVersion()
    {
        return '>=1.2.20';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/st3nzel/TaskLinkPolicies';
    }
}
