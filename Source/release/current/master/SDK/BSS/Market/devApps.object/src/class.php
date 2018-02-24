<?php
//#section#[header]
// Namespace
namespace BSS\Market;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BSS
 * @package	Market
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");
importer::import("BSS", "Market", "appMarket");
importer::import("DEV", "Projects", "project");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \BSS\Market\appMarket;
use \DEV\Projects\project;

/**
 * Developer's application finder.
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	October 13, 2015, 22:17 (EEST)
 * @updated	October 13, 2015, 22:17 (EEST)
 */
class devApps
{
	/**
	 * It gets all applications for the current account that are in development mode and not in market or enterprise dashboard.
	 * 
	 * @return	array
	 * 		An array of all applications.
	 */
	public static function getDevApplications()
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
			
		// Get all market projects
		$marketApps = appMarket::getTeamApplications();
		
		// Get all account's projects and filter
		$allProjects = project::getAccountProjects();
		$devProjects = array();
		foreach ($allProjects as $projectInfo)
		{
			// Check only applications
			if ($projectInfo['projectType'] != 4)
				continue;
				
			// Get project id
			$projectID = $projectInfo['id'];
			
			// Check if project is already in market
			if ($marketApps[$projectID])
				continue;
			
			// Get detailed project info
			$project = new project($projectID);
			$devProjects[$projectID] = $project->info();
		}
		
		return $devProjects;
	}
}
//#section_end#
?>