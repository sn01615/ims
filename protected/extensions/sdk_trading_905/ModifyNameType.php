<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Defines the details about one specific trait name.
  * 
 **/

class ModifyNameType extends EbatNs_ComplexType
{
	/**
	* @var string
	**/
	protected $Name;

	/**
	* @var string
	**/
	protected $NewName;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ModifyNameType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Name' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'NewName' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return string
	 **/
	function getName()
	{
		return $this->Name;
	}

	/**
	 * @return void
	 **/
	function setName($value)
	{
		$this->Name = $value;
	}

	/**
	 * @return string
	 **/
	function getNewName()
	{
		return $this->NewName;
	}

	/**
	 * @return void
	 **/
	function setNewName($value)
	{
		$this->NewName = $value;
	}

}
?>
