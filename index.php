<?php

/**
 * @file plugins/oaiMetadataFormats/wgl/index.php
 *
 * Distributed under the GNU GPL v3
 *
 * @ingroup plugins_oaiMetadata
 * @brief Wrapper for the OAI WGL format plugin.
 *
 */

require_once('OAIMetadataFormatPlugin_WGL.inc.php');
require_once('OAIMetadataFormat_WGL.inc.php');

return new OAIMetadataFormatPlugin_WGL();


