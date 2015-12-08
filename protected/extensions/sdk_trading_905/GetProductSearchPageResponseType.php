<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'ProductSearchPageType.php';

/**
  * This type is deprecated as the call is no longer available.
  * 
  * 
 **/

class GetProductSearchPageResponseType extends AbstractResponseType
{
	/**
	* @var string
	**/
	protected $AttributeSystemVersion;

	/**
	* @var ProductSearchPageType
	**/
	protected $ProductSearchPage;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetProductSearchPageResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'AttributeSystemVersion' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'ProductSearchPage' =>
				array(
					'required' => false,
					'type' => 'ProductSearchPageType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => true,
					'cardinality' => '0..*'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return string
	 **/
	function getAttributeSystemVersion()
	{
		return $this->AttributeSystemVersion;
	}

	/**
	 * @return void
	 **/
	function setAttributeSystemVersion($value)
	{
		$this->AttributeSystemVersion = $value;
	}

	/**
	 * @return ProductSearchPageType
	 * @param integer $index 
	 **/
	function getProductSearchPage($index = null)
	{
		if ($index !== null)
		{
			return $this->ProductSearchPage[$index];
		}
		else
		{
			return $this->ProductSearchPage;
		}
	}

	/**
	 * @return void
	 * @param ProductSearchPageType $value
	 * @param integer $index 
	 **/
	function setProductSearchPage($value, $index = null)
	{
		if ($index !== null)
		{
			$this->ProductSearchPage[$index] = $value;
		}
		else
		{
			$this->ProductSearchPage= $value;
		}
	}

	/**
	 * @return void
	 * @param ProductSearchPageType $value
	 **/
	function addProductSearchPage($value)
	{
		$this->ProductSearchPage[] = $value;
	}

}
?>
