<?php

/**
 * @file plugins/oaiMetadataFormats/wgl/OAIMetadataFormatPlugin_DCWGL.inc.php
 *
 * Distributed under the GNU GPL v3
 *
 * @class OAIMetadataFormatPlugin_WGL
 * @ingroup oai_format
 * @see OAI
 *
 * @brief wgl metadata format plugin for OAI.
 */

import('lib.pkp.classes.plugins.OAIMetadataFormatPlugin');

class OAIMetadataFormatPlugin_DCWGL extends OAIMetadataFormatPlugin {
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'OAIMetadataFormatPlugin_DCWGL';
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.OAIMetadata.wgl.displayName');
	}
	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.OAIMetadata.wgl.description');
	}

	/**
	 * Determine whether the plugin can be disabled.
	 * @return boolean
	 */
	function getCanDisable() {
		return true;
	}

	/**
	 * Determine whether the plugin can be enabled.
	 * @return boolean
	 */
	function getCanEnable() {
		return true;
	}

	/**
	 * Determine whether the plugin is enabled.
	 * @return boolean
	 */
	function getEnabled() {
		$request = PKPApplication::getRequest();
		if (!$request) return false;
		$context = $request->getContext();
		if (!$context) return false;
		return $this->getSetting($context->getId(), 'enabled');
	}

	/**
	 * Set whether the plugin is enabled.
	 * @param $enabled boolean
	 */
	function setEnabled($enabled) {
		$request = PKPApplication::getRequest();
		$context = $request->getContext();
		$this->updateSetting($context->getId(), 'enabled', $enabled, 'bool');
	}


	function getFormatClass() {
		return 'OAIMetadataFormat_DCWGL';
	}

	static function getMetadataPrefix() {
		return 'oai_wgl';
	}

	static function getSchema() {
		return 'http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl/oai_wgl.xsd';
	}

	static function getNamespace() {
		return 'http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl/';
	}



	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $actionArgs) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array_merge($actionArgs, array('verb' => 'settings'))),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $actionArgs)
		);
	}

	function manage($args, $request) {
		$this->import('WGLSettingsForm');
		$context = Request::getContext();
		switch($request->getUserVar('verb')) {
			case 'settings':
				$settingsForm = new WGLSettingsForm($this,$context->getId());
				$settingsForm->initData();
				return new JSONMessage(true, $settingsForm->fetch($request));
			case 'save':
				$settingsForm = new WGLSettingsForm($this,$context->getId());
				$settingsForm->readInputData();
				if ($settingsForm->validate()) {
					$settingsForm->execute();
					$notificationManager = new NotificationManager();
					$notificationManager->createTrivialNotification(
						$request->getUser()->getId(),
						NOTIFICATION_TYPE_SUCCESS,
						array('contents' => __('plugins.OAIMetadata.wgl.settings.saved'))
					);
					return new JSONMessage(true);
				}
				return new JSONMessage(true, $settingsForm->fetch($request));
		}
		return parent::manage($args, $request);
	}


}


