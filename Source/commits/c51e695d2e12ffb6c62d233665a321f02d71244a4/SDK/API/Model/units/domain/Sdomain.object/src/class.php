<?php
//#section#[header]
// Namespace
namespace API\Model\units\domain;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
// Import
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

// Usage
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

class Sdomain
{
	// Get domain's info
	public static function info($description)
	{
		// Get domain info from Database
		$dbq = new dbQuery("758802961", "units.domains");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['name'] = $description;
		$result = $dbc->execute_query($dbq, $attr);
		
		// Fetch Result
		return $dbc->fetch($result);
	}
}
//#section_end#
?>