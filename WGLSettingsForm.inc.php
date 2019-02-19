<?php

/**
 * @file plugins/oaiMetadataFormats/wgl/WGLSettingsForm.inc.php
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
	function __construct($plugin)
	{
		$this->plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('WGLSettingsForm.tpl'));
		$this->setData('pluginName', $plugin->getName());
	}

	function initData()
	{
		$plugin = $this->plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$this->setData('wglSettings', $plugin->getSetting($contextId, 'wglSettings'));
	}

	function readInputData()
	{
		$this->readUserVars(array('wglSettings'));
	}


	function execute()
	{
		$plugin = $this->plugin;
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$plugin->updateSetting($contextId, 'wglSettings', $this->getData('wglSettings'));
	}

	function fetch($request)
	{
		return parent::fetch($request);

	}


}
