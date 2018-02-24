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
importer::import("ESS", "Protocol", "reports/JSONServerReport");
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
importer::import("API", "Resources", "archive/zipManager");
importer::import("INU", "Views", "fileExplorer");
importer::import("UI", "Content", "MIMEContent");

use \ESS\Protocol\http\HTTPResponse;
use \API\Resources\archive\zipManager;
use \INU\Views\fileExplorer;
use \UI\Content\MIMEContent;

// Initialize MIME content
$mime = new MIMEContent();

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$rootIdentifier = urldecode($rootIdentifier);
$subPath = urldecode($_GET['subPath']);
$fileNames = $_GET['fNames'];

// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);
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

// Locate the single file or Pack files in zip and locate that zip
$info = array();
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
		$mime->set($fileName = $info['name'], $type = MIMEContent::CONTENT_APP_STREAM);
		echo $mime->getReport($suggestedFileName = $info['suggest'], $ignore_user_abort = FALSE, $removeFile = TRUE);
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
$mime->set($fileName = $info['name'], $type = MIMEContent::CONTENT_APP_STREAM);
echo $mime->getReport($suggestedFileName = $info['suggest'], $ignore_user_abort = FALSE, $removeFile = TRUE);
return;
//#section_end#
?>