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
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("INU", "Views", "fileExplorer");

// Use
use \API\Resources\filesystem\fileManager;
use \INU\Views\fileExplorer;

//__________ [Initialize Script Objects] __________//

//__________ [Script POST Variables] __________//
$rootIdentifier = $_POST['fexId'];
$subPath = $_POST['subPath'];

//__________ [Script Code] __________//
header("Content-Type:application/json");

// Static map of upload core constants based on their values...
// Sequence does matter...
$uploadErrors = array();
$uploadErrors[] = "UPLOAD_ERR_OK";
$uploadErrors[] = "UPLOAD_ERR_INI_SIZE";
$uploadErrors[] = "UPLOAD_ERR_FORM_SIZE";
$uploadErrors[] = "UPLOAD_ERR_PARTIAL";
$uploadErrors[] = "UPLOAD_ERR_NO_FILE";
$uploadErrors[] = "UPLOAD_ERR_NO_TMP_DIR";
$uploadErrors[] = "UPLOAD_ERR_CANT_WRITE";
$uploadErrors[] = "UPLOAD_ERR_EXTENSION";

$jsonReport = array();
$jsonReport['status'] = FALSE;
$jsonReport['upload']['code'] = $_FILES['fileInfo']['error'];
$jsonReport['upload']['status'] = $uploadErrors[$_FILES['fileInfo']['error']];
if ($_FILES['fileInfo']['error'] == UPLOAD_ERR_OK)
{
	// Get root path from session
	$rootPath = fileExplorer::getSessionPath($rootIdentifier);
	// No rootPath found in session with given 'rootIdentifier'
	if (empty($rootPath))
	{
		$jsonReport['status'] = "Invalid path";
		echo json_encode($jsonReport, JSON_FORCE_OBJECT);
		return;
	}
	
	$subPath = str_replace("::", "/", $subPath);
	
	if (!file_exists(systemRoot.$rootPath.$subPath."/"))
	{
		$jsonReport['status'] = "Path doesn't exist";
		echo json_encode($jsonReport, JSON_FORCE_OBJECT);
		return;
	}
	
	// Check file contents here
	$tmp_name = $_FILES['fileInfo']['tmp_name'];
	//$contents = fileManager::get_contents($tmp_name);
	$contentsStatus = TRUE;
	// ----
	if ($contentsStatus !== TRUE)
	{
		echo json_encode($jsonReport, JSON_FORCE_OBJECT);
		return;
	}
	
	// Move file (or not!)		
	$name = $_FILES['fileInfo']['name'];
	// This should go to the fileManager (?)
	$fexplorer = new fileExplorer($rootPath, $rootIdentifier);
	$status = $fexplorer->moveUpload($tmp_name, $subPath."/".$name);
	$jsonReport['status'] = $status;
	if ($status)
	{
		//$info = $fexplorer->getFileDetails($subPath."/".$name);
		$jsonReport['info']['name'] = $_FILES['fileInfo']['name'];
		$jsonReport['info']['type'] = $_FILES['fileInfo']['type'];
		$jsonReport['info']['size'] = $_FILES['fileInfo']['size'];
		$jsonReport['info']['modified'] = "";//$info['modified'];
		$jsonReport['info']['subpath'] = $subPath."/";
		//$jsonReport['info']['debug'] = $info['debug'];
		/*$icon = $fexplorer->previewFileIcon($_FILES['fileInfo']['name'], $subPath."/");
	$jsonReport['info']['iconclass'] = $fexplorer->previewFileIcon($_FILES['fileInfo']['name'], $subPath."/");*/
	}
}

ob_end_clean();
ob_start();
echo json_encode($jsonReport, JSON_FORCE_OBJECT);
//#section_end#
?>