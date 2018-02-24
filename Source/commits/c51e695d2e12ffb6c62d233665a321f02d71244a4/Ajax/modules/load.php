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
importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\AsCoProtocol;
use \ESS\Protocol\loaders\ModuleLoader;
use \UI\Html\DOM;

// Ascop Variables
if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	$GLOBALS['_REQUEST'] = $_GET['__REQUEST'];
	unset($_GET['__REQUEST']);
	
	AsCoProtocol::set($_GET['__ASCOP']);
	unset($_GET['__ASCOP']);
}
else
{
	$GLOBALS['_REQUEST'] = $_POST['__REQUEST'];
	unset($_POST['__REQUEST']);
	
	AsCoProtocol::set($_POST['__ASCOP']);
	unset($_POST['__ASCOP']);
}

// Initialize DOM
DOM::initialize();

// Define Module Preloader
define("_MDL_PRELOADER_", TRUE);

// Load Module
$output = ModuleLoader::load($GLOBALS['_REQUEST']['ID'], $GLOBALS['_REQUEST']['ACTION']);

echo $output;
return;
//#section_end#
?>