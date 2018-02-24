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
 * Person address manager
 * 
 * Manages all person's addresses.
 * 
 * @version	0.1-1
 * @created	August 20, 2015, 14:24 (EEST)
 * @updated	August 20, 2015, 14:24 (EEST)
 */
class ePersonAddress
{
	/**
	 * The current person id.
	 * 
	 * @type	integer
	 */
	protected $personID;
	
	/**
	 * The current address id.
	 * 
	 * @type	integer
	 */
	protected $addressID;
	
	/**
	 * Whether the person is valid with the current company.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Create an instance of the person address manager.
	 * 
	 * @param	integer	$personID
	 * 		The person id to manage addresses for.
	 * 
	 * @param	integer	$addressID
	 * 		The address id.
	 * 		Leave empty for new addresses.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($personID, $addressID = "")
	{
		$this->personID = $personID;
		$this->addressID = $addressID;
	}
	
	/**
	 * Create a new person address.
	 * 
	 * @param	integer	$typeID
	 * 		The address type id.
	 * 		Use getAddressTypes() to get all types.
	 * 
	 * @param	string	$address
	 * 		The road address (including road number).
	 * 
	 * @param	string	$postal_code
	 * 		The address' postal code.
	 * 
	 * @param	string	$city
	 * 		The city name.
	 * 
	 * @param	integer	$countryID
	 * 		The country id of the address.
	 * 		See Geoloc API for more information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($typeID, $address, $postal_code, $city, $countryID = "")
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Create person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("20031543416209", "enterprise.relations.persons.addresses");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['type'] = $typeID;
		$attr['address'] = $address;
		$attr['pcode'] = $postal_code;
		$attr['city'] = $city;
		$attr['cid'] = (empty($countryID) ? "NULL" : $countryID);
		$result = $rdbc->execute($q, $attr);
		if ($result)
		{
			$addressInfo = $rdbc->fetch($result);
			$this->addressID = $addressInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Update the address' information.
	 * 
	 * @param	integer	$typeID
	 * 		The address type id.
	 * 		Use getAddressTypes() to get all types.
	 * 
	 * @param	string	$address
	 * 		The road address (including road number).
	 * 
	 * @param	string	$postal_code
	 * 		The address' postal code.
	 * 
	 * @param	string	$city
	 * 		The city name.
	 * 
	 * @param	integer	$countryID
	 * 		The country id of the address.
	 * 		See Geoloc API for more information.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($typeID, $address, $postal_code, $city, $countryID = "")
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Update person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("20249262708507", "enterprise.relations.persons.addresses");
		$attr = array();
		$attr['id'] = $this->addressID;
		$attr['pid'] = $this->personID;
		$attr['type'] = $typeID;
		$attr['address'] = $address;
		$attr['pcode'] = $postal_code;
		$attr['city'] = $city;
		$attr['cid'] = (empty($countryID) ? "NULL" : $countryID);
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Remove the current address.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Remove person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("14956854579472", "enterprise.relations.persons.addresses");
		$attr = array();
		$attr['id'] = $this->addressID;
		$attr['pid'] = $this->personID;
		return $rdbc->execute($q, $attr);
	}
	
	/**
	 * Get all information for the current address.
	 * 
	 * @return	array
	 * 		All address information.
	 */
	public function info()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
			
		// Get person address info
		$rdbc = new rdbConnection();
		$q = new dbQuery("34988348931517", "enterprise.relations.persons.addresses");
		$attr = array();
		$attr['id'] = $this->addressID;
		$attr['pid'] = $this->personID;
		$result = $rdbc->execute($q, $attr);
		$addressInfo = $rdbc->fetch($result);
		
		// Set address type
		$addressInfo['type'] = literal::get($scope = "enterprise.relations.addresses", $name = "type_".$addressInfo['type'], $attr = array(), $wrapped = FALSE);
		
		// Set address country
		$allCountries = region::getAllCountries();
		$addressInfo['country'] = $allCountries[$addressInfo['country_id']]["countryName"];
		
		// Return address info array
		return $addressInfo;
	}
	
	/**
	 * Get all address types.
	 * 
	 * @return	array
	 * 		An array of types by id and name.
	 */
	public static function getAddressTypes()
	{
		// Update person address
		$rdbc = new rdbConnection();
		$q = new dbQuery("18943844562283", "enterprise.relations.persons.addresses");
		$result = $rdbc->execute($q);
		
		$types = array();
		$tliterals = literal::get($scope = "enterprise.relations.addresses", $name = "", $attr = array(), $wrapped = FALSE);
		while ($row = $rdbc->fetch($result))
			$types[$row['id']] = $tliterals["type_".$row['name']];
		
		return $types;
	}
	
	/**
	 * Get all person addresses.
	 * 
	 * @return	array
	 * 		An array of all person addresses.
	 */
	public function getAllAddresses()
	{
		// Validate person in the team
		if (!$this->validate())
			return FALSE;
		
		// Get all addresses
		$rdbc = new rdbConnection();
		$q = new dbQuery("29353791041126", "enterprise.relations.persons.addresses");
		$attr = array();
		$attr['pid'] = $this->personID;
		$result = $rdbc->execute($q, $attr);
		
		$allAddresses = array();
		$tliterals = literal::get($scope = "enterprise.relations.addresses", $name = "", $attr = array(), $wrapped = FALSE);
		$allCountries = region::getAllCountries();
		while ($row = $rdbc->fetch($result))
		{
			$row['type'] = $tliterals["type_".$row['type']];
			$row['country'] = $allCountries[$row['country_id']]["countryName"];
			$allAddresses[$row['id']] = $row;
		}
		
		// Return all addresses
		return $allAddresses;
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