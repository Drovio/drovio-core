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