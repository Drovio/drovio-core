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
importer::import("ESS", "Protocol", "loaders/AppLoader");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Apps", "test/appTester");

use \ESS\Protocol\loaders\AppLoader;
use \UI\Html\DOM;
use \DEV\Apps\test\appTester;

// Initialize DOM
DOM::initialize();

// Get application to load
$appID = engine::getVar('__AID');
$appID = (empty($appID) ? engine::getVar('__APP__ID') : $appID);
$appID = (empty($appID) ? engine::getVar('__APP')['ID'] : $appID);
$appID = (empty($appID) ? $GLOBALS['__REQUEST']['ID'] : $appID);

$viewName = engine::getVar('__AVN');
$viewName = (empty($appID) ? engine::getVar('__APP__VIEW_NAME') : $appID);
$viewName = (empty($appID) ? engine::getVar('__APP')['VIEW_NAME'] : $appID);
$viewName = (empty($viewName) ? $GLOBALS['__REQUEST']['VIEW'] : $viewName);

// Activate tester mode for application
$subdomain = AsCoProtocol::getSubdomain();
appTester::setPublisherLock(FALSE, $subdomain);

// Get initial application view
echo AppLoader::load($appID, $viewName);
return;
//#section_end#
?>