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
importer::import("ESS", "Protocol", "server::MIMEServerReport");
importer::import("INU", "Views", "fileExplorer");

use \ESS\Protocol\server\HttpResponse;
use \ESS\Protocol\server\MIMEServerReport;
use \INU\Views\fileExplorer;

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];
$fileNames = $_GET['fNames'];

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
	
// Pack files
$fexplorer = new fileExplorer($rootPath, $rootIdentifier);

// Initialize info
$info = array();

// Locate the single file or Pack files in zip and locate that zip
if (count($fileNames) == 1)
{
	// Check if fileName is folder and create archive
	if (is_dir(systemRoot.$parentFolder."/".$fileNames[0]."/"))
	{
		$info['suggest'] = basename(systemRoot.$parentFolder."/".$fileNames[0]."/").".zip";
	}
	else
	{
		$fileNames[0] = systemRoot.$parentFolder."/".$fileNames[0];
	
		// Copy file to temp
		$info['name'] = $fexplorer->createTempCopy($fileNames);
		
		// Add the rest information
		$info['suggest'] = basename($fileNames[0]);
		print_r($info);
		// Get MIME Report
		ob_clean();
		MIMEServerReport::get($info['name'], httpResponse::CONTENT_APP_ZIP, $info['suggest'], TRUE);
		
		// Delete file
		@unlink($info['name']);
		return;
	}
}

$files = array();
foreach ($fileNames as $file) {
	if (is_dir(systemRoot.$parentFolder."/".$file."/"))
		$files['dirs'][] = systemRoot.$parentFolder."/".$file;
	else
		$files['files'][] = systemRoot.$parentFolder."/".$file;
}

// Zip file
$info['name'] = $fexplorer->createTempCopy($files);
if (empty($info['suggest']))
{
	$info['suggest'] = ($subPath == "/" || empty($subPath) ? "files.zip" : basename($parentFolder).".zip");
}

// Get MIME Report
ob_clean();
MIMEServerReport::get($info['name'], httpResponse::CONTENT_APP_ZIP, $info['suggest'], TRUE);

// Delete file
@unlink($info['name']);
//#section_end#
?>