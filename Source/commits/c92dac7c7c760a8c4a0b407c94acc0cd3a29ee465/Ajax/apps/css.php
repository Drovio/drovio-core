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

// Set the default request headers
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "views::appView");
importer::import("DEV", "Apps", "library::appStyle");
importer::import("DEV", "Apps", "library::appScript");
importer::import("DEV", "Apps", "source::srcPackage");
importer::import("DEV", "Apps", "source::srcObject");

use \DEV\Apps\application;
use \DEV\Apps\views\appView;
use \DEV\Apps\library\appStyle;
use \DEV\Apps\library\appScript;
use \DEV\Apps\source\srcPackage;
use \DEV\Apps\source\srcObject;


header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";

// Init application
$appID = $_GET['id'];
$appName = $_GET['name'];
$app = new application($appID, $appName);
$appID = $app->getID();

// Set cssContent && jsContent
$cssContent = "";

// Get Application Library Styles
$styles = $app->getStyles();
foreach ($styles as $styleName)
{
	$appStyle = new appStyle($appID, $styleName);
	$cssContent .= $appStyle->get();
}

// Get Application Source Package Styles
$pkg = new srcPackage($appID);
$packages = $pkg->getList();
foreach ($packages as $package)
{
	// Get object list
	$objects = $pkg->getObjects($package, $namespace = NULL);
	foreach ($objects as $object)
	{
		// Get css
		$obj = new srcObject($appID, $package, $object['namespace'], $object['name']);
		$cssContent .= $obj->getCSSCode()."\n";
	}
}

// Get Application Views Styles
$views = $app->getViews();
foreach ($views as $viewName)
{
	// Initialize view
	$appView = new appView($appID, $viewName);
	$cssContent .= $appView->getCSS();
}

// Replace resource vars
$resourcePath = $app->getResourcesFolder();
$cssContent = str_replace("%resources%", $resourcePath, $cssContent);
echo $cssContent;
return;
//#section_end#
?>