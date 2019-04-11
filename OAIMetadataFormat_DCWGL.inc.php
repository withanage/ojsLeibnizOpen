<?php

/**
 * @file plugins/oaiMetadataFormats/dcwgl/OAIMetadataFormat_DCWGL.inc.php
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
class OAIMetadataFormat_DCWGL extends PKPOAIMetadataFormat_DC
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
		$doc = (!empty($publicationFormat)) ?$publicationFormat: $submission ;

		$dom = DOMDocument::loadXML( parent::toXml($doc));
		$dom->formatOutput = true;
		$dom->encoding = 'UTF-8';


		$siteAgencies = $this->_getSiteAgencies($submission);

		$submissionAgencies = $this->_getSubmissionAgencies();

		$isLeibnizAgency = false;
		$leibnizAgency = array();

		if (isset($submissionAgencies) & !empty($submissionAgencies)) {
			$leibnizAgencies = explode('|', $submissionAgencies);
			foreach ($leibnizAgencies as $agency) {
				$agency = explode(':', trim($agency));
				foreach ($siteAgencies as $agenciesInSubmission) {
					foreach ($agenciesInSubmission as $agencyInSubmission) {
						if (trim($agency[0]) == trim($agencyInSubmission)) {
							$isLeibnizAgency = true;
							$leibnizAgency = $agency;
							break;
						}
					}
				}
			}
		}


		if ($isLeibnizAgency) {
			$xpath = new DOMXPath($dom);

			$wgl = $dom->createElementNS('http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl', 'oai_wgl:wgl');

			$wgl = $this->_renameDCElements($xpath, $dom, $wgl);

			$wgl = $this->_setWGLType($submission, $dom, $wgl);

			$contributorElement = $dom->createElement('wgl:wglcontributor', trim($leibnizAgency[0]));
			$wgl->appendChild($contributorElement);

			$subjectElement = $dom->createElement('wgl:wglsubject', trim($leibnizAgency[1]));
			$wgl->appendChild($subjectElement);

			$this->_deleteDCElements($xpath);

			$dom->appendChild($wgl);

			$wglString = $dom->saveXML($wgl);
			$wglString = $this->_cleanWGLString($wglString);

			return $wglString;
		}


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
		$plugin = PluginRegistry::getPlugin('oaiMetadataFormats', 'OAIMetadataFormatPlugin_DCWGL');
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
	 * @param $wglString
	 * @return string
	 */
	private function _cleanWGLString($wglString)
	{
		$wglNamespace = "<oai_wgl:wgl" .
			"\txmlns:wgl=\"http://www.leibnizopen.de/fileadmin/default/documents/wgl_dc/\"" .
			"\txmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"" .
			"\txsi:schemaLocation=\"http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl/ http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl/oai_wgl.xsd\"";

		$wglString = str_replace('<oai_wgl:wgl', $wglNamespace, $wglString);
		$wglString = str_replace('xml:lang="de-DE"', 'xml:lang="de"', $wglString);
		$wglString = str_replace('xml:lang="en-US"', 'xml:lang="en"', $wglString);
		return $wglString;
	}

}


