<?php
//#section#[header]
// Namespace
namespace API\Login;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Login
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Login", "account");
importer::import("API", "Platform", "engine");
importer::import("API", "Security", "akeys/apiKey");
importer::import("DRVC", "Profile", "team");
importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "session");
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("SYS", "Resources", "pages/domain");

use \API\Login\account;
use \API\Platform\engine;
use \API\Security\akeys\apiKey;
use \DRVC\Profile\team as IDTeam;
use \ESS\Environment\cookies;
use \ESS\Environment\url;
use \ESS\Environment\session;
use \ESS\Protocol\AsCoProtocol;
use \SYS\Resources\pages\domain;

importer::import("API", "Model", "sql/dbQuery");
importer::import("SYS", "Comm", "db/dbConnection");

use \API\Model\sql\dbQuery;
use \SYS\Comm\db\dbConnection;

/**
 * Drovio Login Team Interface
 * 
 * Manages the team login for the drovio platform using the drovio identity.
 * 
 * @version	0.1-2
 * @created	December 13, 2015, 18:58 (GMT)
 * @updated	December 14, 2015, 6:59 (GMT)
 */
class team
{
	/**
	 * The system team name for the identity database.
	 * 
	 * @type	string
	 */
	const ID_TEAM_NAME = "drovio";
	
	/**
	 * Indicates whether the team is logged in for this run.
	 * 
	 * @type	boolean
	 */
	private static $loggedIn = FALSE;
	
	/**
	 * The current team id.
	 * 
	 * @type	integer
	 */
	private static $teamID = NULL;
	
	/**
	 * Get team information.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 		Leave empty for current team id.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of the team information.
	 */
	public static function info($teamID = "")
	{
		// Get current team id if empty
		$teamID = (empty($teamID) ? self::getTeamID() : $teamID);
		
		// Get public information
		return self::getTeamInstance()->info($teamID);
	}
	
	/**
	 * Update team information.
	 * 
	 * @param	string	$name
	 * 		The team name.
	 * 
	 * @param	string	$description
	 * 		The team description.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 		If empty or in secure mode this will be the current team.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateInfo($name, $description = "", $teamID = "")
	{
		// Get team id
		$teamID = (empty($teamID) || importer::secure() ? self::getTeamID() : $teamID);
		
		// Update identity team info
		return self::getTeamInstance()->updateInfo($teamID, $name, $description);
	}
	
	/**
	 * Validates if the current account is member of the current team.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to validate the account.
	 * 		If empty, get the current team id.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validate($teamID = "")
	{
		// Get team id
		$teamID = (empty($teamID) ? self::getTeamID() : $teamID);
		if (empty($teamID))
		{
			// Logout from team
			self::logout();
			return FALSE;
		}
		
		// Check loggedIn
		if (self::$loggedIn)
			return self::$loggedIn;
		
		// Validate team
		if (!self::getTeamInstance()->validate($teamID))
		{
			// Logout from team
			self::logout();
			return FALSE;
		}
		
		// Set cache and return
		self::$teamID = $teamID;
		self::$loggedIn = TRUE;
		return TRUE;
	}
	
	/**
	 * Logout the account from the team.
	 * 
	 * @return	void
	 */
	public static function logout()
	{
		// Set the current team id as null
		self::$teamID = NULL;
		
		// Set logged in as false
		self::$loggedIn = FALSE;
		
		// Delete all team cookies
		cookies::remove("tm");
	}
	
	/**
	 * Switch from one team to another.
	 * The account must be valid and member of the given team id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to switch to.
	 * 
	 * @param	string	$password
	 * 		The account's current password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function switchTeam($teamID, $password)
	{
		// Check if it's a valid account
		if (!account::getInstance()->validate())
			return FALSE;

		// Validate account member to given team
		if (!self::validate($teamID))
			return FALSE;

		// Authenticate account and switch team
		$username = account::getInstance()->getUsername(TRUE);
		if (account::getInstance()->authenticate($username, $password))
		{
			// Set Cookies again
			if (account::getInstance()->rememberme())
				$duration = 30 * 24 * 60 * 60;
			
			self::$teamID = $teamID;
			self::$loggedIn = TRUE;
			return cookies::set("tm", $teamID, $duration, TRUE);
		}
		
		return FALSE;
	}
	
	/**
	 * Get the default team for the given account.
	 * 
	 * @return	array
	 * 		The default team information.
	 */
	public static function getDefaultTeam()
	{
		return self::getTeamInstance()->getDefaultTeam();
	}	
	
	/**
	 * Set the default team for the given account.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to set as default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setDefaultTeam($teamID)
	{
		return self::getTeamInstance()->setDefaultTeam($teamID);
	}
	
	/**
	 * Get all teams of the current account.
	 * 
	 * @param	boolean	$detailed
	 * 		Get full team details.
	 * 		It is TRUE by default.
	 * 
	 * @return	array
	 * 		An array of all teams.
	 */
	public static function getAccountTeams($detailed = TRUE)
	{
		// Get team information
		$dbc = new dbConnection();
		$q = new dbQuery("15481774027848", "profile.team");
		$attr = array();
		$attr['aid'] = account::getInstance()->getAccountID();
		$result = $dbc->execute($q, $attr);
		$teams = $dbc->fetch($result, TRUE);
		
		/*
		// Get account teams
		$teams = self::getTeamInstance()->getAccountTeams();*/
		if (!$detailed)
			return $teams;
			
		// Create team list
		$teamList = array();
		foreach ($teams as $team)
			$teamList[] = self::info($team['id']);
		
		return $teamList;
	}
	
	/**
	 * Get all team member accounts.
	 * 
	 * @return	array
	 * 		An array of all public account information for each member.
	 */
	public static function getTeamMembers()
	{
		return self::getTeamInstance()->getTeamAccounts(self::getTeamID());
	}
	
	/**
	 * Get the current team id.
	 * 
	 * @return	integer
	 * 		The current team id.
	 */
	public static function getTeamID()
	{
		// Check cache
		if (isset(self::$teamID) && !empty(self::$teamID))
			return self::$teamID;
		
		// Get domain team name
		$domainTeamName = self::getDomainTeamName();
		if (is_null($domainTeamName))
			return self::$teamID = self::getEngineTeamID();
		
		// Get teams from session
		$teams = session::get("teams", NULL, "profile");
		if (empty($teams) || empty($teams[$subdomain]))
		{
			// Get all account teams
			$teamList = self::getAccountTeams(FALSE);
			$teams = array();
			foreach ($teamList as $teamInfo)
				if (!empty($teamInfo['uname']))
					$teams[strtolower($teamInfo['uname'])] = $teamInfo['id'];

			// Store teams to session
			if (!empty($teams))
				session::set("teams", $teams, "profile");
		}
		
		// Check if it is a valid team
		if (isset($teams[$domainTeamName]))
			return self::$teamID = $teams[$domainTeamName];
		
		// Get team id from engine (dev or api)
		return self::$teamID = self::getEngineTeamID();
	}
	
	/**
	 * Get the current identity team name.
	 * It will check based on the request url.
	 * The format is [identity_team_name].id.[domain_team_name].drov.io
	 * 
	 * @return	string
	 * 		The active identity team name.
	 * 		NULL if there is no active team.
	 * 		If NULL, drovio should be the active team name.
	 */
	public static function getIdentityTeamName()
	{
		// Get subdomain and check that it is not taken
		$subdomain = url::getSubDomain();
		if (AsCoProtocol::exists())
			$subdomain = AsCoProtocol::getSubdomain();
		
		// Check if it's in the right format
		$pattern = '/(.*)\.id\.?(.*)$/i';
		$status = preg_match($pattern, $subdomain, $matches);
		if (!$status)
			return NULL;
		
		// Get team name
		return $matches[1];
	}
	
	/**
	 * Get the team name from the request url.
	 * The format is [identity_team_name].id.[domain_team_name].drov.io
	 * 
	 * @return	string
	 * 		The current active team name.
	 */
	public static function getDomainTeamName()
	{
		// CHECK DROVIO DOMAIN
		$domain = url::getDomain();
		if ($domain != "drov" && $domain != "drov.io")
			return NULL;
		
		// Get subdomain and check that it is not taken
		if (AsCoProtocol::exists())
			$subdomain = AsCoProtocol::getSubdomain();
		else
			$subdomain = url::getSubDomain();
		
		// Check www domain
		if (empty($subdomain) || $subdomain == "www")
			return NULL;
		
		// Get all current subdomains
		$system_domains = session::get("domains", NULL, "platform");
		if (empty($system_domains))
		{
			$system_domains = domain::getAllDomains();
			session::set("domains", $system_domains, "platform");
		}
		
		// Check all subdomains if one is valid
		foreach ($system_domains as $domainInfo)
			if ($domainInfo['domain'] == $subdomain)
				return NULL;
		
		// Set current tema name
		$domainTeamName = $subdomain;
		
		// Get identity team name (if any)
		$IdentityTeamName = self::getIdentityTeamName();
		if (!empty($IdentityTeamName))
			$domainTeamName = str_replace($IdentityTeamName.".id", "", $domainTeamName);
		
		return trim($domainTeamName, ".");
	}
	
	/**
	 * Get the team id from engine variables.
	 * Check first if there is an api key and then the current cookie ('tm', for dev).
	 * 
	 * @return	mixed
	 * 		The team id or NULL if no active team.
	 */
	protected static function getEngineTeamID()
	{
		// Set team id from api
		$akey = engine::getVar("akey");
		if (!empty($akey))
		{
			$keyInfo = apiKey::info($akey);
			return $keyInfo['team_id'];
		}
		
		// Get team from engine
		$engineTeamID = engine::getVar("tm");
		if (!empty($engineTeamID))
			return $engineTeamID;
		
		return NULL;
	}
	
	/**
	 * Get the identity team instance for the drovio identity.
	 * 
	 * @return	team
	 * 		The identity team instance.
	 */
	protected static function getTeamInstance()
	{
		return IDTeam::getInstance(self::ID_TEAM_NAME);
	}
}
//#section_end#
?>