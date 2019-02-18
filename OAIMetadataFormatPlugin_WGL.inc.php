<?php

/**
 * @file plugins/oaiMetadataFormats/wgl/OAIMetadataFormatPlugin_WGL.inc.php
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

class OAIMetadataFormatPlugin_WGL extends OAIMetadataFormatPlugin {
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'OAIFormatPlugin_WGL';
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
		return 'OAIMetadataFormat_WGL';
	}

	static function getMetadataPrefix() {
		return 'wgl';
	}

	static function getSchema() {
		return 'http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl/oai_wgl.xsd';
	}

	static function getNamespace() {
		return 'http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl/';
	}
}


