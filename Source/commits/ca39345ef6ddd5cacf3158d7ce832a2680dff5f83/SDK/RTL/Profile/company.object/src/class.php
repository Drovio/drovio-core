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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Profile", "team");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Profile\team;

/**
 * Company Manager Class
 * 
 * Manages the active company and its information.
 * The company as entity extends the team, so the class extends the team class.
 * 
 * @version	2.0-1
 * @created	October 23, 2014, 17:04 (EEST)
 * @revised	December 3, 2014, 15:01 (EET)
 */
class company extends team
{
	/**
	 * All the company data.
	 * 
	 * @type	array
	 */
	private static $companyData = array();
	
	/**
	 * Gets the company info.
	 * 
	 * @return	array
	 * 		An array of the all company data separated to team data and to company data.
	 */
	public static function info()
	{
		// Validate connection to team/company
		if (!self::validate())
			return NULL;
			
		// Total array of info
		$info = array();
		
		// Get team info
		$info['team'] = parent::info();
		
		// Get company info from database
		$info['company'] = self::getInfo(self::getCompanyID());
		
		// Return array
		return $info;
	}
	
	/**
	 * Gets the given company's information from the database.
	 * 
	 * @param	integer	$companyID
	 * 		The company data to get info for.
	 * 
	 * @return	array
	 * 		Array of company data.
	 */
	private static function getInfo($companyID)
	{
		$dbc = new dbConnection();
		$q = new dbQuery("2166405471551", "retail.company.profile");
		$attr = array();
		$attr['id'] = $companyID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Get the current company id.
	 * 
	 * @return	integer
	 * 		The current company id (at this version it is the team id).
	 */
	public static function getCompanyID()
	{
		return parent::getTeamID();
	}
	
	/**
	 * Get the current company ssn value.
	 * 
	 * @return	string
	 * 		The current company ssn value.
	 */
	public static function getCompanySSN()
	{
		return self::getCompanyValue("ssn");
	}
	
	/**
	 * Get all company branches.
	 * 
	 * @return	array
	 * 		An array of all company branches and their information.
	 */
	public static function getBranchList()
	{
		$dbc = new dbConnection();
		$q = new dbQuery("20810741761242", "retail.company.branches");
		$attr = array();
		$attr['id'] = self::getCompanyID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all company storage units.
	 * 
	 * @return	array
	 * 		An array of all storage units.
	 */
	public static function getStorageUnits()
	{
		$dbc = new dbConnection();
		$q = new dbQuery("29536528391504", "retail.company");
		$attr = array();
		$attr['id'] = self::getCompanyID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Gets a company value from the session. If the session is not set yet, updates from the database.
	 * 
	 * @param	string	$name
	 * 		The value name.
	 * 
	 * @return	void
	 */
	private static function getCompanyValue($name)
	{
		// Check session existance
		if (!isset(self::$companyData[$name]))
		{
			$info = self::info();
			self::$companyData = $info['company'];
		}
			
		return self::$companyData[$name];
	}
}
//#section_end#
?>