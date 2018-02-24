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
importer::import("ESS", "Protocol", "reports::JSONServerReport");
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
importer::import("DEV", "Core", "sdk::sdkPackage");
importer::import("DEV", "Modules", "module");

use \DEV\Core\sdk\sdkPackage;
use \DEV\Modules\module;


header('Content-type: text/javascript');

// Get variables
$category = $_GET['category'];
$library = $_GET['library'];
$package = $_GET['package'];

if (isset($category) && $category == "Modules")
{
	// Load Module CSS
	$moduleObject = new module($package);
	echo $moduleObject->loadJS();
}
else if (isset($category) && $category == "Packages")
{
	$sdkPackage = new sdkPackage();
	echo $sdkPackage->loadJS($library, $package);
}
//#section_end#
?>