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

$appID = $_GET['id'];
$filePath = $_GET['path'];
if (empty($appID) || empty($filePath))
	return;

// Get tester status
$tester = appPlayer::testerStatus();
if ($tester)
{
	// Load application's all styles
	$app = new application($appID);
	$appMan = new appManager();
	$devAppFolder = $appMan->getDevAppFolder($appID);
	$mediaFolder = $devAppFolder."/media/";
}
else
{
	// Get application published folder
	$appFolder = appManager::getPublishedAppFolder($appID);
	$mediaFolder = $appFolder."/media/";
}

// open the file in a binary mode
$name = systemRoot.$mediaFolder."/".$filePath;
$fp = fopen($name, 'rb');

// Set the headers
ob_end_clean();

// Get file type
$fileParts = explode(".", $filePath);
$fileType = $fileParts[count($fileParts)-1];

switch ($fileType)
{
	case "svg":
		header('Content-Type: image/svg+xml');
		break;
	case "jpg":
	case "jpeg":
		header('Content-Type: image/jpg');
		break;
	case "png":
		header('Content-Type: image/png');
		break;
}
header("Content-Length: " . filesize($name));

// dump the picture and stop the script
fpassthru($fp);
return;
//#section_end#
?>