<?php

/**
 * @file plugins/generic/pln/PLNGatewayPlugin.inc.php
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PLNGatewayPlugin
 * @ingroup plugins_generic_pln
 *
 * @brief Gateway component of web feed plugin
 *
 */

import('classes.plugins.GatewayPlugin');
import('lib.pkp.classes.site.VersionCheck');
import('lib.pkp.classes.db.DBResultRange');

class PLNGatewayPlugin extends GatewayPlugin {
	/** @var $parentPluginName string Name of parent plugin */
	var $parentPluginName;

	function PLNGatewayPlugin($parentPluginName) {
		parent::GatewayPlugin();
		$this->parentPluginName = $parentPluginName;
	}

	/**
	 * Hide this plugin from the management interface (it's subsidiary)
	 */
	function getHideManagement() {
		return true;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'PLNGatewayPlugin';
	}

	function getDisplayName() {
		return __('plugins.generic.plngateway.displayName');
	}

	function getDescription() {
		return __('plugins.generic.plngateway.description');
	}

	/**
	 * Get the web feed plugin
	 * @return object
	 */
	function &getPLNPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin;
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 */
	function getPluginPath() {
		$plugin =& $this->getPLNPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * Override the builtin to get the correct template path.
	 * @return string
	 */
	function getTemplatePath() {
		$plugin =& $this->getPLNPlugin();
		return $plugin->getTemplatePath();
	}

	/**
	 * Get whether or not this plugin is enabled. (Should always return true, as the
	 * parent plugin will take care of loading this one when needed)
	 * @return boolean
	 */
	function getEnabled() {
		$plugin =& $this->getPLNPlugin();
		return $plugin->getEnabled(); // Should always be true anyway if this is loaded
	}

	/**
	 * Get the management verbs for this plugin (override to none so that the parent
	 * plugin can handle this)
	 * @return array
	 */
	function getManagementVerbs() {
		return array();
	}
        
	/**
	 * Handle fetch requests for this plugin.
	 */
	function fetch() {
		$templateMgr =& TemplateManager::getManager();
                
		$journal =& Request::getJournal();
                $templateMgr->assign_by_ref('journal', $journal);

                $pluginVersionFile = $this->getPluginPath() . DIRECTORY_SEPARATOR . '/version.xml';
                $pluginVersion =& VersionCheck::parseVersionXml($pluginVersionFile);
                $templateMgr->assign_by_ref('pluginVersion', $pluginVersion);
                
		$versionDao =& DAORegistry::getDAO('VersionDAO');
		$ojsVersion =& $versionDao->getCurrentVersion();
		$templateMgr->assign('ojsVersion', $ojsVersion->getVersionString());
                
                $publishedArticlesDAO =& DAORegistry::getDAO('PublishedArticleDAO');
                $range = new DBResultRange(12, 1);
                $publishedArticles =& $publishedArticlesDAO->getPublishedArticlesByJournalId($journal->getId(), $range,  true);
                $templateMgr->assign_by_ref('articles', $publishedArticles);

		$templateMgr->display($this->getTemplatePath() . DIRECTORY_SEPARATOR . 'ping.tpl', 'text/xml');

		return true;
	}
}

?>
