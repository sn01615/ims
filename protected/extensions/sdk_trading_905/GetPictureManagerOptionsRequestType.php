<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';

/**
  *       Requests a list of Picture Manager options and allowed values,
  * such as subscription type and picture display.
  *     
 **/

class GetPictureManagerOptionsRequestType extends AbstractRequestType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetPictureManagerOptionsRequestType', 'urn:ebay:apis:eBLBaseComponents');
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
