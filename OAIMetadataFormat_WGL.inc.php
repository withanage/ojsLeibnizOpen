<?php

/**
 * @file plugins/oaiMetadataFormats/wgl/OAIMetadataFormat_WGL.inc.php
 *
 * Distributed under the GNU GPL v3
 *
 * @class OAIMetadataFormat_WGL
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- WGL.
 */

import('lib.pkp.plugins.oaiMetadataFormats.dc.PKPOAIMetadataFormat_DC');


/**
 * Class OAIMetadataFormat_WGL  Creates a Leibniz-Open XML File
 */
class OAIMetadataFormat_WGL extends PKPOAIMetadataFormat_DC
{
	/**
	 * @see lib/pkp/plugins/oaiMetadataFormats/dc/PKPOAIMetadataFormat_DC::toXml()
	 *
	 * @param $record
	 * @param $format
	 * @return string
	 */
	public function toXml($record, $format = null) {
		$submission = $record->getData('article');
		$submission = (!empty($submission)) ? $submission : $record->getData('monograph');

		$publicationFormat = $record->getData('publicationFormat');
		$doc = parent::toXml($publicationFormat);
		$dom = DOMDocument::loadXML($doc);
		$dom->formatOutput = true;
		$dom->encoding ='UTF-8';


		$siteAgencies = $this->_getSiteAgencies($submission);

		$submissionAgencies = $this->_getSubmissionAgencies();

		$isLeibnizAgency = false;
		$leibnizAgency = array();

		if (isset($submissionAgencies) & !empty($submissionAgencies)) {
			$leibnizAgencies = explode('|', $submissionAgencies);
			foreach ($leibnizAgencies as $agency) {
				$agency = explode(':', $agency);
				foreach ($siteAgencies as $agenciesInSubmission) {
					foreach ($agenciesInSubmission as $agencyInSubmission) {
						if (trim($agency[0]) == $agencyInSubmission) {
							$isLeibnizAgency = true;
							$leibnizAgency = $agency;
						}
					}
				}
			}
		}


		if ($isLeibnizAgency) {
			$xpath = new DOMXPath($dom);

			$wgl = $this->_createWGLElement($dom);

			$wgl = $this->_renameDCElements($xpath, $dom, $wgl);

			$wgl = $this->_setWGLType($submission, $dom, $wgl);

			$contributorElement = $dom->createElement('wgl:wglcontributor', trim($leibnizAgency[0]));
			$wgl->appendChild($contributorElement);

			$subjectElement = $dom->createElement('wgl:wglsubject', trim($leibnizAgency[1]));
			$wgl->appendChild($subjectElement);

			$this->_deleteDCElements($xpath);

			$dom->appendChild($wgl);
		}
		return $dom->saveXML($dom->documentElement);


	}

	/**
	 * @param $node DOMElement
	 * @param $doc DOMDocument
	 * @param $prefix string
	 * @return DOMElement
	 */
	public function renameNamespace($node, $doc, $prefix) {
		$prefixedName = preg_replace('/.*:/', $prefix . ':', $node->nodeName);
		$newElement = $doc->createElement($prefixedName);

		foreach ($node->attributes as $value)
			$newElement->setAttribute($value->nodeName, $value->value);

		if (!$node->childNodes)
			return $newElement;


		foreach ($node->childNodes as $child) {
			if ($child->nodeName == "#text")
				$newElement->appendChild($doc->createTextNode($child->nodeValue));
			else
				$newElement->appendChild($this->renameNamespace($child, $doc, $prefix));
		}

		return $newElement;
	}

	/**
	 * @param $submission
	 * @return mixed
	 */
	protected function _getSiteAgencies($submission) {
		$site = Application::getRequest()->getSite();
		$submissionAgencyDao = DAORegistry::getDAO('SubmissionAgencyDAO');
		$siteSupportedLocales = $site->getSupportedLocales();
		$agencies = $submissionAgencyDao->getAgencies($submission->getId(), $siteSupportedLocales);
		return $agencies;
	}

	/**
	 * @return mixed
	 */
	protected function _getSubmissionAgencies() {
		$context = Request::getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$plugin = PluginRegistry::getPlugin('oaiMetadataFormats', 'OAIFormatPlugin_WGL');
		$pluginSettings = $plugin->getSetting($contextId, 'wglSettings');
		return $pluginSettings;
	}

	/**
	 * @param DOMXPath $xpath
	 * @param $dom
	 * @param $ne
	 * @return mixed
	 */
	protected function _renameDCElements(DOMXPath $xpath, $dom, $ne)
	{
		$dcElements = $xpath->query("//oai_dc:dc/*");
		foreach ($dcElements as $node) {
			$clone = $this->renameNamespace($node, $dom, 'wgl');
			$ne->appendChild($clone);

		}
		return $ne;
	}

	/**
	 * @param $submission
	 * @param $dom
	 * @param $ne
	 */
	protected function _setWGLType($submission, $dom, $ne)
	{
		if (is_a($submission, 'PublishedArticle')) {
			$extraElement = $dom->createElement('wgl:wgltype', 'Zeitschriftenartikel');
			$ne->appendChild($extraElement);
		} elseif (is_a($submission, 'publishedMonograph')) {
			$extraElement = $dom->createElement('wgl:wgltype', 'Monographie');
			$ne->appendChild($extraElement);
		}
		return $ne;
	}

	/**
	 * @param DOMXPath $xpath
	 */
	protected function _deleteDCElements(DOMXPath $xpath)
	{
		$oaiDCElement = $xpath->query("//oai_dc:dc")->item(0);
		$getParent = $oaiDCElement->parentNode;
		$getParent->removeChild($oaiDCElement);
	}

	/**
	 * @param $dom
	 * @return mixed
	 */
	protected function _createWGLElement($dom)
	{
		$ne = $dom->createElementNS('http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl', 'oai_wgl:wgl');
		$ne->setAttribute('xmlns:wgl', 'http://www.leibnizopen.de/fileadmin/default/documents/wgl_dc');
		$ne->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$ne->setAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
		return $ne;
	}
}


