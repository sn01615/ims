<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class AddressAttributeCodeType extends EbatNs_FacetType
{
	const CodeType_ReferenceNumber = 'ReferenceNumber';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('AddressAttributeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_AddressAttributeCodeType = new AddressAttributeCodeType();
?>