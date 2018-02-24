<?php
//#section#[header]
// Namespace
namespace RTL\Products;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	RTL
 * @package	Products
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Profile", "company");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;

/**
 * Company Product Price Manager
 * 
 * Sets, gets and removes product prices.
 * To be used only by product managers.
 * Changing product prices will have immediate effect everywhere.
 * 
 * All prices are saved without any taxes or VATs.
 * 
 * @version	0.1-1
 * @created	December 15, 2014, 16:54 (EET)
 * @revised	December 15, 2014, 16:54 (EET)
 */
class cProductPrice
{
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	/**
	 * The company relative product id.
	 * 
	 * @type	integer
	 */
	protected $companyProductID;
	
	/**
	 * Initialize the company product.
	 * 
	 * @param	integer	$id
	 * 		The company product id.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		
		// Set company product id
		$this->companyProductID = $id;
	}
	
	/**
	 * Get all product prices for the active company.
	 * 
	 * @return	array
	 * 		An array of all prices, including the tax rate and the price type title.
	 */
	public function getPrices()
	{
		// Set product price
		$q = new dbQuery("17383966877549", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Set a price for the current product.
	 * 
	 * @param	integer	$priceType
	 * 		The price type as it fetched from the getPriceTypes() function.
	 * 
	 * @param	float	$price
	 * 		The product price.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setPrice($priceType, $price)
	{
		// Set product price
		$q = new dbQuery("28240796859702", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['type'] = $priceType;
		$attr['price'] = $price;
		$attr['time'] = time();
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Remove a price from the current product.
	 * 
	 * @param	integer	$priceType
	 * 		The price type to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removePrice($priceType)
	{
		// Remove product price
		$q = new dbQuery("34438601673638", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['type'] = $priceType;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set the tax rate id for the current product.
	 * 
	 * @param	integer	$taxRate
	 * 		The tax rate id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setTaxRate($taxRate)
	{
		// Set product tax rate
		$q = new dbQuery("28066033210391", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['rate'] = $taxRate;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all company price types, including global types.
	 * 
	 * @return	array
	 * 		An array of all price types.
	 */
	public static function getPriceTypes()
	{
		// Get all tax rates in the given company
		$dbc = new dbConnection();
		$q = new dbQuery("26594258916143", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>