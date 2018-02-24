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

//__________ [Script GET Variables] __________//
// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['ri'];
$subPath = $_GET['sp'];
$fileName = $_GET['fn'];

//__________ [Script Code] __________//
if (empty($rootIdentifier) || (!is_string($rootIdentifier)))
	return;

// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
	return;

if (!is_string($fileName) || empty($fileName))
	return;

$subPath = str_replace("::", "/", $subPath);

if (!file_exists(systemRoot.$rootPath.$subPath."/"))
	return;

$path = $rootPath.$subPath;

/*$fexplorer = new fileExplorer($path, $rootIdentifier);
$success = $fexplorer->createFolder($folderName);*/

ob_end_clean();
ob_start();
//#section_end#
?>