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

importer::import("API", "Geoloc", "region");
importer::import("API", "Literals", "literal");
importer::import("API", "Model", "sql/dbQuery");
importer::import("ENP", "Comm", "rdbConnection");
importer::import("ENP", "Profile", "company");
importer::import("ENP", "Relations", "ePerson");

use \API\Geoloc\region;
use \API\Literals\literal;
use \API\Model\sql\dbQuery;
use \ENP\Comm\rdbConnection;
use \ENP\Profile\company;
use \ENP\Relations\ePerson;

/**
 * Person mail address manager
 * 
 * Manages all person's mail addresses
 * 
 * @version	0.1-2
 * @created	August 20, 2015, 15:50 (EEST)
 * @updated	August 20, 2015, 17:41 (EEST)
 */
class ePersonMail
{
	/**
	 * The current person id.
	 * 
	 * @type	integer
	 */
	protected $personID;
	
	/**
	 * The current mail address id.
	 * 
	 * @type	integer
	 */
	protected $mailAddressID;
	
	/**
	 * Whether the person is valid with the current company.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Create an instance of the person mail address manager.
	 * 
	 * @param	integer	$personID
	 * 		The person id to manage mail addresses for.
	 * 
	 * @param	integer	$mailAddressID
	 * 		The address id.
	 * 		Leave empty for new addresses.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($personID, $mailAddressID = "")
	{
		$this->personID = $personID;
		$this->mailAddressID = $mailAddressID;
	}
	
	/**
	 * Create a new person mail address.
	 * 
	 * @param	integer	$typeID
	 * 		The mail address type id.
	 * 		Use getMailTypes() to get all types.
	 * 
	 * @param	string	$mailAddress
	 * 		The mail address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($typeID, $mailAddress)
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Create person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("25037542966558", "enterprise.relations.persons.mail");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['type'] = $typeID;
		$attr['mail'] = $mailAddress;
		$result = $rdbc->execute($q, $attr);
		if ($result)
		{
			$mailAddressInfo = $rdbc->fetch($result);
			$this->mailAddressID = $mailAddressInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update the mail info.
	 * 
	 * @param	integer	$typeID
	 * 		The mail address type id.
	 * 		Use getMailTypes() to get all types.
	 * 
	 * @param	string	$mailAddress
	 * 		The new mail address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($typeID, $mailAddress)
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Update person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("16621804181953", "enterprise.relations.persons.mail");
		$attr = array();
		$attr['id'] = $this->mailAddressID;
		$attr['pid'] = $this->personID;
		$attr['type'] = $typeID;
		$attr['mail'] = $mailAddress;
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Remove the current mail address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Remove person mail
		$rdbc = new rdbConnection();
		$q = new dbQuery("32237597614957", "enterprise.relations.persons.mail");
		$attr = array();
		$attr['id'] = $this->mailAddressID;
		$attr['pid'] = $this->personID;
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Get all mail address types.
	 * 
	 * @return	array
	 * 		An array of types by id and name.
	 */
	public static function getMailTypes()
	{
		// Update person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("33558636096531", "enterprise.relations.persons.mail");
		$result = $rdbc->execute($q);
		
		$types = array();
		$tliterals = literal::get($scope = "enterprise.relations.mail", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $rdbc->fetch($result))
			$types[$row['id']] = $tliterals["type_".$row['name']];
		
		return $types;
	}
	
	/**
	 * Get all person mail addresses.
	 * 
	 * @return	void
	 */
	public function getAllMail()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
		
		// Get all addresses
		$rdbc = new rdbConnection();
		$q = new dbQuery("16717830941164", "enterprise.relations.persons.mail");
		$attr = array();
		$attr['pid'] = $this->personID;
		$result = $rdbc->execute($q, $attr);
		
		$allMailAddresses = array();
		$tliterals = literal::get($scope = "enterprise.relations.mail", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $rdbc->fetch($result))
		{
			$row['type'] = $tliterals["type_".$row['type']];
			$allMailAddresses[$row['id']] = $row;
		}
		
		// Return all addresses
		return $allMailAddresses;
	}
	
	/**
	 * Validate whether the given person is member of the current team.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	private function validate()
	{
		// Check class cache
		if ($this->valid)
			return $this->valid;
			
		// Get current team/company id
		$teamID = company::getCompanyID();
		if (empty($teamID))
			return FALSE;
		
		// Get person's owner team/company id
		$person = new ePerson($this->personID);
		$personInfo = $person->info();
		
		// Check if it is the same team/company
		$this->valid = ($personInfo['owner_company_id'] == $teamID);
		return $this->valid;
	}
}
//#section_end#
?>