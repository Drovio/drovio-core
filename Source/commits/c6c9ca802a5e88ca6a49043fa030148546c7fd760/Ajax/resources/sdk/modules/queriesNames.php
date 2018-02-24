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
importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::JSONServerReport");
use \ESS\Protocol\server\AsCoProtocol;

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

// Set the headers at least once
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
importer::import("ESS", "Protocol", "server::JSONServerReport");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "account");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Modules", "components::mQuery");

use \ESS\Protocol\server\JSONServerReport;
use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Security\account;
use \DEV\Modules\module;
use \DEV\Modules\components\mQuery;


$moduleQueries = array();
if (account::validate())
{
	// Get modules
	$dbc = new interDbConnection();
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