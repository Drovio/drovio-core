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
importer::import("API", "Model", "units::sql::dbQuery");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;

// Get modules
$dbc = new interDbConnection();
$dbq = new dbQuery("1464459212", "units.modules");
$attr = array();
$result = $dbc->execute($dbq, $attr);
$modules = $dbc->toArray($result, "id", "title");

ob_end_clean();
ob_start();
header("Content-Type:text/json");
echo json_encode($modules, TRUE);
//#section_end#
?>