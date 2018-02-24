<?php
//#section#[header]
// Namespace
namespace DEV\BugTracker;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	BugTracker
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Security", "account");
importer::import("API", "Security", "accountKey");
importer::import("DEV", "Projects", "project");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Security\account;
use \API\Security\accountKey;
use \DEV\Projects\project;

/**
 * Bug Tracker project
 * 
 * It can create virtual Bug Tracker projects for a team.
 * 
 * @version	0.1-2
 * @created	October 9, 2014, 20:03 (EEST)
 * @revised	October 17, 2014, 15:59 (EEST)
 */
class btProject extends project
{
	/**
	 * Create a virtual Bug Tracker project.
	 * The request must be from a registered account.
	 * 
	 * @param	string	$title
	 * 		The project title.
	 * 
	 * @param	string	$description
	 * 		The project description.
	 * 		It can be empty.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		The project id on success, false on failure.
	 */
	public function create($title, $description = "")
	{
		// Check that is a logged in account
		$accountID = account::getAccountID();
		if (empty($accountID))
			return FALSE;
			
		// Set the project type as virtual project
		$projectType = 10;
		
		// Create a virtual project
		$projectID = parent::create($title, $projectType, $description);
		
		// If success, add account to project
		if ($projectID)
		{
			$dbc = new dbConnection();
			$dbq = new dbQuery("17407435658338", "developer.projects");
			$attr = array();
			$attr['aid'] = $accountID;
			$attr['pid'] = $projectID;
			$status = $dbc->execute($dbq, $attr);
			
			// Create account key for the given role
			if ($status)
			{
				privileges::addAccountToGroupID($accountID, 7);
				accountKey::create(7, 2, $projectID, $accountID);
				
				return $projectID;
			}
			
			return NULL;
		}
		
		return FALSE;
	}
}
//#section_end#
?>