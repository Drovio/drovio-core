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

// Use
use \ESS\Protocol\client\BootLoader;


ob_end_clean();
header('Content-type: text/javascript');


// Load Global CSS Packages (API, UI)
$libName = "ACL";
$packages = array();
$packages[] = "UI";

foreach ($packages as $packageName)
{
	BootLoader::loadFriendJS("appCenter", $libName, $packageName);
	echo "\n";
}
//#section_end#
?>