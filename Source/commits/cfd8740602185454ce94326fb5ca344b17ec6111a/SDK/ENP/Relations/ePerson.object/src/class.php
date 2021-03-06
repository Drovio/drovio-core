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
importer::import("ENP", "Comm", "rdbConnection");
importer::import("ENP", "Profile", "company");
importer::import("ENP", "Relations", "ePersonMail");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \ENP\Comm\rdbConnection;
use \ENP\Profile\company;
use \ENP\Relations\ePersonMail;

/**
 * Enterprise person relation
 * 
 * This is an abstract person relation manager for teams.
 * 
 * @version	4.0-1
 * @created	July 24, 2015, 12:15 (EEST)
 * @updated	August 20, 2015, 17:41 (EEST)
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
		$teamID = company::getCompanyID();
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
	 * Get all information for a given person.
	 * 
	 * @return	array
	 * 		The person's stored information.
	 * 		If the relation has been approved by the person, it will return public information.
	 */
	public function info()
	{
		// Get current team id
		$teamID = company::getCompanyID();
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
	 * Update person information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function getConnectedPersonInfo()
	{
		// Get current team id
		$teamID = company::getCompanyID();
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
		// Validate that the connection is valid
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
		foreach ($alMail as $mailID => $mailAddress)
			if ($mailAddress == $personInfo['mail'])
				$valid = TRUE;
		
		// If not valid, return FALSE
		if (!$valid)
			return FALSE;
			
		// Connect to drovio person
		$edbc = new rdbConnection();
		$q = new dbQuery("19684028026155", "enterprise.relations.persons");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['dpid'] = $personID;
		$result = $edbc->execute($q, $attr);
		return $edbc->fetch($result);
	}
	
	/**
	 * Get the candidate drovio person id that matches the current person with any email.
	 * 
	 * @return	integer
	 * 		The matched drovio person id.
	 */
	public function getDrovioPersonByMail()
	{
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
				return $personInfo['id'];
			}
		}
		
		// No person found, return null
		return NULL;
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($firstname, $lastname, $middlename = "", $notes = "")
	{
		// Get current team id
		$teamID = company::getCompanyID();
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
		return $edbc->execute($q, $attr);
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
		$teamID = company::getCompanyID();
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