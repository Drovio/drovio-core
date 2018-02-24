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
importer::import("ESS", "Protocol", "reports/JSONServerReport");
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
importer::import("ESS", "Protocol", "loaders/ModuleLoader");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\loaders\ModuleLoader;
use \UI\Html\DOM;

// Initialize DOM
DOM::initialize();

// Define Module Preloader
define("_MDL_PRELOADER_", TRUE);

// Get module to load
$moduleID = engine::getVar('__MID');
$moduleID = (empty($moduleID) ? engine::getVar('__MODULE__ID') : $moduleID);
$moduleID = (empty($moduleID) ? engine::getVar('__MODULE')['ID'] : $moduleID);
$moduleID = (empty($moduleID) ? $GLOBALS['__REQUEST']['ID'] : $moduleID);

$viewName = engine::getVar('__MVN');
$viewName = (empty($viewName) ? engine::getVar('__MODULE__VIEW_NAME') : $viewName);
$viewName = (empty($viewName) ? engine::getVar('__MODULE')['VIEW_NAME'] : $viewName);
$viewName = (empty($viewName) ? $GLOBALS['__REQUEST']['ACTION'] : $viewName);

// Load module
echo ModuleLoader::load($moduleID, $viewName);
return;
//#section_end#
?>