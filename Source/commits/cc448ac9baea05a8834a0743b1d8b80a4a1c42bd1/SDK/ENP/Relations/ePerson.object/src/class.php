<?php
//#section#[header]
// Namespace
namespace ENP\Relations;

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
 * @package	Relations
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("AEL", "Identity", "account");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");
importer::import("ENP", "Comm", "rdbConnection");
importer::import("ENP", "Relations", "ePersonMail");

use \SYS\Comm\db\dbConnection;
use \AEL\Identity\account;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \ENP\Comm\rdbConnection;
use \ENP\Relations\ePersonMail;

/**
 * Enterprise person relation
 * 
 * This is an abstract person relation manager for teams.
 * 
 * @version	8.0-1
 * @created	July 24, 2015, 10:15 (BST)
 * @updated	November 3, 2015, 16:38 (GMT)
 */
class ePerson
{
	/**
	 * The current person id.
	 * 
	 * @type	integer
	 */
	protected $personID;
	
	/**
	 * Creates a person instance.
	 * 
	 * @param	integer	$personID
	 * 		The person id.
	 * 		Leave empty for creating new person.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($personID = "")
	{
		$this->personID = $personID;
	}
	
	/**
	 * Create a new person relation.
	 * 
	 * @param	string	$firstname
	 * 		The person's first name.
	 * 
	 * @param	string	$lastname
	 * 		The person's last name.
	 * 
	 * @param	string	$middlename
	 * 		The person's middle name.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($firstname, $lastname, $middlename = "")
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		// Create person relation
		$rdbc = new rdbConnection();
		$q = new dbQuery("33886354759649", "enterprise.relations.persons");
		$attr = array();
		$attr['tid'] = $teamID;
		$attr['firstname'] = $firstname;
		$attr['lastname'] = $lastname;
		$attr['middle_name'] = $middlename;
		$result = $rdbc->execute($q, $attr);
		if ($result)
		{
			$personInfo = $rdbc->fetch($result);
			$this->personID = $personInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update person information.
	 * 
	 * @param	string	$firstname
	 * 		The person's first name.
	 * 
	 * @param	string	$lastname
	 * 		The person's last name.
	 * 
	 * @param	string	$middlename
	 * 		The person's middle name.
	 * 		It is empty by default.
	 * 
	 * @param	string	$notes
	 * 		The person's notes.
	 * 		It is empty by default.
	 * 
	 * @param	string	$dateOfBirth
	 * 		The person's date of birth as a date input element can provide.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($firstname, $lastname, $middlename = "", $notes = "", $dateOfBirth = "")
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		// Update person information
		$edbc = new rdbConnection();
		$q = new dbQuery("3410884001192", "enterprise.relations.persons");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		$attr['firstname'] = $firstname;
		$attr['lastname'] = $lastname;
		$attr['middle_name'] = $middlename;
		$attr['notes'] = $notes;
		$attr['birthday'] = $dateOfBirth;
		return $edbc->execute($q, $attr);
	}
	
	/**
	 * Remove the current person from the relations list.
	 * This will remove the person from any groups also.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		// Update person information
		$rdbc = new rdbConnection();
		$q = new dbQuery("24525066170657", "enterprise.relations.persons");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Get all information for a given person.
	 * 
	 * @return	array
	 * 		The person's stored information.
	 * 		If the relation has been approved by the person, it will return public information.
	 */
	public function info()
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
			
		// Get person info
		$edbc = new rdbConnection();
		$q = new dbQuery("19684028026155", "enterprise.relations.persons");
		$attr = array();
		$attr['pid'] = $this->personID;
		$result = $edbc->execute($q, $attr);
		return $edbc->fetch($result);
	}
	
	/**
	 * Get the current person id.
	 * 
	 * @return	string
	 * 		The current person id.
	 */
	public function getPersonID()
	{
		return $this->personID;
	}
	
	/**
	 * Get all persons for a given relation type.
	 * 
	 * @param	integer	$groupID
	 * 		The person's group id.
	 * 		If empty, get all persons.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all persons with the same relation.
	 */
	public static function getPersons($groupID = "")
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		if (empty($groupID))
		{
			// Get all relation persons
			$rdbc = new rdbConnection();
			$q = new dbQuery("2135630191546", "enterprise.relations.persons");
			$attr = array();
			$attr['tid'] = $teamID;
			$result = $rdbc->execute($q, $attr);
			return $rdbc->fetch($result, TRUE);
		}
		
		return array();
	}
	
	/**
	 * Import an account as a person and connect them.
	 * It creates a new person.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to be imported.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function import($accountID)
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		// Get account id information
		$accountInfo = account::getInstance()->info($accountID);
		if (empty($accountInfo))
			return FALSE;
		
		// Create new person
		$status = $this->create($accountInfo['firstname'], $accountInfo['lastname'], $accountInfo['middle_name']);
		if (!$status)
			return FALSE;
		
		// Connect person to account id
		$rdbc = new rdbConnection();
		$q = new dbQuery("26308784333655", "enterprise.relations.persons");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		$attr['aid'] = $accountID;
		$status = $rdbc->execute($q, $attr);
		if (!$status)
			return FALSE;
		
		// Update person email
		$emp = new ePersonMail($this->personID);
		return $emp->create($typeID = 1, $accountInfo['mail']);
	}
	
	/**
	 * Get the information of the connected identity account, if any.
	 * 
	 * @return	array
	 * 		An array of all account information.
	 * 		For more information see the Identity API.
	 */
	public function getAccountInfo()
	{
		// Get person info
		$personInfo = $this->info();
		
		// Get connected account info
		if (empty($personInfo['identity_account_id']))
			return NULL;
		
		// Get account info
		return account::getInstance()->info($personInfo['identity_account_id']);
	}
	
	
	/**
	 * Update person information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	This is a deprecated feature.
	 */
	public function getConnectedPersonInfo()
	{
		return array();
	}
	
	/**
	 * Connect the relation person to an existing drovio person account.
	 * 
	 * @param	integer	$personID
	 * 		The drovio person id to connect to.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 * 
	 * @deprecated	This is a deprecated feature.
	 */
	public function connectToPerson($personID)
	{
		return FALSE;
	}
	
	/**
	 * Get the candidate drovio person ids that matches the current person with any email.
	 * 
	 * @return	integer
	 * 		An array of the matched drovio person ids.
	 * 
	 * @deprecated	This is a deprecated feature.
	 */
	public function getDrovioPersonsByMail()
	{
		return array();
	}
}
//#section_end#
?>