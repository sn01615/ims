<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';

/**
  * Response to the <b>AddMemberMessageAAQToPartner</b> call.
  * 
 **/

class AddMemberMessageAAQToPartnerResponseType extends AbstractResponseType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('AddMemberMessageAAQToPartnerResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

}
?>
