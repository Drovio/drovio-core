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
importer::import("INU", "Views", "fileExplorer");

// Use
use \INU\Views\fileExplorer;

//__________ [Initialize Script Objects] __________//

//__________ [Script POST Variables] __________//
// File Explorer ID, used to identify root path
$rootIdentifier = $_POST['fexId'];
$subPath = $_POST['subPath'];
$oldName = $_POST['oName'];
$newName = $_POST['nName'];

//__________ [Script Code] __________//
header("Content-Type:application/json");
$jsonReport = array();
// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$jsonReport['status'] = "Invalid path!";
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

if (!is_string($oldName) || empty($oldName))
{
	$jsonReport['status'] = "Invalid file name!";
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

if (!is_string($newName) || empty($newName))
{
	$jsonReport['status'] = "Invalid new name!";
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

$subPath = str_replace("::", "/", $subPath);

if (!file_exists(systemRoot.$rootPath.$subPath."/"))
{
	$jsonReport['status'] = "Path doesn't exist!";
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

$path = $rootPath.$subPath;

$fexplorer = new fileExplorer($path, $rootIdentifier);
$success = $fexplorer->renameFile($path, $oldName, $newName);

$jsonReport['status'] = $success;
$jsonReport['info']['newName'] = $newName;

ob_end_clean();
ob_start();
echo json_encode($jsonReport, JSON_FORCE_OBJECT);
//#section_end#
?>