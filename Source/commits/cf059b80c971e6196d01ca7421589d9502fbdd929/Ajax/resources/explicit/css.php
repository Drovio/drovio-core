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
// Import
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Developer", "components::sdk::sdkPackage");

// Use
use \ESS\Protocol\client\BootLoader;
use \API\Developer\components\units\modules\module;
use \API\Developer\components\sdk\sdkPackage;


header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";

// Get variables
$category = $_GET['category'];
$library = $_GET['library'];
$package = $_GET['package'];

if (isset($category) && $category == "Modules")
{
	// Load Module CSS
	$moduleObject = new module($package);
	echo $moduleObject->loadCSS();
	return;
}
else if (isset($category) && $category == "Packages")
{
	$sdkPackage = new sdkPackage();
	echo $sdkPackage->loadCSS($library, $package);
	return;
}

if (isset($_GET['hs']))
{
	BootLoader::loadCSS($_GET['hs']);
	return;
}

// Load Module CSS
$moduleObject = new module($_GET['md']);
echo $moduleObject->loadCSS();
return;
//#section_end#
?>