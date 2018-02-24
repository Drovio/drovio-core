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
importer::import("API", "Literals", "literal");
importer::import("INU", "Views", "fileExplorer");
importer::import("UI", "Content", "JSONContent");

use \API\Literals\literal;
use \INU\Views\fileExplorer;
use \UI\Content\JSONContent;

// File Explorer ID, used to identify root path
$rootIdentifier = $_POST['fexId'];
$subPath = $_POST['subPath'];
$folderName = $_POST['fName'];

$json = new JSONContent();
$jsonReport = array();
// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$jsonReport['status'] = "invalid_path";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_invalidPath", NULL, FALSE);
	echo $json->getReport($jsonReport);
	return;
}

$readOnly = fileExplorer::isReadOnly($rootIdentifier);
// Cannot create folder in read only mode
if ($readOnly)
{
	$jsonReport['status'] = "read_only";
	$jsonReport['msg'] = "read_only";
	echo $json->getReport($jsonReport);
	return;
}

if (!is_string($folderName) || empty($folderName))
{
	$jsonReport['status'] = "invalid_folder_name";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_invalidFolderName", NULL, FALSE);
	echo $json->getReport($jsonReport);
	return;
}

$subPath = str_replace("::", "/", $subPath);

if (!file_exists(systemRoot.$rootPath.$subPath."/"))
{
	$jsonReport['status'] = "path_not_exists";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_pathNotExists", NULL, FALSE);
	echo $json->getReport($jsonReport);
	return;
}

$fexplorer = new fileExplorer($rootPath, $rootIdentifier, null, TRUE, FALSE);
$success = $fexplorer->createFolder($folderName, $subPath);

$jsonReport['status'] = $success;

ob_end_clean();
ob_start();
echo $json->getReport($jsonReport);
//#section_end#
?>