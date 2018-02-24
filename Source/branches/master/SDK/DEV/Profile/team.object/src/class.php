<?php
//#section#[header]
// Namespace
namespace DEV\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");

use \ESS\Environment\session;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team as APITeam;

/**
 * Developer team profile
 * 
 * Manages developer plans.
 * 
 * @version	0.1-1
 * @created	July 3, 2015, 14:20 (EEST)
 * @updated	July 3, 2015, 14:20 (EEST)
 */
class team
{
	/**
	 * The free plan id.
	 * 
	 * @type	integer
	 */
	const FREE_PLAN = 1;
	/**
	 * The basic plan id.
	 * 
	 * @type	integer
	 */
	const BASIC_PLAN = 2;
	/**
	 * The premium plan id.
	 * 
	 * @type	integer
	 */
	const PREMIUM_PLAN = 3;
	/**
	 * The enterprise plan id.
	 * 
	 * @type	integer
	 */
	const ENTERPRISE_PLAN = 4;
	
	/**
	 * The month type plan.
	 * 
	 * @type	integer
	 */
	const MONTH_TYPE = 1;
	/**
	 * The year type plan.
	 * 
	 * @type	integer
	 */
	const YEAR_TYPE = 2;
	
	/**
	 * Get current plan for the given team.
	 * 
	 * @param	boolean	$live
	 * 		Whether to get the plan live form database or use cache.
	 * 		It is FALSE by default.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to get the plan for.
	 * 		If empty get the current developer team.
	 * 		It is empty by default.
	 * 
	 * @return	integer
	 * 		The plan id.
	 */
	public static function getCurrentPlan($live = FALSE, $teamID = "")
	{
		// Get team id to get plan for
		$teamID = (empty($teamID) ? APITeam::getTeamID() : $teamID);
		
		// Check session
		$sessionValue = session::get("team_plan_".$teamID, NULL, "dev_plans");
		if (empty(!$sessionValue) && !$live)
			return $sessionValue;
		
		// Load plan from db
		$dbc = new dbConnection();
		$dbq = new dbQuery("22387433952349", "developer.plans");
		$attr = array();
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		$info = $dbc->fetch($result);
		if (empty($info))
		{
			self::setCurrentPlan(self::FREE_PLAN, self::YEAR_TYPE, $teamID);
			return self::FREE_PLAN;
		}
		
		// Get plan, add to session and return
		$planID = $info['plan_id'];
		session::set("team_plan_".$teamID, $planID, "dev_plans");
		return $planID;
	}
	
	/**
	 * Set current plan for the given team.
	 * 
	 * @param	integer	$planID
	 * 		The plan id.
	 * 		See class constants.
	 * 
	 * @param	integer	$type
	 * 		The plan type
	 * 		See class constants.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to set the plan for.
	 * 		If empty get the current developer team.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setCurrentPlan($planID = self::FREE_PLAN, $type = self::YEAR_TYPE, $teamID = "")
	{
		// Get team id to get plan for
		$teamID = (empty($teamID) ? APITeam::getTeamID() : $teamID);
		
		// Update plan to database
		$dbc = new dbConnection();
		$dbq = new dbQuery("29999454651701", "developer.plans");
		$attr = array();
		$attr['tid'] = $teamID;
		$attr['pid'] = $planID;
		$attr['time'] = time();
		$attr['type'] = $type;
		$result = $dbc->execute($dbq, $attr);
		if (!$result)
			return FALSE;
		
		// Set session and return true
		session::set("team_plan_".$teamID, $planID, "dev_plans");
		return TRUE;
	}
	
	/**
	 * Get the plan short name for the given id.
	 * 
	 * @param	integer	$planID
	 * 		The plan id to get the name for.
	 * 
	 * @return	string
	 * 		The plan name.
	 */
	public static function getPlanName($planID)
	{
		switch ($planID)
		{
			case 1:
				return "free";
			case 2:
				return "basic";
			case 3:
				return "premium";
			case 4:
				return "enterprise";
		}
		
		return NULL;
	}
}
//#section_end#
?>