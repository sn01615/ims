<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class ModifyActionCodeType extends EbatNs_FacetType
{
	const CodeType_Add = 'Add';
	const CodeType_Delete = 'Delete';
	const CodeType_Update = 'Update';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('ModifyActionCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_ModifyActionCodeType = new ModifyActionCodeType();
?>