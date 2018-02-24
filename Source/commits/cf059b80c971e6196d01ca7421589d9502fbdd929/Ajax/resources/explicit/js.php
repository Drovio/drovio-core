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
	return;
}
else if (isset($category) && $category == "Packages")
{
	$sdkPackage = new sdkPackage();
	echo $sdkPackage->loadJS($library, $package);
	return;
}

echo "// Invalid Input";
return;
//#section_end#
?>