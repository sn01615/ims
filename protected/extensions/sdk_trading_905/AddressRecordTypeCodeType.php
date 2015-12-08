<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class AddressRecordTypeCodeType extends EbatNs_FacetType
{
	const CodeType_Residential = 'Residential';
	const CodeType_Business = 'Business';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('AddressRecordTypeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_AddressRecordTypeCodeType = new AddressRecordTypeCodeType();
?>