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
importer::import("API", "Developer", "components::sdk::sdkPackage");
importer::import("API", "Developer", "components::prime::indexing::libraryIndex");
importer::import("API", "Developer", "profiler::sdkTester");
importer::import("API", "Resources", "layoutManager");

// Use
use \ESS\Protocol\client\BootLoader;
use \API\Developer\components\sdk\sdkPackage;
use \API\Developer\components\prime\indexing\libraryIndex;
use \API\Developer\profiler\sdkTester;
use \API\Resources\layoutManager;

ob_end_clean();
header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";


// Load Global CSS Packages (API, UI, INU)

// Get Packages
$libraries = array();
$libraries[] = "ESS";

// Initialize
$sdkPkg = new sdkPackage();
foreach ($libraries as $libName)
{
	$packages = libraryIndex::getReleasePackageList("/System/Library/SDK/", $libName, $fullNames = FALSE);
	foreach ($packages as $packageName)
	{
		if (sdkTester::libPackageStatus($libName, $packageName))
			$sdkPkg->loadCSS($libName, $packageName);
		else
			BootLoader::loadFriendCSS("Packages", $libName, $packageName);
		
		echo "\n";
	}
}

// Load Layout Styles
$layoutsPath = layoutManager::getFilePath();
importer::incl($layoutsPath, FALSE, TRUE);
//#section_end#
?>