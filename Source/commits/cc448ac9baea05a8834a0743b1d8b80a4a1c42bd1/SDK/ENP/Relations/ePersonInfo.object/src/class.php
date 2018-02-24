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
 * Person Custom Info
 * 
 * Manages custom information for each person.
 * All the information is being encoded into a single json.
 * 
 * @version	0.1-1
 * @created	November 3, 2015, 18:20 (GMT)
 * @updated	November 3, 2015, 18:20 (GMT)
 */
class ePersonInfo
{
	/**
	 * The current person id.
	 * 
	 * @type	integer
	 */
	protected $personID;
	
	/**
	 * The current person info.
	 * 
	 * @type	array
	 */
	private $info;
	
	/**
	 * Whether the person is valid with the current team.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Create an instance of the person info manager.
	 * 
	 * @param	integer	$personID
	 * 		The person id to manage info for.
	 * 
	 * @return	void
	 */
	public function __construct($personID)
	{
		$this->personID = $personID;
	}
	
	/**
	 * Get an info value.
	 * 
	 * @param	string	$name
	 * 		The info name.
	 * 
	 * @return	mixed
	 * 		If given a name, it will return the value.
	 * 		If the value doesn't exist, return NULL.
	 * 		If no name is given, return all values in an associative array.
	 */
	public function get($name = "")
	{
		// Validate person
		if (!$this->validate())
			return FALSE;
		
		// Check cache
		if (!empty($this->info))
			return (empty($name) ? $this->info : $this->info[$name]);
		
		// Get info from database
		$rdbc = new rdbConnection();
		$q = new dbQuery("31225478354817", "enterprise.relations.persons.info");
		$attr = array();
		$attr['pid'] = $this->personID;
		$result = $rdbc->execute($q, $attr);
		$personInfo = $rdbc->fetch($result);
		if (empty($personInfo) || empty($personInfo['info']))
			return NULL;
		
		// Get information into the array
		$this->info = json_decode($personInfo['info'], TRUE);
		if (empty($this->info))
			return NULL;
		
		// Return the name again
		return $this->get($name);
	}
	
	/**
	 * Set a new info value.
	 * 
	 * @param	string	$name
	 * 		The info name.
	 * 
	 * @param	string	$value
	 * 		The info value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($name, $value)
	{
		// Validate person
		if (!$this->validate())
			return FALSE;
		
		// Get information first
		$currentInfo = $this->get();
		
		// Add (or update) the value
		if (is_null($value))
			unset($currentInfo[$name]);
		else
			$currentInfo[$name] = $value;
		
		return $this->setAll($currentInfo);
	}
	
	/**
	 * Set all the information for the person.
	 * 
	 * @param	array	$info
	 * 		An associative array of all the name-value pairs.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setAll($info)
	{
		// Validate person
		if (!$this->validate())
			return FALSE;
		
		if (empty($info))
		{
			// Encode and update to database
			$rdbc = new rdbConnection();
			$q = new dbQuery("35412049179789", "enterprise.relations.persons.info");
			$attr = array();
			$attr['pid'] = $this->personID;
			$result = $rdbc->execute($q, $attr);
		}
		else
		{
			// Encode and update to database
			$rdbc = new rdbConnection();
			$q = new dbQuery("27934152186421", "enterprise.relations.persons.info");
			$attr = array();
			$attr['pid'] = $this->personID;
			$attr['info'] = json_encode($info, JSON_FORCE_OBJECT);
			$result = $rdbc->execute($q, $attr);
		}
		
		// Check result and fetch info
		if ($result)
		{
			$this->info = $info;
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Remove the given value from the info.
	 * 
	 * @param	string	$name
	 * 		The info name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($name)
	{
		// Validate person
		if (!$this->validate())
			return FALSE;
		
		// Remove info value
		return $this->set($name, NULL);
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