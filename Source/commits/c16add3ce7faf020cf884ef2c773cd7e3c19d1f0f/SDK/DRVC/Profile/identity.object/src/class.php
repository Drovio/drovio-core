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
importer::import("API", "Model", "sql/dbQuery");
importer::import("DRVC", "Comm", "dbConnection");
importer::import("DRVC", "Profile", "account");

use \API\Model\sql\dbQuery;
use \DRVC\Comm\dbConnection;
use \DRVC\Profile\account;

class identity
{
	const DB_PREFIX = "drovio.identity";
	
	/**
	 * Setup an identity database for the given team name.
	 * 
	 * @param	string	$teamName
	 * 		The team name to setup the database for.
	 * 
	 * @param	string	$password
	 * 		The password for the demo account.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setup($teamName, $password)
	{
		// Normalize team name
		$teamName = strtolower($teamName);
		
		// Setup database
		$dbc = new dbConnection(NULL);
		$q = new dbQuery("2132704406083", "identity.setup");
		$attr = array();
		$attr['db_name'] = self::DB_PREFIX.".".$teamName;
		$result = $dbc->execute($q, $attr);
		if (!$result)
			return FALSE;
		
		// Create demo account
		account::init($teamName);
		return account::create($email = "demo@identity.drov.io", $firstname = "Demo", $lastname = "Account", $password);
	}
}
//#section_end#
?>