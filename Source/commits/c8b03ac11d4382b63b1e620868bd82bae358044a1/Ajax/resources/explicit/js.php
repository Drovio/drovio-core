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
// Import
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Developer", "components::sdk::sdkPackage");

// Use
use \ESS\Protocol\client\BootLoader;
use \API\Developer\components\units\modules\module;
use \API\Developer\components\sdk\sdkPackage;

header('Content-type: text/javascript');


if (isset($_GET['hs']))
{
	BootLoader::loadJS($_GET['hs']);
	return;
}


// Load Module JS
$moduleObject = new module($_GET['md']);
echo $module->loadJS();
return;
//#section_end#
?>