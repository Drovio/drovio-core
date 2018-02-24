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
importer::import("ESS", "Protocol", "http/HTTPResponse");
importer::import("ESS", "Protocol", "reports/MIMEServerReport");
importer::import("API", "Resources", "archive/zipManager");
importer::import("INU", "Views", "fileExplorer");

use \ESS\Protocol\http\HTTPResponse;
use \ESS\Protocol\reports\MIMEServerReport;
use \API\Resources\archive\zipManager;
use \INU\Views\fileExplorer;

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$rootIdentifier = urldecode($rootIdentifier);
$subPath = $_GET['subPath'];
$fileNames = $_GET['fNames'];

// js FormData fix
$subPath = urldecode($subPath);

// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier' or empty list for filenames
if (empty($rootPath) || !is_array($fileNames))
	return;
	
// Filter filenames
$fileNames = array_filter($fileNames,
	function($value) { return is_string($value) && (!empty($value)); }
);

// Double check if filenames are not empty
if (empty($fileNames))
	return;

// Normalize path and check if parent folder exists
$subPath = urldecode(trim(str_replace("::", "/", $subPath)));
$parentFolder = $rootPath.$subPath."/";
$parentFolder = urldecode($parentFolder);
if (!file_exists(systemRoot.$parentFolder))
	return;
	
// Initialize file explorer for next procedures
$fexplorer = new fileExplorer($rootPath, $rootIdentifier, null, TRUE, FALSE);

// Initialize info
$info = array();

// Locate the single file or Pack files in zip and locate that zip
if (count($fileNames) == 1)
{
	// Check if fileName is folder and create archive
	if (is_dir(systemRoot.$parentFolder."/".$fileNames[0]."/"))
		$info['suggest'] = basename(systemRoot.$parentFolder."/".$fileNames[0]."/").".zip";
	else
	{
		$fileNames[0] = systemRoot.$parentFolder."/".$fileNames[0];
	
		// Copy file to temp
		$info['name'] = $fexplorer->createTempCopy($fileNames);
		
		// Add the rest information
		$info['suggest'] = basename($fileNames[0]);
		
		// Get MIME Report and unlink temp file
		ob_clean();
		MIMEServerReport::get($info['name'], HTTPResponse::CONTENT_APP_ZIP, $info['suggest'], TRUE);
		@unlink($info['name']);
		return;
	}
}

// Gather all files
$zipContents = array();
foreach ($fileNames as $file)
{
	if (is_dir(systemRoot.$parentFolder."/".$file."/"))
		$zipContents['dirs'][] = systemRoot.$parentFolder."/".$file;
	else
		$zipContents['files'][] = systemRoot.$parentFolder."/".$file;
}


// Create Zip file
$info['name'] = $fexplorer->createTempCopy($files);
if (empty($info['suggest']))
	$info['suggest'] = ($subPath == "/" || empty($subPath) ? "files.zip" : basename($parentFolder).".zip");
$bStatus = zipManager::create($info['name'], $zipContents, TRUE, TRUE);

// Get MIME Report and unlink temp file
ob_clean();
MIMEServerReport::get($info['name'], HTTPResponse::CONTENT_APP_ZIP, $info['suggest'], TRUE);
@unlink($info['name']);
return;
//#section_end#
?>