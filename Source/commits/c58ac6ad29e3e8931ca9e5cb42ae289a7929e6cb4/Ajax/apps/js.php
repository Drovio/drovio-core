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
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "components::appView");
importer::import("DEV", "Apps", "components::appStyle");
importer::import("DEV", "Apps", "components::appScript");
importer::import("DEV", "Apps", "components::source::sourceLibrary");
importer::import("DEV", "Apps", "components::source::sourcePackage");
importer::import("DEV", "Apps", "components::source::sourceObject");

use \DEV\Apps\application;
use \DEV\Apps\components\appView;
use \DEV\Apps\components\appStyle;
use \DEV\Apps\components\appScript;
use \DEV\Apps\components\source\sourceLibrary;
use \DEV\Apps\components\source\sourcePackage;
use \DEV\Apps\components\source\sourceObject;


header('Content-type: text/javascript');

// Init application
$appID = $_GET['id'];
$app = new application($appID);

// Set jsContent
$jsContent = "";

// Gather Application Scripts
$scripts = $app->getScripts();
foreach ($scripts as $scriptName)
{
	$appScript = new appScript($appID, $scriptName);
	$jsContent .= $appScript->get()."\n";
}

// Publish Views
$views = $app->getViews();
foreach ($views as $viewName)
{
	// Initialize view
	$appView = new appView($appID, $viewName);
	$jsContent .= $appView->getJS()."\n";
}

// Publish Source Objects
$lib = new sourceLibrary($appID);
$libraries = $lib->getList();
foreach ($libraries as $library)
{
	// Get packages
	$packages = $lib->getPackageList($library);
	foreach ($packages as $package)
	{
		// Get object list
		$pkg = new sourcePackage($appID);
		$objects = $pkg->getObjects($library, $package, $namespace = NULL);
		foreach ($objects as $object)
		{
			$obj = new sourceObject($appID, $library, $package, $object['namespace'], $object['name']);
			$jsContent .= $obj->getJSCode()."\n";
		}
	}
}

// Output js
echo $jsContent;
return;
//#section_end#
?>