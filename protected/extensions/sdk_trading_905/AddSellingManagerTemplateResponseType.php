<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'SellingManagerProductDetailsType.php';
require_once 'FeesType.php';

/**
  * Contains values indicating template information for a newly-exported item.
  * 
 **/

class AddSellingManagerTemplateResponseType extends AbstractResponseType
{
	/**
	* @var long
	**/
	protected $CategoryID;

	/**
	* @var long
	**/
	protected $Category2ID;

	/**
	* @var long
	**/
	protected $SaleTemplateID;

	/**
	* @var long
	**/
	protected $SaleTemplateGroupID;

	/**
	* @var string
	**/
	protected $SaleTemplateName;

	/**
	* @var SellingManagerProductDetailsType
	**/
	protected $SellingManagerProductDetails;

	/**
	* @var FeesType
	**/
	protected $Fees;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('AddSellingManagerTemplateResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'CategoryID' =>
				array(
					'required' => false,
					'type' => 'long',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'Category2ID' =>
				array(
					'required' => false,
					'type' => 'long',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'SaleTemplateID' =>
				array(
					'required' => false,
					'type' => 'long',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'SaleTemplateGroupID' =>
				array(
					'required' => false,
					'type' => 'long',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'SaleTemplateName' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'SellingManagerProductDetails' =>
				array(
					'required' => false,
					'type' => 'SellingManagerProductDetailsType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'Fees' =>
				array(
					'required' => false,
					'type' => 'FeesType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return long
	 **/
	function getCategoryID()
	{
		return $this->CategoryID;
	}

	/**
	 * @return void
	 **/
	function setCategoryID($value)
	{
		$this->CategoryID = $value;
	}

	/**
	 * @return long
	 **/
	function getCategory2ID()
	{
		return $this->Category2ID;
	}

	/**
	 * @return void
	 **/
	function setCategory2ID($value)
	{
		$this->Category2ID = $value;
	}

	/**
	 * @return long
	 **/
	function getSaleTemplateID()
	{
		return $this->SaleTemplateID;
	}

	/**
	 * @return void
	 **/
	function setSaleTemplateID($value)
	{
		$this->SaleTemplateID = $value;
	}

	/**
	 * @return long
	 **/
	function getSaleTemplateGroupID()
	{
		return $this->SaleTemplateGroupID;
	}

	/**
	 * @return void
	 **/
	function setSaleTemplateGroupID($value)
	{
		$this->SaleTemplateGroupID = $value;
	}

	/**
	 * @return string
	 **/
	function getSaleTemplateName()
	{
		return $this->SaleTemplateName;
	}

	/**
	 * @return void
	 **/
	function setSaleTemplateName($value)
	{
		$this->SaleTemplateName = $value;
	}

	/**
	 * @return SellingManagerProductDetailsType
	 **/
	function getSellingManagerProductDetails()
	{
		return $this->SellingManagerProductDetails;
	}

	/**
	 * @return void
	 **/
	function setSellingManagerProductDetails($value)
	{
		$this->SellingManagerProductDetails = $value;
	}

	/**
	 * @return FeesType
	 **/
	function getFees()
	{
		return $this->Fees;
	}

	/**
	 * @return void
	 **/
	function setFees($value)
	{
		$this->Fees = $value;
	}

}
?>
