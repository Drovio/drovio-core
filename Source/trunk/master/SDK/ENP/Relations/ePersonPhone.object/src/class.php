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
importer::import("API", "Profile", "team");
importer::import("ENP", "Comm", "rdbConnection");
importer::import("ENP", "Relations", "ePerson");

use \API\Geoloc\region;
use \API\Literals\literal;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \ENP\Comm\rdbConnection;
use \ENP\Relations\ePerson;

/**
 * Person phone manager
 * 
 * Manages all person's phones.
 * 
 * @version	0.1-4
 * @created	August 20, 2015, 14:28 (EEST)
 * @updated	September 1, 2015, 15:24 (EEST)
 */
class ePersonPhone
{
	/**
	 * The current person id.
	 * 
	 * @type	integer
	 */
	protected $personID;
	
	/**
	 * The current phone id.
	 * 
	 * @type	integer
	 */
	protected $phoneID;
	
	/**
	 * Whether the person is valid with the current company.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Create a new instance of the person phone manager.
	 * 
	 * @param	integer	$personID
	 * 		The person id to manage phones for.
	 * 
	 * @param	integer	$phoneID
	 * 		The phone id.
	 * 		Leave empty for new phones.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($personID, $phoneID = "")
	{
		$this->personID = $personID;
		$this->phoneID = $phoneID;
	}
	
	/**
	 * Create a new person phone.
	 * 
	 * @param	integer	$typeID
	 * 		The phone type id.
	 * 		See getPhoneTypes() for more information.
	 * 
	 * @param	string	$phone
	 * 		The phone number.
	 * 		It should be without country code.
	 * 
	 * @param	integer	$countryID
	 * 		The country code.
	 * 		See Geoloc API for more information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($typeID, $phone, $countryID = "")
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Create person phone
		$rdbc = new rdbConnection();
		$q = new dbQuery("22210102843166", "enterprise.relations.persons.phones");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['type'] = $typeID;
		$attr['phone'] = $phone;
		$attr['cid'] = (empty($countryID) ? "NULL" : $countryID);
		$result = $rdbc->execute($q, $attr);
		if ($result)
		{
			$phoneInfo = $rdbc->fetch($result);
			$this->phoneID = $phoneInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update the phone information.
	 * 
	 * @param	integer	$typeID
	 * 		The phone type id.
	 * 		See getPhoneTypes() for more information.
	 * 
	 * @param	string	$phone
	 * 		The phone number.
	 * 		It should be without country code.
	 * 
	 * @param	integer	$countryID
	 * 		The country code.
	 * 		See Geoloc API for more information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($typeID, $phone, $countryID = "")
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Update person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("21994484514018", "enterprise.relations.persons.phones");
		$attr = array();
		$attr['id'] = $this->phoneID;
		$attr['pid'] = $this->personID;
		$attr['type'] = $typeID;
		$attr['phone'] = $phone;
		$attr['cid'] = (empty($countryID) ? "NULL" : $countryID);
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Remove current phone from person.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Update person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("2021339345813", "enterprise.relations.persons.phones");
		$attr = array();
		$attr['id'] = $this->phoneID;
		$attr['pid'] = $this->personID;
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Get phone information.
	 * 
	 * @return	array
	 * 		An array with phone information.
	 */
	public function info()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Get person phone info
		$rdbc = new rdbConnection();
		$q = new dbQuery("16599367344554", "enterprise.relations.persons.phones");
		$attr = array();
		$attr['id'] = $this->phoneID;
		$attr['pid'] = $this->personID;
		$result = $rdbc->execute($q, $attr);
		$phoneInfo = $rdbc->fetch($result);
		
		// Set phone type
		$phoneInfo['type'] = literal::get($scope = "enterprise.relations.phones", $name = "type_".$phoneInfo['type'], $attr = array(), $wrapped = FALSE);
		
		// Set phone country
		$allCountries = region::getAllCountries();
		$phoneInfo['country'] = $allCountries[$phoneInfo['country_id']]["countryName"];
		
		// Return phone info array
		return $phoneInfo;
	}
	
	/**
	 * Get all phone types.
	 * 
	 * @return	array
	 * 		An array of phone types by id.
	 */
	public static function getPhoneTypes()
	{
		// Update person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("2561665835986", "enterprise.relations.persons.phones");
		$result = $rdbc->execute($q);
		
		$types = array();
		$tliterals = literal::get($scope = "enterprise.relations.phones", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $rdbc->fetch($result))
			$types[$row['id']] = $tliterals["type_".$row['name']];
		
		return $types;
	}
	
	/**
	 * Get all person phones.
	 * 
	 * @return	array
	 * 		An array of all person phones an their info
	 */
	public function getAllPhones()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
		
		// Get all addresses
		$rdbc = new rdbConnection();
		$q = new dbQuery("20760154928209", "enterprise.relations.persons.phones");
		$attr = array();
		$attr['pid'] = $this->personID;
		$result = $rdbc->execute($q, $attr);
		
		$allPhones = array();
		$tliterals = literal::get($scope = "enterprise.relations.phones", $name = "", $attr = array(), $wrapped = FALSE);
		$allCountries = region::getAllCountries();
		while ($row = $rdbc->fetch($result))
		{
			$row['type'] = $tliterals["type_".$row['type']];
			$row['country'] = $allCountries[$row['country_id']]["countryName"];
			$allPhones[$row['id']] = $row;
		}
		
		// Return all addresses
		return $allPhones;
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
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		// Get person's owner team/company id
		$person = new ePerson($this->personID);
		$personInfo = $person->info();
		
		// Check if it is the same team/company
		$this->valid = ($personInfo['owner_team_id'] == $teamID);
		return $this->valid;
	}
}
//#section_end#
?>