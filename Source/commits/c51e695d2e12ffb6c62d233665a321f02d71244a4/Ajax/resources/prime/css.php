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
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "layoutManager");

// Use
use \ESS\Protocol\client\BootLoader;
use \API\Developer\components\sdk\sdkPackage;
use \API\Developer\components\prime\indexing\libraryIndex;
use \API\Developer\profiler\tester;
use \API\Developer\resources\paths;
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
	if (tester::status())
	{
		$packages = libraryIndex::getPackageList(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $fullNames = FALSE);
		foreach ($packages as $packageName)
		{
			if (sdkPackage::getTesterStatus($libName, $packageName))
				$sdkPkg->loadCSS($libName, $packageName);
			else
				BootLoader::loadFriendCSS("Packages", $libName, $packageName);
			
			// Break Package Code
			echo "\n";
		}
	}
	else
	{
		$packages = libraryIndex::getReleasePackageList("/System/Library/SDK/", $libName, $fullNames = FALSE);
		
		foreach ($packages as $packageName)
		{
			BootLoader::loadFriendCSS("Packages", $libName, $packageName);
			echo "\n";
		}
	}
}

// Load Layout Styles
$layoutsPath = layoutManager::getFilePath();
importer::incl($layoutsPath, FALSE, TRUE);
//#section_end#
?>