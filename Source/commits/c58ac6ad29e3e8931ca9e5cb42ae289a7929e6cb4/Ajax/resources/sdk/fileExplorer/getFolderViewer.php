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

//__________ [Initialize Script Objects] __________//

//__________ [Script GET Variables] __________//
// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$prettyName = $_GET['pName'];
$subPath = $_GET['curSubP'];

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

if (empty($prettyName))
	$prettyName = "/";

if (empty($subPath))
	$subPath = "";

$subPath = str_replace("::", "/", $subPath);

$fexplorer = new fileExplorer($rootPath, $rootIdentifier, $prettyName);
$folderViewer = $fexplorer->getFolderTreeview($subPath);
//DOM::append($fviewer);
 
//ob_end_clean();
//ob_start();
echo DOM::innerHTML($folderViewer);
//#section_end#
?>