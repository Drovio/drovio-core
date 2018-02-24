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
importer::import("API", "Developer", "appcenter::appPlayer");
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Html", "DOM");

use \API\Developer\appcenter\appPlayer;
use \ACL\Platform\importer as ACLImporter;
use \UI\Html\HTMLContent;
use \UI\Html\DOM;

// Initialize DOM
DOM::initialize();

// Init application player
appPlayer::init();

$pageContent = new HTMLContent();
$actionFactory = $pageContent->getActionFactory();

// Get application id
$appID = $_GET['id'];
$viewName = $_GET['view'];
if (empty($appID))
{
	// Application id request invalid
	$error = DOM::create("div", "Application request is not valid!");
	$pageContent->buildElement($error);
	echo $pageContent->getReport($holder = $holder, $method = "replace");
	return;
}

// Build application container
$appContainer = DOM::create("div", "", "applicationContainer");
$pageElement = $pageContent->buildElement($appContainer)->get();
DOM::append($pageElement);

// Load application view
$appViewContent = appPlayer::getView($appID, $viewName);
DOM::innerHTML($appContainer, $appViewContent);

// Run view
appPlayer::play($appID, $viewName);

// Return content
ob_end_clean();
$holder = (empty($_GET['holder']) ? "#applicationContainer" : $_GET['holder']);
echo $pageContent->getReport($holder = $holder, $method = "replace");
return;
//#section_end#
?>