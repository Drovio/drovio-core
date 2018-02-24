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
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Platform", "engine");
importer::import("API", "Security", "akeys/apiKey");
importer::import("DRVC", "Profile", "team");
importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "session");
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("SYS", "Resources", "pages/domain");

use \API\Login\account;
use \API\Model\sql\dbQuery;
use \API\Platform\engine;
use \API\Security\akeys\apiKey;
use \DRVC\Profile\team as IDTeam;
use \ESS\Environment\cookies;
use \ESS\Environment\url;
use \ESS\Environment\session;
use \ESS\Protocol\AsCoProtocol;
use \SYS\Comm\db\dbConnection;
use \SYS\Resources\pages\domain;

/**
 * Drovio Login Team Interface
 * 
 * Manages the team login for the drovio platform using the drovio identity.
 * 
 * @version	2.0-2
 * @created	December 13, 2015, 18:58 (GMT)
 * @updated	December 17, 2015, 12:55 (GMT)
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
		$teamID = (empty($teamID) ? static::getTeamID() : $teamID);
		
		// Get public information
		$teamInfo = static::getTeamInstance()->info($teamID);
		if (empty($teamInfo))
		{
			// Get team information
			$dbc = new dbConnection();
			$q = new dbQuery("16182138337279", "profile.team");
			$attr = array();
			$attr['tid'] = $teamID;
			$result = $dbc->execute($q, $attr);
			return $dbc->fetch($result);
		}
		
		return $teamInfo;
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
		$teamID = (empty($teamID) || importer::secure() ? static::getTeamID() : $teamID);
		
		// Update identity team info
		return static::getTeamInstance()->updateInfo($teamID, $name, $description);
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
		$teamID = (empty($teamID) ? static::getTeamID() : $teamID);
		if (empty($teamID))
		{
			// Logout from team
			static::logout();
			return FALSE;
		}
		
		// Check loggedIn
		if (self::$loggedIn)
			return self::$loggedIn;
		
		// Validate team
		if (!static::getTeamInstance()->validate($teamID))
		{
			// Logout from team
			static::logout();
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
		if (!static::validate($teamID))
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
		return static::getTeamInstance()->getDefaultTeam();
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
		return static::getTeamInstance()->setDefaultTeam($teamID);
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
		// Get account teams
		$teams = static::getTeamInstance()->getAccountTeams();
		if (!$detailed)
			return $teams;
		
		// Create team list
		$teamList = array();
		foreach ($teams as $team)
			$teamList[] = static::info($team['id']);
		
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
		return static::getTeamInstance()->getTeamAccounts(static::getTeamID());
	}
	
	/**
	 * Add an account to a team.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to accept the account.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to add to the team.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function addTeamAccount($teamID, $accountID)
	{
		return static::getTeamInstance()->addTeamAccount($teamID, $accountID);
	}
	
	/**
	 * Remove an account from a team.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to remove the account from.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to remove from the team.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeTeamAccount($teamID, $accountID)
	{
		return static::getTeamInstance()->removeTeamAccount($teamID, $accountID);
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
		$domainTeamName = static::getDomainTeamName();
		if (is_null($domainTeamName))
			return self::$teamID = static::getEngineTeamID();
		
		// Get teams from session
		$teams = session::get("teams", NULL, "profile");
		if (empty($teams) || empty($teams[$subdomain]))
		{
			// Get all account teams
			$teamList = static::getAccountTeams(FALSE);
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
		return self::$teamID = static::getEngineTeamID();
	}
	
	/**
	 * Get the current identity team name.
	 * It will check based on the request url.
	 * The format is [identity_team_name].id.[domain_team_name].drov.io
	 * It also supports the engine variable "id_team".
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
		{
			// Get engine variable
			return engine::getVar("id_team");
		}
		
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
		$IdentityTeamName = static::getIdentityTeamName();
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
	public static function getTeamInstance()
	{
		// Get current identity team
		$identityTeam = static::getIdentityTeamName();
		$identityTeam = (empty($identityTeam) ? static::ID_TEAM_NAME : $identityTeam);
		
		// Get identity team instance
		return IDTeam::getInstance($identityTeam, account::getInstance()->getAuthToken());
	}
}
//#section_end#
?>