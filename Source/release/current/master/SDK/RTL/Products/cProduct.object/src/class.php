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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Products", "cProductCodeManager");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;
use \RTL\Products\cProductCodeManager;

/**
 * Company Product Manager
 * 
 * Manages all information for a product from the perspective of the current active company.
 * 
 * @version	4.1-1
 * @created	December 15, 2014, 15:48 (EET)
 * @updated	October 13, 2015, 12:38 (EEST)
 */
class cProduct
{
	/**
	 * The retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The company relative product id.
	 * 
	 * @type	integer
	 */
	private $productID;
	
	/**
	 * Initialize the company product.
	 * 
	 * @param	integer	$id
	 * 		The company product id.
	 * 		Leave empty for new product.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Initialize database connection and variables
		$this->dbc = new dbConnection();
		$this->productID = $id;
	}
	
	/**
	 * Create a new company product.
	 * 
	 * @param	string	$title
	 * 		The product title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($title)
	{
		// Check empty
		if (empty($title))
			return FALSE;
			
		// Create company product
		$q = new dbQuery("15790019094335", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['title'] = $title;
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			$productInfo = $this->dbc->fetch($result);
			$this->productID = $productInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Get all information about this product.
	 * 
	 * @return	arra
	 * 		An array of product information.
	 */
	public function info()
	{
		// Get company product info
		$q = new dbQuery("20607211206042", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->productID;
		$result = $this->dbc->execute($q, $attr);
		$productInfo = $this->dbc->fetch($result);
		
		// Get product codes
		//$codeManager = new cProductCodeManager($this->productID);
		//$productInfo['codes'] = $codeManager->get();
		
		return $productInfo;
	}
	
	/**
	 * Update product basic information.
	 * 
	 * @param	string	$title
	 * 		The product title.
	 * 
	 * @param	string	$notes
	 * 		The product notes.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($title, $notes = "")
	{
		// Update company product info
		$q = new dbQuery("29080143278493", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->productID;
		$attr['title'] = $title;
		$attr['notes'] = $notes;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Create a global product for this company product and connect.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Update company product info
		$q = new dbQuery("16275050423963", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->productID;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get the product id.
	 * 
	 * @return	string
	 * 		The current product id.
	 */
	public function getProductID()
	{
		return $this->productID;
	}
	
	/**
	 * Set the tax rate id for the current product.
	 * 
	 * @param	integer	$taxRate
	 * 		The tax rate id reference.
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
		$attr['pid'] = $this->productID;
		$attr['rate'] = $taxRate;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set the unit of measurement for the current product.
	 * 
	 * @param	integer	$mUnit
	 * 		The unit of measurement id reference.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setMUnit($mUnit)
	{
		// Set product measurement unit
		$q = new dbQuery("17841505452715", "retail.products.stock");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->productID;
		$attr['unit'] = $mUnit;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Deactivate the company product to be publicly invisible.
	 * 
	 * @param	integer	$groupID
	 * 		The group id of products.
	 * 		Leave empty for all products.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function getProducts($groupID = "")
	{
		// Get current company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
		
		if (empty($groupID))
		{
			// Get all company products
			$dbc = new dbConnection();
			$q = new dbQuery("15588933471719", "retail.products");
			$attr = array();
			$attr['cid'] = $companyID;
			$result = $dbc->execute($q, $attr);
			return $dbc->fetch($result, TRUE);
		}
		
		return array();
	}
}
//#section_end#
?>