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
importer::import("API", "Developer", "appcenter::appPlayer");
importer::import("API", "Developer", "appcenter::appManager");
importer::import("API", "Developer", "appcenter::application");

// Use
use \API\Developer\appcenter\appPlayer;
use \API\Developer\appcenter\appManager;
use \API\Developer\appcenter\application;

ob_end_clean();
header('Content-type: text/javascript');

$appID = $_GET['id'];
if (empty($appID))
{
	echo "/* Application is not initialized properly! */";
	return;
}

// Get tester status
$tester = appPlayer::testerStatus();
if ($tester)
{
	// Load application's all styles
	$app = new application($appID);
	$appMan = new appManager();
	$devAppFolder = $appMan->getDevAppFolder($appID);
	$devAppFolder .= "/.repository/trunk/master/";
	
	// Application Scripts
	$scripts = $app->getScripts();
	foreach ($scripts as $script)
	{
		$appScript = $devAppFolder."/scripts/".$script.".js";
		importer::incl($appScript, $root = TRUE, $once = TRUE);
		echo "\n";
	}
	
	// Application View Scripts
	$views = $app->getViews();
	foreach ($views as $view)
	{
		$viewScript = $devAppFolder."/views/".$view.".view/script.js";
		importer::incl($viewScript, $root = TRUE, $once = TRUE);
		echo "\n";
	}
	
	// Application Source Styles
	$srcPackage = $app->getSrcPackage();
	$packages = $srcPackage->getPackages($fullNames = TRUE);
	foreach ($packages as $packageName)
	{
		$objects = $srcPackage->getObjects($packageName, $parentNs = "");
		foreach ($objects as $object)
		{
			$namespace = str_replace("::", "/", $object['namespace']);
			$objectPath = $namespace."/".$object['name'];
			$objectScript = $devAppFolder."/src/".$packageName."/".$objectPath.".object/script.js";
			importer::incl($objectScript, $root = TRUE, $once = TRUE);
			echo "\n";
		}
	}
	
	return;
}

// Get application published folder
$appFolder = appManager::getPublishedAppFolder($appID);
importer::incl($appFolder."/script.js", $root = TRUE, $once = TRUE);
return;
//#section_end#
?>