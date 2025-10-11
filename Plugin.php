<?php

namespace Kanboard\Plugin\TaskLinkPolicies;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;
use Kanboard\Core\Security\Role;

class Plugin extends Base
{
    public function initialize()
    {
        // UI: add a panel under Project → Integrations
        $this->template->hook->attach('template:project:integrations', 'tasklinkpolicies:project/integration');

        // Route to save settings (⚠️ plugin name case must match namespace for routing)
        $this->route->addRoute('/project/:project_id/tasklink-policies/save', 'ProjectPolicyController', 'save', 'TaskLinkPolicies');

        // Authorize only project managers to save
        $this->projectAccessMap->add('ProjectPolicyController', 'save', Role::PROJECT_MANAGER);

        // Override models to enforce policies
        $this->container['taskModel'] = $this->container->factory(function ($c) {
            return new \Kanboard\Plugin\TaskLinkPolicies\Model\TaskModel($c);
        });

        $this->container['taskPositionModel'] = $this->container->factory(function ($c) {
            return new \Kanboard\Plugin\TaskLinkPolicies\Model\TaskPositionModel($c);
        });
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
        return t('Enforce dependency logic based on internal task links (blocks, duplicates, parent/child, milestones, fixes) with per-project settings.');
    }

    public function getPluginAuthor()
    {
        return 'Kanboard AI Assistant';
    }

    public function getPluginVersion()
    {
        return '0.2.0';
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
