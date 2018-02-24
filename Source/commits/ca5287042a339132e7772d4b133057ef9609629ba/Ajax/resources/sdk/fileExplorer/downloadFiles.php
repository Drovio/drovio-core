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
importer::import("ESS", "Protocol", "server::HttpResponse");
importer::import("INU", "Views", "fileExplorer");

use \ESS\Protocol\server\HttpResponse;
use \INU\Views\fileExplorer;

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];
$fileNames = $_GET['fNames'];

// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
	return;
	
if (!is_array($fileNames))
	return;
	
$fileNames = array_filter($fileNames,
	function($value)
	{
		return is_string($value) && (!empty($value));
	}
);
			
if (empty($fileNames))
	return;

$subPath = str_replace("::", "/", $subPath);

if (!file_exists(systemRoot.$rootPath.$subPath."/"))
	return;

$path = $rootPath.$subPath;

// Pack files
$fexplorer = new fileExplorer($path, $rootIdentifier);
//$fexplorer->packFiles($fileNames);
//return;

// Get path information
$info = $fexplorer->packFiles($fileNames);

// Get MIME Report
ob_clean();
MIMEServerReport::clear();
MIMEServerReport::get($info['name'], httpResponse::CONTENT_APP_ZIP, $info['suggest'], TRUE);

// Delete file
@unlink($info['name']);
//#section_end#
?>