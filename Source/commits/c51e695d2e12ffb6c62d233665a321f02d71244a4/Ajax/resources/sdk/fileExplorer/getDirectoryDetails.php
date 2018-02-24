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
importer::import("UI", "Html", "DOM");
importer::import("INU", "Views", "fileExplorer");

// Use
use \UI\Html\DOM;
use \INU\Views\fileExplorer;

//__________ [Initialize Script Objects] __________//

//__________ [Script GET Variables] __________//
// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];

//__________ [Script Code] __________//
DOM::initialize();

// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	echo DOM::innerHTML(fileExplorer::getInvalidRoot());
	return;
}
	
$subPath = str_replace("::", "/", $subPath);

$fexplorer = new fileExplorer($rootPath, $rootIdentifier);
$fviewer = $fexplorer->getFileArea($subPath);
//DOM::append($fviewer);

ob_end_clean();
ob_start();
echo DOM::innerHTML($fviewer);
//#section_end#
?>