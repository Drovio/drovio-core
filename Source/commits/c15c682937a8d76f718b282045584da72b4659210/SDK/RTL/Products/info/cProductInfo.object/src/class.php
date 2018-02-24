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
importer::import("API", "Security", "accountKey");
importer::import("RTL", "Comm", "dbConnection");
importer::import("RTL", "Profile", "company");

use \API\Model\sql\dbQuery;
use \API\Security\accountKey;
use \RTL\Comm\dbConnection;
use \RTL\Profile\company;

/**
 * Company product info manager
 * 
 * Manages product info values for the current company.
 * 
 * NOTE: You must be company manager or product manager to manage data through this class.
 * 
 * @version	2.0-1
 * @created	December 17, 2014, 10:48 (EET)
 * @revised	December 19, 2014, 13:31 (EET)
 */
class cProductInfo
{
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
	 * NOTE: To add a company specific info, you must be team admin.
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
			
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
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
	 * NOTE: To set a company specific info value, you must be team admin.
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
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
			return FALSE;
			
		// Get company product info
		$q = new dbQuery("24421429534941", "retail.products.info");
		$attr = array();
		$attr['pid'] = $this->productID;
		$attr['cid'] = company::getCompanyID();
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
		if (!empty($infoID) && isset(self::$productInfo[$this->productID][$infoID]))
			return self::$productInfo[$this->productID][$infoID];
		
		// Get company product info
		$q = new dbQuery("1748397464019", "retail.products.info");
		$attr = array();
		$attr['pid'] = $this->productID;
		$attr['cid'] = company::getCompanyID();
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