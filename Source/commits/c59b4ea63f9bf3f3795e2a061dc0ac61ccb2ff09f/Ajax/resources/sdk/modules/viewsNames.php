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

importer::import("ESS", "Protocol", "server::AsCoProtocol");
use \ESS\Protocol\server\AsCoProtocol;

// Ascop Variables
$GLOBALS['__REQUEST'] = $_REQUEST['__REQUEST'];
unset($_REQUEST['__REQUEST']);

AsCoProtocol::set($_REQUEST['__ASCOP']);
unset($_REQUEST['__ASCOP']);
//#section_end#
//#section#[code]
importer::import("ESS", "Protocol", "server::JSONServerReport");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "account");

use \ESS\Protocol\server\JSONServerReport;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\components\units\modules\module;
use \API\Model\units\sql\dbQuery;
use \API\Security\account;



$moduleViews = array();
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
		$views = $moduleObject->getViews();
		$moduleViews = array_merge($moduleViews, $views);
	}
}

// Set Headers
ob_end_clean();
ob_start();
JSONServerReport::setResponseHeaders();
echo json_encode($moduleViews, TRUE);
//#section_end#
?>