<?php
//#section#[header]
// Namespace
namespace ENP\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ENP
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ENP", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");

use \ENP\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;

/**
 * Enterprise company profile
 * 
 * Manages the active company and its information.
 * The company as entity extends the team, so the class extends the team class.
 * 
 * @version	0.1-1
 * @created	July 29, 2015, 17:08 (EEST)
 * @updated	July 29, 2015, 17:08 (EEST)
 */
class company extends team
{
	/**
	 * All the company information.
	 * 
	 * @type	array
	 */
	private static $companyData = array();
	
	/**
	 * Gets the company info.
	 * 
	 * @return	array
	 * 		An array of the all company data separated to team data and to company data:
	 * 		['team'] = Team Info Array
	 * 		['company'] = Company Info Array
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
	 * 		The current company/team id.
	 */
	public static function getCompanyID()
	{
		return parent::getTeamID();
	}
	
	/**
	 * Gets a company value from the session.
	 * If the session is not set yet, updates from the database.
	 * 
	 * @param	string	$name
	 * 		The property name.
	 * 
	 * @return	string
	 * 		The property value.
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