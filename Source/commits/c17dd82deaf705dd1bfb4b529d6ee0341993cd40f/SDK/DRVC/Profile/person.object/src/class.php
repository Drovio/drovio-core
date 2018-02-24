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

importer::import("API", "Model", "sql/dbQuery");
importer::import("DRVC", "Comm", "dbConnection");
importer::import("DRVC", "Profile", "account");

use \API\Model\sql\dbQuery;
use \DRVC\Comm\dbConnection;
use \DRVC\Profile\account;

/**
 * Person Identity
 * 
 * Manages a person identity
 * 
 * @version	2.0-1
 * @created	October 8, 2015, 12:29 (BST)
 * @updated	October 22, 2015, 18:17 (BST)
 */
class person
{
	/**
	 * The team name to access the identity database.
	 * 
	 * @type	string
	 */
	protected $teamName = "";
	
	/**
	 * All person information.
	 * 
	 * @type	array
	 */
	protected $info = array();
	
	/**
	 * The identity database connection.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * An array of instances for each team identity (in case of multiple instances).
	 * 
	 * @type	array
	 */
	private static $instances = array();
	
	/**
	 * Static team name for compatibility.
	 * 
	 * @type	string
	 */
	private static $staticTeamName = "";
	
	/**
	 * Get an identity person instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	person
	 * 		The person instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instances[$teamName]))
			self::$instances[$teamName] = new person($teamName);
		
		// Return instance
		return self::$instances[$teamName];
	}
	
	/**
	 * Create a new person instance.
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
	}
	
	/**
	 * Initialize the identity.
	 * 
	 * @param	string	$teamName
	 * 		The team to access the identity database.
	 * 
	 * @return	void
	 */
	public static function init($teamName)
	{
		self::$staticTeamName = $teamName;
	}
	
	/**
	 * Get all person info from the logged in person.
	 * 
	 * @param	integer	$personID
	 * 		The person id to get information for.
	 * 		Leave empty for current person.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all person information.
	 */
	public function info($personID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return person::getInstance(self::$staticTeamName)->info($personID);
		
		// Get current person id
		$personID = (empty($personID) ? account::getInstance($this->teamName)->getPersonID() : $personID);

		// Check cache
		if (empty($this->info[$personID]))
		{
			// Get account info from database
			$q = new dbQuery("25113553281301", "identity.account");
			$attr = array();
			$attr['pid'] = $personID;
			$result = $this->dbc->execute($q, $attr);
			$info = $this->dbc->fetch($result);
			$accountInfo = account::getInstance($this->teamName)->info($info['id']);
			$accountInfo['accountID'] = $accountInfo['id'];
			$accountInfo['accountTitle'] = $accountInfo['title'];
			
			// Get person info
			$q = new dbQuery("27676107119787", "identity.person");
			$attr = array();
			$attr['pid'] = $personID;
			$result = $this->dbc->execute($q, $attr);
			$personInfo = $this->dbc->fetch($result);
			
			$this->info[$personID] = array_merge($accountInfo, $personInfo);
		}

		// Return information
		return $this->info[$personID];
	}
	
	/**
	 * Update person basic information.
	 * 
	 * @param	string	$firstname
	 * 		The person firstname.
	 * 
	 * @param	string	$lastname
	 * 		The person lastname.
	 * 
	 * @param	string	$middle_name
	 * 		The person middle name.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($firstname, $lastname, $middle_name = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return person::getInstance(self::$staticTeamName)->updateInfo($firstname, $lastname, $middle_name);
		
		// Update person info
		$q = new dbQuery("17222292713474", "identity.person");
		$attr = array();
		$attr['pid'] = account::getInstance($this->teamName)->getPersonID();
		$attr['firstname'] = $firstname;
		$attr['lastname'] = $lastname;
		$attr['middle_name'] = $middle_name;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get person's email.
	 * 
	 * @return	string
	 * 		The person's mail.
	 */
	public function getMail()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return person::getInstance(self::$staticTeamName)->getMail();
		
		return $this->getPersonValue("mail");
	}
	
	/**
	 * Get the person's firstname.
	 * 
	 * @return	string
	 * 		The person's firstname.
	 */
	public function getFirstname()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return person::getInstance(self::$staticTeamName)->getFirstname();
		
		return $this->getPersonValue("firstname");
	}
	
	/**
	 * Get the person's lastname.
	 * 
	 * @return	string
	 * 		The person's lastname.
	 */
	public function getLastname()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return person::getInstance(self::$staticTeamName)->getLastname();
		
		return $this->getPersonValue("lastname");
	}
	
	/**
	 * Get a person's information value from the database.
	 * 
	 * @param	string	$name
	 * 		The information name.
	 * 
	 * @return	string
	 * 		The information value.
	 */
	private function getPersonValue($name)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return person::getInstance(self::$staticTeamName)->getPersonValue($name);
		
		// Get person information
		$personInfo = $this->info();
		
		// Return requested value
		return $personInfo[$name];
	}
	
	/**
	 * Remove the given person from the database.
	 * The person must not have connected accounts.
	 * 
	 * @param	integer	$personID
	 * 		The person id to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($personID)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return person::getInstance(self::$staticTeamName)->remove();
		
		// Remove
		$q = new dbQuery("29629250756443", "identity.person");
		$attr = array();
		$attr['pid'] = $personID;
		return $this->dbc->execute($q, $attr);
	}
}
//#section_end#
?>