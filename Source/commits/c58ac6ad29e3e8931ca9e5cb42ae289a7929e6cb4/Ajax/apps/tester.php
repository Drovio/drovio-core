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
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("DEV", "Apps", "test::appTester");

use \UI\Html\HTMLContent;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \DEV\Apps\test\appTester;

// Initialize DOM
DOM::initialize();

// Init application player
appTester::init();

$pageContent = new HTMLContent();
$actionFactory = $pageContent->getActionFactory();

// Get application id
$appID = $_REQUEST['id'];
$viewName = $_REQUEST['view'];
$holder = $_REQUEST['holder'];
if (empty($appID))
{
	// Application id request invalid
	$error = DOM::create("div", "Application request is not valid!");
	$pageContent->buildElement($error);
	echo $pageContent->getReport($holder = $holder, $method = "replace");
	return;
}

// Build application container
$pageElement = $pageContent->build("appViewContent")->get();
DOM::append($pageElement);

// Load application view
appTester::play($appID, $pageElement, $viewName);

// Return content
ob_clean();
$holder = (empty($holder) ? "#applicationContainer" : $holder);
echo $pageContent->getReport($holder, $method = "replace");
return;
//#section_end#
?>