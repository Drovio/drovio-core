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
 * Company Product Info Category Manager
 * 
 * Manages product info categories for generic use and for the current company.
 * 
 * NOTE: You must be company manager or product manager to manage data through this class.
 * 
 * @version	0.1-3
 * @created	December 16, 2014, 16:32 (EET)
 * @revised	December 16, 2014, 17:29 (EET)
 */
class cProductInfoCategory
{
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The info category id.
	 * 
	 * @type	integer
	 */
	protected $categoryID;
	
	/**
	 * Initialize the info category.
	 * 
	 * @param	integer	$id
	 * 		The info category id.
	 * 		Leave empty for new product.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		
		// Set the category id
		$this->categoryID = $id;
	}
	
	/**
	 * Add a new info category.
	 * 
	 * NOTE:
	 * To add a company specific category, you must be company manager.
	 * To add a new generic category you must be product manager.
	 * 
	 * @param	string	$title
	 * 		The category title.
	 * 
	 * @param	boolean	$generic
	 * 		If set to TRUE, this will be a generic category. If FALSE, it will be a company specific category.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function add($title, $generic = FALSE)
	{
		// Check title
		if (empty($title))
			return FALSE;
			
		// Check if account is in the right user group
		if (!$generic && !privileges::accountToGroup("RTL_COMPANY_MANAGER"))
			return FALSE;
		
		// Check generic category privileges
		if ($generic && !privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Get company product info
		$q = new dbQuery("31317033779096", "retail.products.info");
		$attr = array();
		$attr['cid'] = ($generic ? "NULL" : company::getCompanyID());
		$attr['title'] = $title;
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			// Get created category
			$categoryData = $this->dbc->fetch($result);
			$this->categoryID = $categoryData['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Add this category to a given hierarchy.
	 * 
	 * NOTE:
	 * To modify a company specific category, you must be company manager.
	 * To modify a new generic category you must be product manager.
	 * 
	 * @param	integer	$hierarchyID
	 * 		The hierarchy id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function addToHierarchy($hierarchyID)
	{
		// Check hierarchy id
		if (empty($hierarchyID))
			return FALSE;
		
		// Check if account is in the right user group
		$ownValidation = ($this->validate(TRUE) && privileges::accountToGroup("RTL_COMPANY_MANAGER"));
		$genericValidation = ($this->validate() &&privileges::accountToGroup("RTL_PRODUCT_MANAGER"));
		if (!($ownValidation || $genericValidation))
			return FALSE;
			
		// Add category to hierarchy
		$q = new dbQuery("23067056068637", "retail.products.info");
		$attr = array();
		$attr['cid'] = $this->categoryID;
		$attr['hid'] = $hierarchyID;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get category information.
	 * 
	 * @return	array
	 * 		An array of information for this category.
	 */
	public function info()
	{
		// Get category info
		$q = new dbQuery("29790938412443", "retail.products.info");
		$attr = array();
		$attr['cid'] = $this->categoryID;
		$result = $this->dbc->execute($q, $attr);
		$categoryInfo = $this->dbc->fetch($result);
		
		// Get hierarchies
		$q = new dbQuery("1782346019762", "retail.products.info");
		$attr = array();
		$attr['cid'] = $this->categoryID;
		$result = $this->dbc->execute($q, $attr);
		$categoryInfo['hierarchies'] = $this->dbc->fetch($result, TRUE);
		
		return $categoryInfo;
	}
	
	/**
	 * Validate the current category whether it is generic or owned by the current company.
	 * 
	 * @param	boolean	$strict
	 * 		If strict, it will only check if the category is owned by the current company.
	 * 
	 * @return	boolean
	 * 		True if the category is valid, false otherwise.
	 */
	public function validate($strict = FALSE)
	{
		// Get category info
		$info = $this->info();
		
		// Validate if is this company's
		$own = ($info['company_id'] == company::getCompanyID());
		if ($strict)
			return $own;
		return (empty($info['company_id']) || $own);
	}
	
	/**
	 * Get all product info categories in the system.
	 * It includes generic and company specific categories.
	 * 
	 * @param	integer	$hierarchyID
	 * 		The hierarchy id to match the categories.
	 * 		Leave empty to ignore hierarchy.
	 * 		It is NULL by default.
	 * 
	 * @return	array
	 * 		An array of all product info categories.
	 */
	public function getCategories($hierarchyID = NULL)
	{
		// Get all categories in the given hierarchy
		$q = new dbQuery("1702396305648", "retail.products.info");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['hid'] = (empty($hierarchyID) ? "NULL" : $hierarchyID);
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>