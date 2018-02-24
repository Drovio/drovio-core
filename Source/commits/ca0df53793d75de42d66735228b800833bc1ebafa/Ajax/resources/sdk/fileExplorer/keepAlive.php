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
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "reports::JSONServerReport");
use \ESS\Protocol\AsCoProtocol;

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

// Set the default request headers
\ESS\Protocol\reports\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
importer::import("UI", "Content", "JSONContent");
importer::import("INU", "Views", "fileExplorer");

use \UI\Content\JSONContent;
use \INU\Views\fileExplorer;

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];

$json = new JSONContent();
$jsonReport = array();
$jsonReport['status'] = "success!";

// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$jsonReport['status'] = "session_lost";
	echo $json->getReport($jsonReport);
	return;
}

$subPath = str_replace("::", "/", $subPath);
$fexplorer = new fileExplorer($rootPath, $rootIdentifier, null, TRUE);
$path = $rootPath."/".$subPath."/";
if (!file_exists(systemRoot.$path))
{
	$jsonReport['status'] = "invalid_path";
	echo $json->getReport($jsonReport);
	return;
}

$contents = $fexplorer->getDirectoryContents($subPath);
$list = array();
foreach ((array)$contents['dirs'] as $details)
	$list[] = $details['name'];
	
// Files go last/down on the list
foreach ((array)$contents['files'] as $details)
	$list[] = $details['name'];

$jsonReport['contents'] = $list;
echo $json->getReport($jsonReport);
//#section_end#
?>