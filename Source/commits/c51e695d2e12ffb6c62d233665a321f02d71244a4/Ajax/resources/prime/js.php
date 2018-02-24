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

// Use
use \ESS\Protocol\client\BootLoader;
use \API\Developer\components\sdk\sdkPackage;
use \API\Developer\components\prime\indexing\libraryIndex;
use \API\Developer\profiler\tester;
use \API\Developer\resources\paths;

ob_end_clean();
header('Content-type: text/javascript');

// jQuery
BootLoader::loadJS("jq.jquery");
echo "\n";
BootLoader::loadJS("jq.jquery.ba-dotimeout.min");
echo "\n";

// Load Global JS Packages (API, UI, INU)

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
				$sdkPkg->loadJS($libName, $packageName);
			else
				BootLoader::loadFriendJS("Packages", $libName, $packageName);
			
			// Break Package Code
			echo "\n";
		}
	}
	else
	{
		$packages = libraryIndex::getReleasePackageList("/System/Library/SDK/", $libName, $fullNames = FALSE);

		foreach ($packages as $packageName)
			BootLoader::loadFriendJS("Packages", $libName, $packageName);
	}
}
//#section_end#
?>