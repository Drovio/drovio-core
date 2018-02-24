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
//#section_end#
//#section#[code]
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Model", "units::sql::dbQuery");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\components\units\modules\module;
use \API\Model\units\sql\dbQuery;

// Get modules
$dbc = new interDbConnection();
$dbq = new dbQuery("1464459212", "units.modules");
$attr = array();
$result = $dbc->execute($dbq, $attr);
$modules = $dbc->toArray($result, "id", "title");

$moduleViews = array();
foreach ($modules as $module_id => $title)
{
	// Create new module
	$moduleObject = new module($module_id);
	$views = $moduleObject->getViews();
	$moduleViews = array_merge($moduleViews, $views);
}

ob_end_clean();
ob_start();
header("Content-Type:text/json");
echo json_encode($moduleViews, TRUE);
//#section_end#
?>