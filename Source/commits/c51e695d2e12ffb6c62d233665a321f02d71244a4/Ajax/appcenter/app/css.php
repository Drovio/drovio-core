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
header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";

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
	
	// Application Styles
	$styles = $app->getStyles();
	foreach ($styles as $style)
	{
		$appStyle = $devAppFolder."/styles/".$style.".css";
		importer::incl($appStyle, $root = TRUE, $once = TRUE);
		echo "\n";
	}
	
	// Application View Styles
	$views = $app->getViews();
	foreach ($views as $view)
	{
		$viewStyle = $devAppFolder."/views/".$view.".view/style.css";
		importer::incl($viewStyle, $root = TRUE, $once = TRUE);
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
			$objectStyle = $devAppFolder."/src/".$packageName."/".$objectPath.".object/model/style.css";
			importer::incl($objectStyle, $root = TRUE, $once = TRUE);
			echo "\n";
		}
	}
	
	return;
}

// Get application published folder
$appFolder = appManager::getPublishedAppFolder($appID);
importer::incl($appFolder."/style.css", $root = TRUE, $once = TRUE);
return;
//#section_end#
?>