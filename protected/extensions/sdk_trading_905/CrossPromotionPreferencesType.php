<?php
/* Generated on 2/3/15 3:23 AM by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'ItemFormatSortFilterCodeType.php';
require_once 'GallerySortFilterCodeType.php';
require_once 'ItemSortFilterCodeType.php';
require_once 'ItemFormatSortFilterCodeType.php';
require_once 'GallerySortFilterCodeType.php';
require_once 'ItemSortFilterCodeType.php';

/**
  *  Contains preferences describing how items similar to the one the user is
  *   presently viewing are promoted.
  *   <br><br>
  * <span class="tablenote"><b>Note:</b>
  * eBay Store Cross Promotions are no longer supported in the Trading API, so the 
  * <b>CrossPromotionPreferences</b> container and the 
  * <b>ShowCrossPromotionPreferences</b> flag (in 
  *   <b>GetUserPreferences</b>) should no longer be used/set.
  * </span>
  * 
 **/

class CrossPromotionPreferencesType extends EbatNs_ComplexType
{
	/**
	* @var boolean
	**/
	protected $CrossPromotionEnabled;

	/**
	* @var ItemFormatSortFilterCodeType
	**/
	protected $CrossSellItemFormatSortFilter;

	/**
	* @var GallerySortFilterCodeType
	**/
	protected $CrossSellGallerySortFilter;

	/**
	* @var ItemSortFilterCodeType
	**/
	protected $CrossSellItemSortFilter;

	/**
	* @var ItemFormatSortFilterCodeType
	**/
	protected $UpSellItemFormatSortFilter;

	/**
	* @var GallerySortFilterCodeType
	**/
	protected $UpSellGallerySortFilter;

	/**
	* @var ItemSortFilterCodeType
	**/
	protected $UpSellItemSortFilter;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('CrossPromotionPreferencesType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'CrossPromotionEnabled' =>
				array(
					'required' => false,
					'type' => 'boolean',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'CrossSellItemFormatSortFilter' =>
				array(
					'required' => false,
					'type' => 'ItemFormatSortFilterCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'CrossSellGallerySortFilter' =>
				array(
					'required' => false,
					'type' => 'GallerySortFilterCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'CrossSellItemSortFilter' =>
				array(
					'required' => false,
					'type' => 'ItemSortFilterCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'UpSellItemFormatSortFilter' =>
				array(
					'required' => false,
					'type' => 'ItemFormatSortFilterCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'UpSellGallerySortFilter' =>
				array(
					'required' => false,
					'type' => 'GallerySortFilterCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'UpSellItemSortFilter' =>
				array(
					'required' => false,
					'type' => 'ItemSortFilterCodeType',
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
	 * @return boolean
	 **/
	function getCrossPromotionEnabled()
	{
		return $this->CrossPromotionEnabled;
	}

	/**
	 * @return void
	 **/
	function setCrossPromotionEnabled($value)
	{
		$this->CrossPromotionEnabled = $value;
	}

	/**
	 * @return ItemFormatSortFilterCodeType
	 **/
	function getCrossSellItemFormatSortFilter()
	{
		return $this->CrossSellItemFormatSortFilter;
	}

	/**
	 * @return void
	 **/
	function setCrossSellItemFormatSortFilter($value)
	{
		$this->CrossSellItemFormatSortFilter = $value;
	}

	/**
	 * @return GallerySortFilterCodeType
	 **/
	function getCrossSellGallerySortFilter()
	{
		return $this->CrossSellGallerySortFilter;
	}

	/**
	 * @return void
	 **/
	function setCrossSellGallerySortFilter($value)
	{
		$this->CrossSellGallerySortFilter = $value;
	}

	/**
	 * @return ItemSortFilterCodeType
	 **/
	function getCrossSellItemSortFilter()
	{
		return $this->CrossSellItemSortFilter;
	}

	/**
	 * @return void
	 **/
	function setCrossSellItemSortFilter($value)
	{
		$this->CrossSellItemSortFilter = $value;
	}

	/**
	 * @return ItemFormatSortFilterCodeType
	 **/
	function getUpSellItemFormatSortFilter()
	{
		return $this->UpSellItemFormatSortFilter;
	}

	/**
	 * @return void
	 **/
	function setUpSellItemFormatSortFilter($value)
	{
		$this->UpSellItemFormatSortFilter = $value;
	}

	/**
	 * @return GallerySortFilterCodeType
	 **/
	function getUpSellGallerySortFilter()
	{
		return $this->UpSellGallerySortFilter;
	}

	/**
	 * @return void
	 **/
	function setUpSellGallerySortFilter($value)
	{
		$this->UpSellGallerySortFilter = $value;
	}

	/**
	 * @return ItemSortFilterCodeType
	 **/
	function getUpSellItemSortFilter()
	{
		return $this->UpSellItemSortFilter;
	}

	/**
	 * @return void
	 **/
	function setUpSellItemSortFilter($value)
	{
		$this->UpSellItemSortFilter = $value;
	}

}
?>
