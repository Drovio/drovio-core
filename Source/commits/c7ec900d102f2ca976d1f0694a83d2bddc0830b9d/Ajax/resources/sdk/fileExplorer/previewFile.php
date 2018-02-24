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

// Important Headers
importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::JSONServerReport");
use \ESS\Protocol\server\AsCoProtocol;

// Set Ascop Variables
if (isset($_REQUEST['__REQUEST']))
{
	$GLOBALS['__REQUEST'] = $_REQUEST['__REQUEST'];
	unset($_REQUEST['__REQUEST']);
}
if (isset($_REQUEST['__ASCOP']))
{
	AsCoProtocol::set($_REQUEST['__ASCOP']);
	unset($_REQUEST['__ASCOP']);
}

// Set the headers at least once
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
// Import
importer::import("UI", "Html", "DOM");
importer::import("INU", "Views", "fileExplorer");

// Use
use \UI\Html\DOM;
use \INU\Views\fileExplorer;

DOM::initialize();

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];
$fileName = $_GET['fn'];
$mode = $_GET['mode'];

header("Content-Type:application/json");
$jsonReport = array();
// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$jsonReport['status'] = "Invalid path!";
	//JSONServerReport::addContent($jsonReport);
	//echo JSONServerReport::get();
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

if (!is_string($fileName) || empty($fileName)){
	$jsonReport['status'] = "Invalid filename!";
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	//JSONServerReport::addContent($jsonReport);
	//echo JSONServerReport::get();
	return;
}

$subPath = str_replace("::", "/", $subPath);

if (!file_exists(systemRoot.$rootPath.$subPath."/")){
	$jsonReport['status'] = "Path doesn't exist!";
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	//JSONServerReport::addContent($jsonReport);
	//echo JSONServerReport::get();
	return;
}

$path = $rootPath.$subPath;

$fexplorer = new fileExplorer($path, $rootIdentifier);
$htmlContent = DOM::create("div");

$previewHtml = "";
if ($mode == "icon")
	$wrap = $fexplorer->iconifyFile($fileName);
else {
	$previewHtml = $fexplorer->previewFile($fileName); 
	$wrap = DOM::create("div", "", "", "filePreviewWrapper");
	DOM::append($wrap, $previewHtml);
}

DOM::append($htmlContent, $wrap);

$jsonReport['status'] = TRUE;
$jsonReport['htmlContent'] = DOM::innerHTML($htmlContent);

ob_end_clean();
ob_start();
echo json_encode($jsonReport, JSON_FORCE_OBJECT);
//JSONServerReport::addContent($jsonReport);
//echo JSONServerReport::get();
//#section_end#
?>