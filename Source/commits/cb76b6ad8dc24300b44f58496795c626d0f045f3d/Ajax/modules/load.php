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
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\loaders\ModuleLoader;
use \UI\Html\DOM;

// Initialize DOM
DOM::initialize();

// Define Module Preloader
define("_MDL_PRELOADER_", TRUE);

// Load Module
$output = ModuleLoader::load($GLOBALS['__REQUEST']['ID'], $GLOBALS['__REQUEST']['ACTION']);

echo $output;
return;
//#section_end#
?>