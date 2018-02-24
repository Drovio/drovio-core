<?php
//#section#[header]
// Namespace
namespace RTL\Products\info;

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
 * @namespace	\info
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "privileges");
importer::import("RTL", "Comm", "dbConnection");
importer::import("RTL", "Profile", "company");

use \API\Model\sql\dbQuery;
use \API\Security\privileges;
use \RTL\Comm\dbConnection;
use \RTL\Profile\company;

/**
 * Company product info manager
 * 
 * Manages product info values for the current company.
 * 
 * NOTE: You must be company manager or product manager to manage data through this class.
 * 
 * @version	1.0-1
 * @created	December 17, 2014, 10:48 (EET)
 * @revised	December 18, 2014, 17:09 (EET)
 */
class cProductInfo
{
	/**
	 * The company product type.
	 * 
	 * @type	string
	 */
	const COMPANY_PRODUCT = "cmp";
	/**
	 * The global product type.
	 * 
	 * @type	string
	 */
	const GLOBAL_PRODUCT = "glb";
	
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The product id.
	 * 
	 * @type	integer
	 */
	protected $productID;
	/**
	 * The product type which defines the product id.
	 * 
	 * @type	string
	 */
	protected $productType;
	
	/**
	 * Array of all product info at a given time.
	 * 
	 * @type	array
	 */
	private static $productInfo;
	
	/**
	 * Initialize the info manager.
	 * 
	 * @param	integer	$productID
	 * 		The product id.
	 * 
	 * @param	string	$type
	 * 		The product id type.
	 * 		Select for company type or global type using the class constants.
	 * 		Default is COMPANY_PRODUCT.
	 * 
	 * @return	void
	 */
	public function __construct($productID, $type = self::COMPANY_PRODUCT)
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		
		// Set product id and type
		$this->productID = $productID;
		$this->productType = $type;
	}
	
	/**
	 * Add a new product info.
	 * This functions creates a new product info, with no value.
	 * 
	 * NOTE:
	 * To add a company specific info, you must be company manager.
	 * To add a new generic info you must be product manager.
	 * 
	 * @param	integer	$categoryID
	 * 		The info category id.
	 * 
	 * @param	string	$title
	 * 		The info title.
	 * 
	 * @param	boolean	$is_bool
	 * 		This defines whether the info should be handled as boolean or not.
	 * 
	 * @param	boolean	$generic
	 * 		If set to TRUE, this will be a generic category. If FALSE, it will be a company specific category.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function add($categoryID, $title, $is_bool = FALSE, $generic = FALSE)
	{
		// Check category and title
		if (empty($categoryID) || empty($title))
			return FALSE;
			
		// Check if account is in the right user group
		if (!$generic && !privileges::accountToGroup("RTL_COMPANY_MANAGER"))
			return FALSE;
		
		// Check generic category privileges
		if ($generic && !privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Get company product info
		$q = new dbQuery("33359037155111", "retail.products.info");
		$attr = array();
		$attr['cid'] = ($generic ? "NULL" : company::getCompanyID());
		$attr['catid'] = $categoryID;
		$attr['title'] = $title;
		$attr['is_bool'] = ($is_bool ? 1 : 0);
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set a product info value for the current product.
	 * 
	 * NOTE:
	 * To set a company specific info value, you must be company manager.
	 * To set a global info value you must be product manager.
	 * 
	 * @param	integer	$infoID
	 * 		The product info id.
	 * 
	 * @param	string	$value
	 * 		The product info value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($infoID, $value = NULL)
	{
		// Check info id
		if (empty($infoID))
			return FALSE;
			
		// Check if account is in the right user group
		if ($this->productType == self::COMPANY_PRODUCT && !privileges::accountToGroup("RTL_COMPANY_MANAGER"))
			return FALSE;
		
		// Check generic category privileges
		if ($this->productType == self::GLOBAL_PRODUCT && !privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Get company product info
		$q = new dbQuery("24421429534941", "retail.products.info");
		$attr = array();
		if ($this->productType == self::COMPANY_PRODUCT)
		{
			$attr['cpid'] = $this->productID;
			$attr['gpid'] = "NULL";
		}
		else if ($this->productType == self::GLOBAL_PRODUCT)
		{
			$attr['cpid'] = "NULL";
			$attr['gpid'] = $this->productID;
		}
		$attr['cid'] = company::getCompanyID();
		$attr['iid'] = $infoID;
		$attr['value'] = $value;
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			if (empty($value))
				unset(self::$productInfo[$this->productType][$infoID]);
			else
				self::$productInfo[$this->productType][$infoID] = $value;
			return TRUE;
		}

		return FALSE;
	}
	
	/**
	 * Get a product info value.
	 * 
	 * @param	mixed	$infoID
	 * 		The info id to get the value for.
	 * 		You can leave empty to get all information about the current product.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		An array of all information or the requested info value.
	 */
	public function get($infoID = "")
	{
		// Check memory
		if (!empty($infoID) && isset(self::$productInfo[$this->productType][$infoID]))
			return self::$productInfo[$this->productType][$infoID];
		
		// Get company product info
		$q = new dbQuery("1748397464019", "retail.products.info");
		$attr = array();
		if ($this->productType == self::COMPANY_PRODUCT)
		{
			$attr['cpid'] = $this->productID;
			$attr['gpid'] = "NULL";
		}
		else if ($this->productType == self::GLOBAL_PRODUCT)
		{
			$attr['cpid'] = "NULL";
			$attr['gpid'] = $this->productID;
		}
		$attr['cid'] = company::getCompanyID();
		$attr['iid'] = (empty($infoID) ? "NULL" : $infoID);
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			// Get all product info
			$infoData = $this->dbc->fetch($result, TRUE);
			$infoArray = $this->dbc->toArray($infoData, "id", "value");
			self::$productInfo[$this->productType] = $infoArray;
			
			// Get value
			if (!empty($infoID) && isset(self::$productInfo[$this->productType][$infoID]))
				return $this->get($infoID);
			
			// Return all values
			return self::$productInfo[$this->productType];
		}
		
		return NULL;
	}
	
	/**
	 * Get all product info by category.
	 * 
	 * @param	mixed	$categoryID
	 * 		The category to get info for.
	 * 		Leave empty to get all information.
	 * 		It is NULL by default.
	 * 
	 * @return	array
	 * 		An array of all product information.
	 */
	public function getInfo($categoryID = NULL)
	{
		// Get all info in the given category
		$q = new dbQuery("26658529823183", "retail.products.info");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['catid'] = (empty($categoryID) ? "NULL" : $categoryID);
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>