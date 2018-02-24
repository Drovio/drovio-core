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
 * Company Branch Manager
 * 
 * Creates and updates branches.
 * 
 * @version	3.0-1
 * @created	December 3, 2014, 15:01 (EET)
 * @updated	September 2, 2015, 11:26 (EEST)
 */
class branch
{
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The company branch id.
	 * 
	 * @type	integer
	 */
	protected $branchID;
	
	/**
	 * Initialize the company branch.
	 * 
	 * @param	integer	$branchID
	 * 		The branch id.
	 * 
	 * @return	void
	 */
	public function __construct($branchID = "")
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		
		// Set branch id
		$this->branchID = $branchID;
	}
	
	/**
	 * Create a new company branch.
	 * 
	 * @param	string	$title
	 * 		The branch title.
	 * 
	 * @param	string	$address
	 * 		The branch address.
	 * 		It could include a full address with postal code, city and country.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($title, $address = "")
	{
		// Check parent id
		if (empty($title))
			return FALSE;
		
		// Create branch
		$dbc = new dbConnection();
		$q = new dbQuery("25421712376204", "retail.company.branches");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['title'] = $title;
		$attr['address'] = $address;
		$result = $dbc->execute($q, $attr);
		if ($result)
		{
			$branchData = $dbc->fetch($result);
			$this->branchID = $branchData['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update branch information.
	 * 
	 * @param	string	$title
	 * 		The new branch title.
	 * 
	 * @param	string	$address
	 * 		The new branch address.
	 * 		It could include a full address with postal code, city and country.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($title, $address = "")
	{
		// Check parent id
		if (empty($title))
			return FALSE;
		
		// Create branch
		$dbc = new dbConnection();
		$q = new dbQuery("22479077146526", "retail.company.branches");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['bid'] = $this->branchID;
		$attr['title'] = $title;
		$attr['address'] = $address;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Remove a branch from the database.
	 * It should be empty of products or other references.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Check parent id
		if (empty($title))
			return FALSE;
		
		// Create branch
		$dbc = new dbConnection();
		$q = new dbQuery("32925174601907", "retail.company.branches");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['bid'] = $this->branchID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get the current company branch id.
	 * 
	 * @return	string
	 * 		The current branch id.
	 */
	public function getBranchID()
	{
		return $this->branchID;
	}
}
//#section_end#
?>