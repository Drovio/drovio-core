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
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("DEV", "Tools", "validator");
importer::import("ENP", "Comm", "dbConnection");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \DEV\Tools\validator;
use \ENP\Comm\dbConnection as eDbConnection;

abstract class ePerson
{
	const RELATION_CUSTOMER = 1;
	
	const RELATION_STAFF = 3;
	
	const MAIL_DOMAIN = "drovio.enterprise.person";
	
	protected $personID;
	
	// Constructor Method
	public function __construct($personID = "")
	{
		$this->personID = $personID;
	}
	
	public function create($relationID, $mail_phone, $firstname, $lastname = "")
	{
		// Normalize relation id
		$relationID = ($relationID == self::RELATION_CUSTOMER ? self::RELATION_CUSTOMER : self::RELATION_STAFF);
		
		// Check for valid email or phone number
		if (!validator::validEmail($mail_phone))
		{
			// Check if it is a valid phone number
			// Replace + with 00
			$mail_phone = str_repace("+", "00", $mail_phone);
			// Check if it is a valid phone number (just integer)
			if (is_numeric($mail_phone) && is_int(intval($mail_phone)))
			{
				// Set mail to by custom drovio enterprise person
				$mail_phone .= "@".self::MAIL_DOMAIN;
				
				// Create person with the new assigned email
				return $this->create($relationID, $mail_phone, $firstname, $lastname);
			}
			
			return FALSE;
		}
		
		// Get current team id
		$teamID = team::getTeamID();
		if (empty($teamID))
			return FALSE;
		
		// Create person with given mail
		$dbc = new dbConnection();
		$q = new dbQuery("33978101814318", "profile.person");
		$attr = array();
		$attr['email'] = $mail_phone;
		$attr['firstname'] = $firstname;
		$attr['lastname'] = $lastname;
		$result = $dbc->execute($q, $attr);
		if (!$result)
			return FALSE;
		
		// Fetch person information (person id)
		$personInfo = $dbc->fetch($result);
		$this->personID = $personInfo['id'];
		
		// Create person relation
		$edbc = new eDbConnection();
		$q = new dbQuery("2512998634591", "enterprise.relations");
		$attr = array();
		$attr['pid'] = $this->personID;
		$attr['tid'] = $teamID;
		$attr['rid'] = $relationID;
		$attr['time'] = time();
		return $edbc->execute($q, $attr);
	}
}
//#section_end#
?>