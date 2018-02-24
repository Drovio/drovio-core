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
 * @version	1.0-1
 * @created	October 9, 2014, 20:03 (EEST)
 * @revised	October 20, 2014, 15:37 (EEST)
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
	
	/**
	 * Get all issues of a project in a given version (optional).
	 * 
	 * @param	string	$version
	 * 		The project's version.
	 * 		You can leave empty for all versions.
	 * 
	 * @return	array
	 * 		An array of all issues in the given version.
	 * 		If no version is given, the array contains all issues for all versions by version id (key).
	 */
	public function getIssues($version = "")
	{
		// Initialize issues
		$issues = array();
		
		// Check empty version
		if (empty($version))
		{
			// If version is empty, get issues for each version
			$releases = $this->getReleases();
			foreach ($releases as $relInfo)
				$issues[$relInfo['version']] = $this->getIssues($relInfo['version']);
		}
		else
		{
			// Get issues for the given version
			$dbc = new dbConnection();
			$dbq = new dbQuery("25225848931745", "developer.issues");
			$attr = array();
			$attr['pid'] = $this->getID();
			$attr['version'] = $version;
			$result = $dbc->execute($dbq, $attr);
			$issues = $dbc->fetch($result, TRUE);
		}
		
		// Return issues list
		return $issues;
	}
}
//#section_end#
?>