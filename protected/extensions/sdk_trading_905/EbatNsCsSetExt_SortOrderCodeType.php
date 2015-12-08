<?php
// autogenerated file 05.05.2008 16:30
// $Id: EbatNsCsSetExt_SortOrderCodeType.php,v 1.2 2013-04-05 11:15:50 thomasbiniasch Exp $
// $Log: EbatNsCsSetExt_SortOrderCodeType.php,v $
// Revision 1.2  2013-04-05 11:15:50  thomasbiniasch
// bugfixes and template updates, first running version milestone!
//
//
require_once 'EbatNs_FacetType.php';

/**
 *  
 *
 *
 * @property string Ascending
 * @property string Descending
 * @property string CustomCode
 */
class EbatNsCsSetExt_SortOrderCodeType extends EbatNs_FacetType
{
	const CodeType_Ascending = 'Ascending';
	const CodeType_Descending = 'Descending';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 */
	function __construct()
	{
		parent::__construct('EbatNsCsSetExt_SortOrderCodeType', 'http://www.intradesys.com/Schemas/ebay/AttributeData_Extension.xsd');

	}
}

$Facet_EbatNsCsSetExt_SortOrderCodeType = new EbatNsCsSetExt_SortOrderCodeType();

?>
