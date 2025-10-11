<?php

namespace Kanboard\Plugin\TaskLinkPolicies;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        // Add settings panel under Project → Integrations, like official Slack plugin does
        // so admins can tweak the rules per project.
        $this->template->hook->attach('template:project:integrations', 'tasklinkpolicies:project/integration');

        // Register controller routes to save settings
        $this->route->addRoute('/project/:project_id/tasklink-policies/save', 'ProjectPolicyController', 'save', 'tasklinkpolicies');

        // Override core models to enforce policies before moves/closes
        // Pattern based on Kanboard docs: override container services with our own classes.
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
        return '0.1.0';
    }

    public function getCompatibleVersion()
    {
        // Tested against Kanboard >= 1.2.20, should work with newer versions.
        return '>=1.2.20';
    }

    public function getPluginHomepage()
    {
        return 'https://example.com/tasklinkpolicies';
    }
}
