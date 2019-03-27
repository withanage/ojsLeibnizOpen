<?php

/**
 * @file plugins/oaiMetadataFormats/dcwgl/index.php
 *
 * Distributed under the GNU GPL v3
 *
 * @ingroup plugins_oaiMetadata
 * @brief Wrapper for the OAI WGL format plugin.
 *
 */

require_once('OAIMetadataFormatPlugin_DCWGL.inc.php');
require_once('OAIMetadataFormat_DCWGL.inc.php');

return new OAIMetadataFormatPlugin_DCWGL();


