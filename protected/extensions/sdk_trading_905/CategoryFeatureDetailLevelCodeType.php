<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class CategoryFeatureDetailLevelCodeType extends EbatNs_FacetType
{
	const CodeType_ReturnAll = 'ReturnAll';
	const CodeType_ReturnFeatureDefinitions = 'ReturnFeatureDefinitions';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('CategoryFeatureDetailLevelCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_CategoryFeatureDetailLevelCodeType = new CategoryFeatureDetailLevelCodeType();
?>