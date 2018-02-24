<?php
//#section#[header]
require_once($_SERVER['DOCUMENT_ROOT'].'/_domainConfig.php');

// Importer
use \API\Platform\importer;

// Engine Start
importer::import("API", "Platform", "engine");
use \API\Platform\engine;
engine::start();

use \Exception;

// Important Headers
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "reports::JSONServerReport");
use \ESS\Protocol\AsCoProtocol;

// Set Ascop Variables
if (isset($_REQUEST['__REQUEST']))
{
	$GLOBALS['__REQUEST'] = $_REQUEST['__REQUEST'];
	unset($_REQUEST['__REQUEST']);
}
if (isset($_REQUEST['__ASCOP']))
{
	AsCoProtocol::set($_REQUEST['__ASCOP']);
	unset($_REQUEST['__ASCOP']);
}

// Set the default request headers
\ESS\Protocol\reports\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Profile", "account");
importer::import("DEV", "Modules", "module");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \DEV\Modules\module;

$moduleQueries = array();
if (account::validate())
{
	// Get modules
	$dbc = new dbConnection();
	$dbq = new dbQuery("1464459212", "units.modules");
	$attr = array();
	$result = $dbc->execute($dbq, $attr);
	$modules = $dbc->toArray($result, "id", "title");
	
	foreach ($modules as $module_id => $title)
	{
		// Create new module
		$moduleObject = new module($module_id);
		$queries = $moduleObject->getQueries();
		$moduleQueries = array_merge($moduleQueries, $queries);
	}
}

// Set Headers
ob_clean();
echo json_encode($moduleQueries, TRUE);
//#section_end#
?>