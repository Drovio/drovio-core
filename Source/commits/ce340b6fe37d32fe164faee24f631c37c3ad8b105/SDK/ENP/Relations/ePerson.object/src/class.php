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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("ENP", "Comm", "rdbConnection");
importer::import("ENP", "Relations", "ePersonMail");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \API\Profile\team;
use \ENP\Comm\rdbConnection;
use \ENP\Relations\ePersonMail;

/**
 * Enterprise person relation
 * 
 * This is an abstract person relation manager for teams.
 * 
 * @version	7.1-1
 * @created	July 24, 2015, 12:15 (EEST)
 * @updated	September 16, 2015, 12:12 (EEST)
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
	 * Update person information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function getConnectedPersonInfo()
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		// Get current relation person info
		$personInfo = $this->info();
		$personID = $personInfo['drovio_person_id'];
		if (empty($personID))
			return NULL;
			
		// Get account info from database
		$dbc = new dbConnection();
		$q = new dbQuery("34727623921168", "profile.account");
		$attr = array();
		$attr['pid'] = $personID;
		$result = $dbc->execute($q, $attr);
		$info = $dbc->fetch($result);
		$accountInfo = account::info($info['id']);
		
		// Get person info
		$dbc = new dbConnection();
		$q = new dbQuery("1921568048", "profile.person");
		$attr = array();
		$attr['pid'] = $personID;
		$result = $dbc->execute($q, $attr);
		$personInfo = $dbc->fetch($result);
		
		// Return merged info
		return array_merge($accountInfo, $personInfo);
	}
	
	/**
	 * Connect the relation person to an existing drovio person account.
	 * 
	 * @param	integer	$personID
	 * 		The drovio person id to connect to.
	 * 
	 * @return	boolean
	 * 		True on success, false otherwise.
	 */
	public function connectToPerson($personID)
	{
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
			
		// Check that the connection is valid
		$valid = FALSE;
		
		// Get person info
		$dbc = new dbConnection();
		$q = new dbQuery("1921568048", "profile.person");
		$attr = array();
		$attr['pid'] = $personID;
		$result = $dbc->execute($q, $attr);
		$personInfo = $dbc->fetch($result);
		
		// Get all mail
		$ePersonMail = new ePersonMail($this->personID);
		$allMail = $ePersonMail->getAllMail();
		foreach ($allMail as $mailID => $mailInfo)
			if ($mailInfo['mail'] == $personInfo['mail'])
				$valid = TRUE;
				
		// If not valid, return FALSE
		if (!$valid)
			return FALSE;
			
		// Connect to drovio person
		$rdbc = new rdbConnection();
		$q = new dbQuery("15436894461926", "enterprise.relations.persons");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		$attr['dpid'] = $personID;
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Get the candidate drovio person ids that matches the current person with any email.
	 * 
	 * @return	integer
	 * 		An array of the matched drovio person ids.
	 */
	public function getDrovioPersonsByMail()
	{
		$matchedPersonIDs = array();
		
		// Get all mail
		$ePersonMail = new ePersonMail($this->personID);
		$allMail = $ePersonMail->getAllMail();
		foreach ($allMail as $mailID => $mailInfo)
		{
			// Get person by mail
			$dbc = new dbConnection();
			$q = new dbQuery("1206709991", "profile.person");
			$attr = array();
			$attr['mail'] = $mailInfo['mail'];
			$result = $dbc->execute($q, $attr);
			if ($dbc->get_num_rows($result) > 0)
			{
				$personInfo = $dbc->fetch($result);
				$matchedPersonIDs[] = $personInfo['id'];
			}
		}
		
		// Return the list of matched person ids
		return $matchedPersonIDs;
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
}
//#section_end#
?>