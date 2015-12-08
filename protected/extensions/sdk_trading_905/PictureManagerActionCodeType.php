<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PictureManagerActionCodeType extends EbatNs_FacetType
{
	const CodeType_Add = 'Add';
	const CodeType_Delete = 'Delete';
	const CodeType_Rename = 'Rename';
	const CodeType_Move = 'Move';
	const CodeType_Change = 'Change';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PictureManagerActionCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PictureManagerActionCodeType = new PictureManagerActionCodeType();
?>