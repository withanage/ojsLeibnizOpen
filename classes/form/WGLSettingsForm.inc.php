<?php

/**
 * @file plugins/oaiMetadataFormats/dcwgl/WGLSettingsForm.inc.php
 *
 * Distributed under the GNU GPL v3

 */

import('lib.pkp.classes.form.Form');

/**
 * Class WGLSettingsForm
 */
class WGLSettingsForm extends Form
{

	/**
	 * WGLSettingsForm constructor.
	 * @param $plugin
	 */
	/***	 * @var context	 */
	private $_contextId;

	/**
	 * @return context
	 */
	function _getContextId() {
		return $this->_contextId;
	}
	/** @var DOIPubIdPlugin */
	var $_plugin;

	/**
	 * Get the plugin.
	 * @return DOIPubIdPlugin
	 */
	function _getPlugin() {
		return $this->_plugin;
	}


	function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('WGLSettingsForm.tpl'));
		$this->setData('pluginName', $plugin->getName());
	}

	function initData()
	{
		$plugin = $this->_getPlugin();
		$contextId =  $this->_getContextId();
		$wglSettings = $plugin->getSetting($contextId, 'wglSettings');
		if (isset($wglSettings) & !empty($wglSettings)) {
			$this->setData('wglSettings', $wglSettings);
		}
		else {
			$this->setData('wglSettings', '');
		}
	}

	function readInputData()
	{
		$this->readUserVars(array('wglSettings'));
	}


	function execute()
	{
		$plugin = $this->_plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$plugin->updateSetting($contextId, 'wglSettings', $this->getData('wglSettings'));
	}
	function manage($args, $request) {
		$plugin = $this->getAuthorizedContextObject(ASSOC_TYPE_PLUGIN);
		return $plugin->manage($args, $request);
	}

	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		return parent::fetch($request);
	}




}
