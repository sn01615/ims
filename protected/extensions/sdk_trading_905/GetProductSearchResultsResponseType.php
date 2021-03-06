<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'DataElementSetType.php';
require_once 'ProductSearchResultType.php';

/**
  * This type is deprecated as the call is no longer available.
  * 
  * 
 **/

class GetProductSearchResultsResponseType extends AbstractResponseType
{
	/**
	* @var DataElementSetType
	**/
	protected $DataElementSets;

	/**
	* @var ProductSearchResultType
	**/
	protected $ProductSearchResult;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetProductSearchResultsResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'DataElementSets' =>
				array(
					'required' => false,
					'type' => 'DataElementSetType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => true,
					'cardinality' => '0..*'
				),
				'ProductSearchResult' =>
				array(
					'required' => false,
					'type' => 'ProductSearchResultType',
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
	 * @return DataElementSetType
	 * @param integer $index 
	 **/
	function getDataElementSets($index = null)
	{
		if ($index !== null)
		{
			return $this->DataElementSets[$index];
		}
		else
		{
			return $this->DataElementSets;
		}
	}

	/**
	 * @return void
	 * @param DataElementSetType $value
	 * @param integer $index 
	 **/
	function setDataElementSets($value, $index = null)
	{
		if ($index !== null)
		{
			$this->DataElementSets[$index] = $value;
		}
		else
		{
			$this->DataElementSets= $value;
		}
	}

	/**
	 * @return void
	 * @param DataElementSetType $value
	 **/
	function addDataElementSets($value)
	{
		$this->DataElementSets[] = $value;
	}

	/**
	 * @return ProductSearchResultType
	 * @param integer $index 
	 **/
	function getProductSearchResult($index = null)
	{
		if ($index !== null)
		{
			return $this->ProductSearchResult[$index];
		}
		else
		{
			return $this->ProductSearchResult;
		}
	}

	/**
	 * @return void
	 * @param ProductSearchResultType $value
	 * @param integer $index 
	 **/
	function setProductSearchResult($value, $index = null)
	{
		if ($index !== null)
		{
			$this->ProductSearchResult[$index] = $value;
		}
		else
		{
			$this->ProductSearchResult= $value;
		}
	}

	/**
	 * @return void
	 * @param ProductSearchResultType $value
	 **/
	function addProductSearchResult($value)
	{
		$this->ProductSearchResult[] = $value;
	}

}
?>
