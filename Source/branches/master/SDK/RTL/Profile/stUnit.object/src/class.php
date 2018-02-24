<?php
//#section#[header]
// Namespace
namespace RTL\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Profile", "company");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;

/**
 * Storage unit manager
 * 
 * This class manages all the company's storage units.
 * 
 * @version	0.1-1
 * @created	October 3, 2015, 15:49 (EEST)
 * @updated	October 3, 2015, 15:49 (EEST)
 */
class stUnit
{
	/**
	 * The retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The storage unit id.
	 * 
	 * @type	integer
	 */
	private $stUnitID;
	
	/**
	 * Initialize the storage unit.
	 * 
	 * @param	string	$stUnitID
	 * 		The storage unit id.
	 * 		Leave empty for new units.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($stUnitID = "")
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		
		// Set storage unit id
		$this->stUnitID = $stUnitID;
	}
	
	/**
	 * Create a new storage unit.
	 * 
	 * @param	string	$title
	 * 		The storage unit title.
	 * 
	 * @param	string	$description
	 * 		The storage unit description.
	 * 		This is optional.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($title, $description = "")
	{
		// Check parent id
		if (empty($title))
			return FALSE;
		
		// Create storage unit
		$dbc = new dbConnection();
		$q = new dbQuery("17960569000668", "retail.company.sunits");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$result = $dbc->execute($q, $attr);
		if ($result)
		{
			$stData = $dbc->fetch($result);
			$this->stUnitID = $stData['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update the storage unit information.
	 * 
	 * @param	string	$title
	 * 		The storage unit title.
	 * 
	 * @param	string	$description
	 * 		The storage unit description.
	 * 		This is optional.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($title, $description = "")
	{
		// Check parent id
		if (empty($title))
			return FALSE;
		
		// Create branch
		$dbc = new dbConnection();
		$q = new dbQuery("26379808821676", "retail.company.sunits");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['suid'] = $this->stUnitID;
		$attr['title'] = $title;
		$attr['description'] = $description;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Remove the current storage unit from the system.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove storage unit
		$dbc = new dbConnection();
		$q = new dbQuery("32167353198472", "retail.company.sunits");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['suid'] = $this->stUnitID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get storage unit information.
	 * 
	 * @return	array
	 * 		An array of all storage unit information.
	 */
	public function info()
	{
		// Get storage unit info
		$dbc = new dbConnection();
		$q = new dbQuery("24042000868697", "retail.company.sunits");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['suid'] = $this->stUnitID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Get the current storage unit id.
	 * 
	 * @return	string
	 * 		The storage unit id.
	 */
	public function getStorageUnitID()
	{
		return $this->stUnitID;
	}
}
//#section_end#
?>