<?php
//#section#[header]
// Namespace
namespace RTL\Products\globl;

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
 * @namespace	\globl
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "privileges");
importer::import("RTL", "Comm", "dbConnection");

use \API\Model\sql\dbQuery;
use \API\Security\privileges;
use \RTL\Comm\dbConnection;

/**
 * Global product info manager
 * 
 * Manages global product info values.
 * 
 * NOTE: You must be retail product manager to manage data through this class.
 * 
 * @version	0.1-1
 * @created	December 19, 2014, 13:31 (EET)
 * @revised	December 19, 2014, 13:31 (EET)
 */
class globalProductInfo
{
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The global product id.
	 * 
	 * @type	integer
	 */
	protected $productID;
	
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
	 * 		The global product id.
	 * 
	 * @return	void
	 */
	public function __construct($productID)
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		
		// Set product id and type
		$this->productID = $productID;
	}
	
	/**
	 * Add a new product info.
	 * This functions creates a new product info, with no value.
	 * 
	 * NOTE: To add a new info you must be retail product manager.
	 * 
	 * @param	integer	$categoryID
	 * 		The info category id.
	 * 
	 * @param	string	$title
	 * 		The info title.
	 * 
	 * @param	boolean	$is_bool
	 * 		This defines whether the info should be handled as boolean or not.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function add($categoryID, $title, $is_bool = FALSE)
	{
		// Check category and title
		if (empty($categoryID) || empty($title))
			return FALSE;
		
		// Check generic category privileges
		if ($generic && !privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Get company product info
		$q = new dbQuery("33359037155111", "retail.products.info");
		$attr = array();
		$attr['cid'] = "NULL";
		$attr['catid'] = $categoryID;
		$attr['title'] = $title;
		$attr['is_bool'] = ($is_bool ? 1 : 0);
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set product info value for the current product.
	 * 
	 * NOTE: To set a global info value you must be retail product manager.
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
		
		// Check generic category privileges
		if (!privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Get company product info
		$q = new dbQuery("26015304472908", "retail.products.info");
		$attr = array();
		$attr['pid'] = $this->productID;
		$attr['iid'] = $infoID;
		$attr['value'] = $value;
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			if (empty($value))
				unset(self::$productInfo[$this->productID][$infoID]);
			else
				self::$productInfo[$this->productID][$infoID] = $value;
			return TRUE;
		}

		return FALSE;
	}
	
	/**
	 * Get product info value.
	 * 
	 * @param	integer	$infoID
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
		if (!empty($infoID) && isset(self::$productInfo[$this->productID][$infoID]))
			return self::$productInfo[$this->productID][$infoID];
		
		// Get company product info
		$q = new dbQuery("16996376082132", "retail.products.info");
		$attr = array();
		$attr['pid'] = $this->productID;
		$attr['iid'] = (empty($infoID) ? "NULL" : $infoID);
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			// Get all product info
			$infoData = $this->dbc->fetch($result, TRUE);
			self::$productInfo[$this->productID] = array();
			foreach ($infoData as $info)
				self::$productInfo[$this->productID][$info['info_id']] = $info;
			
			// Get value
			if (!empty($infoID) && isset(self::$productInfo[$this->productID][$infoID]))
				return $this->get($infoID);
			
			// Return all values
			return self::$productInfo[$this->productID];
		}
		
		return NULL;
	}
	
	/**
	 * Get all global product info by category.
	 * 
	 * @param	integer	$categoryID
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
		$attr['cid'] = "NULL";
		$attr['catid'] = (empty($categoryID) ? "NULL" : $categoryID);
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>