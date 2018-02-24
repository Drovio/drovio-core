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
importer::import("INU", "Views", "fileExplorer");
importer::import("API", "Literals", "literal");

// Use
use \INU\Views\fileExplorer;
use \API\Literals\literal;

//__________ [Initialize Script Objects] __________//

//__________ [Script POST Variables] __________//
// File Explorer ID, used to identify root path
$rootIdentifier = $_POST['fexId'];
$subPath = $_POST['subPath'];
$fileNames = $_POST['fNames'];
$copy = (isset($_POST['copy']) ? TRUE : FALSE);
$destination = $_POST['fdest'];

//__________ [Script Code] __________//
header("Content-Type:application/json");
$jsonReport = array();
// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$jsonReport['status'] = literal::get("sdk.INU.Views", "msg_invalidPath", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}
	
if (!is_array($fileNames))
{
	$jsonReport['status'] = literal::get("sdk.INU.Views", "msg_invalidFiles", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}
	
$fileNames = array_filter($fileNames,
	function($value)
	{
		return is_string($value) && (!empty($value));
	}
);
	
if (empty($fileNames))
{
	$jsonReport['status'] = literal::get("sdk.INU.Views", "msg_invalidFiles", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

$subPath = str_replace("::", "/", $subPath);

if (!is_dir(systemRoot.$rootPath.$subPath."/"))
{
	$jsonReport['status'] = literal::get("sdk.INU.Views", "msg_pathNotExists", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

if (!is_dir(systemRoot.$rootPath."/".$destination."/"))
{
	$jsonReport['status'] = literal::get("sdk.INU.Views", "msg_destinationNotExists", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

$fexplorer = new fileExplorer($rootPath, $rootIdentifier);
$info = array();
if ($copy === TRUE)
	$info = $fexplorer->copyFiles($subPath, $fileNames, $destination);
else
	$info = $fexplorer->moveFiles($subPath, $fileNames, $destination);

$jsonReport['status'] = $info;
$jsonReport['copy'] = $copy;
$jsonReport['fileNames'] = $fileNames;
$jsonReport['subPath'] = $subPath; 
$jsonReport['destination'] = $destination;

ob_end_clean();
ob_start();
echo json_encode($jsonReport, JSON_FORCE_OBJECT);
//#section_end#
?>