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
class OAIMetadataFormat_WGL extends PKPOAIMetadataFormat_DC {
	/**
	  * @see lib/pkp/plugins/oaiMetadataFormats/dc/PKPOAIMetadataFormat_DC::toXml()
	 *
	 * @param $record
	 * @param $format
	 * @return string
	 */
	function toXml($record, $format = null)
	{

		$submission = $record->getData('article');
		$submission = (!empty($submission)) ? $submission: $record->getData('monograph') ;

		$publicationFormat = $record->getData('publicationFormat');
		$doc = parent::toXml($publicationFormat);
		$dom = DOMDocument::loadXML($doc);
		$dom->formatOutput = true;
		$xpath = new DOMXPath($dom);

		$newElement = $dom->createElementNS('http://www.leibnizopen.de/fileadmin/default/documents/oai_wgl', 'oai_wgl:wgl');
		$newElement->setAttribute('xmlns:wgl','http://www.leibnizopen.de/fileadmin/default/documents/wgl_dc');
		$newElement->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
		$newElement->setAttribute('xsi:schemaLocation','http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');

		// copy modified DC elements
		$dcElements = $xpath->query("//oai_dc:dc/*");
		foreach($dcElements as $node) {
			$clone = $this->renameNamespace($node, $dom, 'wgl');
			$newElement->appendChild($clone);

		}

		// add wgl elements
		$extraElements = array(
			'wgl:wglcontributor'=>'contributor',
			'wgl:wglsubject'=>$submission->getLocalizedSubject()
		);
		foreach($extraElements as $key=>$value){
			$extraElement = $dom->createElement($key,$value);
			$newElement->appendChild($extraElement);
		}
		if (is_a($submission,'PublishedArticle')){
			$extraElement = $dom->createElement('wgl:wgltype','Zeitschriftenartikel');
			$newElement->appendChild($extraElement);
		}
		elseif (is_a($submission,'publishedMonograph')){
			$extraElement = $dom->createElement('wgl:wgltype','Monographie');
			$newElement->appendChild($extraElement);
		}


		// remove old DC header
		$oaiDCElement = $xpath->query("//oai_dc:dc")->item(0);
		$getParent = $oaiDCElement->parentNode;
		$getParent->removeChild($oaiDCElement);

		$dom->appendChild($newElement);

		return $dom->saveXML();
	}

	/**
	 * @param $node DOMElement
	 * @param $doc DOMDocument
	 * @param $prefix string
	 * @return DOMElement
	 */
	function renameNamespace($node, $doc, $prefix)
	{
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
}


