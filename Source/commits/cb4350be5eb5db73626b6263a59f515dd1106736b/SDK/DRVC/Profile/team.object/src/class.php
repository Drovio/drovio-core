<?php
//#section#[header]
// Namespace
namespace DRVC\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DRVC
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("API", "Platform", "engine");
importer::import("API", "Security", "akeys/apiKey");
importer::import("DRVC", "Comm", "dbConnection");

use \ESS\Environment\session;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \API\Platform\engine;
use \API\Security\akeys\apiKey;
use \DRVC\Comm\dbConnection;

/**
 * Identity Team Manager Class
 * 
 * Manages teams for the identity API.
 * 
 * @version	0.1-1
 * @created	December 13, 2015, 18:07 (GMT)
 * @updated	December 13, 2015, 18:07 (GMT)
 */
class team
{
	/**
	 * Cache for getting team info.
	 * 
	 * @type	array
	 */
	private $teamInfo = array();
	
	/**
	 * The team to access the identity database.
	 * 
	 * @type	string
	 */
	private $teamName = "";
	
	/**
	 * The identity database connection.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The current account instance.
	 * 
	 * @type	account
	 */
	private $account;
	
	/**
	 * An array of instances for each team identity (in case of multiple instances).
	 * 
	 * @type	array
	 */
	private static $instances = array();
	
	/**
	 * Get a team account instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	team
	 * 		The team instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instances[$teamName]))
			self::$instances[$teamName] = new team($teamName);
		
		// Return instance
		return self::$instances[$teamName];
	}
	
	/**
	 * Create a new team instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	void
	 */
	protected function __construct($teamName)
	{
		$this->teamName = $teamName;
		$this->dbc = new dbConnection($this->teamName);
		$this->account = account::getInstance($this->teamName);
	}
	
	/**
	 * Get information about the given team id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @return	array
	 * 		An array of the team information.
	 */
	public function info($teamID)
	{
		if (!empty($this->teamInfo[$teamID]))
			return $this->teamInfo[$teamID];
		
		// Get team information
		$q = new dbQuery("32631080802434", "identity.team");
		$attr = array();
		$attr['tid'] = $teamID;
		$result = $this->dbc->execute($q, $attr);
		return $this->teamInfo[$teamID] = $this->dbc->fetch($result);
	}
	
	/**
	 * Update team information.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to update information for.
	 * 
	 * @param	string	$name
	 * 		The team name.
	 * 		It cannot be empty.
	 * 
	 * @param	string	$description
	 * 		A team description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($teamID, $name, $description = "")
	{
		// Check name
		if (empty($name))
			return FALSE;
		
		// Update Information
		$q = new dbQuery("2432633615587", "identity.team");
		$attr = array();
		$attr['tid'] = $teamID;
		$attr['name'] = $name;
		$attr['description'] = $description;
		$status = $this->dbc->execute($q, $attr);
		if ($status)
			unset($this->teamInfo[$teamID]);
		
		return $status;
	}
	
	/**
	 * Create a new team and set the current account as owner.
	 * 
	 * @param	string	$uname
	 * 		The team unique name.
	 * 
	 * @param	string	$name
	 * 		The team normal name.
	 * 
	 * @param	string	$description
	 * 		The team description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($uname, $name, $description = "")
	{
		// Validate account
		if (!$this->account->validate())
			return FALSE;
		
		// Normalize uname
		$uname = trim($uname);
		$uname = str_replace(" ", "_", $uname);
		$uname = str_replace(".", "_", $uname);
		
		// Check name
		if (empty($uname) || empty($name))
			return FALSE;
		
		// Update Information
		$q = new dbQuery("15778076543705", "identity.team");
		$attr = array();
		$attr['uname'] = $uname;
		$attr['name'] = $name;
		$attr['description'] = $description;
		$attr['aid'] = $this->account->getAccountID();
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Validates if the current account is member of the given team.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to validate the account.
	 * 
	 * @return	boolean
	 * 		True if is member, false otherwise.
	 */
	public function validate($teamID)
	{
		// Validate account
		if (!$this->account->validate())
			return FALSE;
		
		// Get current account id
		$accountID = $this->account->getAccountID();

		// Check if there is a valid team
		if (empty($teamID) || empty($accountID))
			return FALSE;
		
		// Check if account is in team
		$q = new dbQuery("25638597161503", "identity.team");
		$attr = array();
		$attr['tid'] = $teamID;
		$attr['aid'] = $accountID;
		$result = $this->dbc->execute($q, $attr);
		
		// Check account into team
		if ($this->dbc->get_num_rows($result) > 0)
			return TRUE;
		
		// Not valid
		return FALSE;
	}
	
	/**
	 * Get the default team for the current account.
	 * 
	 * @return	array
	 * 		The default team information.
	 */
	public function getDefaultTeam()
	{
		// Check if it's a valid account
		if (!$this->account->validate())
			return FALSE;
		
		// Get default team
		$q = new dbQuery("3486211728093", "identity.team");
		$attr = array();
		$attr['aid'] = $this->account->getAccountID();
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result);
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
	public function setDefaultTeam($teamID)
	{
		// Check if it's a valid account
		if (!$this->account->validate())
			return FALSE;
		
		// Validate account member to given team
		if (!$this->validate($teamID))
			return FALSE;
		
		// Set default team
		$q = new dbQuery("23211170490594", "identity.team");
		$attr = array();
		$attr['aid'] = $this->account->getAccountID();
		$attr['tid'] = $teamID;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all teams of the current account.
	 * 
	 * @return	array
	 * 		An array of all teams' information.
	 */
	public function getAccountTeams()
	{
		// Check if it's a valid account
		if (!$this->account->validate())
			return FALSE;
		
		// Get account teams from database
		$q = new dbQuery("32625895787891", "identity.team");
		$attr = array();
		$attr['aid'] = $this->account->getAccountID();
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all team member accounts.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to get accounts for.
	 * 
	 * @return	array
	 * 		An array of all account information for each member.
	 */
	public function getTeamAccounts($teamID)
	{
		// Get team accounts
		$q = new dbQuery("15969829439705", "identity.team");
		$attr = array();
		$attr['tid'] = $teamID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>