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
//#section_end#
//#section#[code]
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Profiler", "logger");

use \UI\Html\HTMLContent;
use \UI\Html\DOM;
use \DEV\Profiler\logger;

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	// Get priority
	$priority = $_POST['loggerPriority'];
	
	// Update logger to selected priority
	logger::activate($priority);
}

DOM::initialize();

// Return success
$pageContent = new HTMLContent();
$pageContent->addReportAction("logger.updated");
echo $pageContent->getReport();
//#section_end#
?>