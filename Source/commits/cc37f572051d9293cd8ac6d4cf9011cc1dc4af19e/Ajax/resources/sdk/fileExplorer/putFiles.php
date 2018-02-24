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
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Literals", "literal");
importer::import("INU", "Views", "fileExplorer");
importer::import("UI", "Content", "JSONContent");

use \UI\Content\JSONContent;
use \API\Resources\filesystem\fileManager;
use \API\Literals\literal;
use \INU\Views\fileExplorer;

// Initialize json content
$json = new JSONContent();

// Initialize fileExplorer vars
$rootIdentifier = $_POST['fexId'];
$subPath = $_POST['subPath'];

// Static map of upload core constants based on their values
$uploadErrors = array();
$uploadErrors[0] = "UPLOAD_ERR_OK";
$uploadErrors[1] = "UPLOAD_ERR_INI_SIZE";
$uploadErrors[2] = "UPLOAD_ERR_FORM_SIZE";
$uploadErrors[3] = "UPLOAD_ERR_PARTIAL";
$uploadErrors[4] = "UPLOAD_ERR_NO_FILE";
$uploadErrors[5] = "UPLOAD_ERR_NO_TMP_DIR";
$uploadErrors[6] = "UPLOAD_ERR_CANT_WRITE";
$uploadErrors[7] = "UPLOAD_ERR_EXTENSION";

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
		$jsonReport['status'] = "invalid_path";
		$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_invalidPath", array(), FALSE);
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
	
	
	$subPath = str_replace("::", "/", $subPath);
	if (!file_exists(systemRoot.$rootPath.$subPath."/"))
	{
		$jsonReport['status'] = "path_not_exists";
		$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_pathNotExists", array(), FALSE);
		echo $json->getReport($jsonReport);
		return;
	}
	
	// Check file contents here
	$tmp_name = $_FILES['fileInfo']['tmp_name'];
	$contentsStatus = TRUE;
	if ($contentsStatus !== TRUE)
	{
		echo $json->getReport($jsonReport);
		return;
	}
	
	// Move file (or not!)
	$name = $_FILES['fileInfo']['name'];
	$fexplorer = new fileExplorer($rootPath, $rootIdentifier, null, TRUE, FALSE);
	$fexplorer->moveUploadedFile($tmp_name, $subPath."/".$name);
	
	if ($jsonReport['status'])
	{
		$jsonReport['info']['name'] = $_FILES['fileInfo']['name'];
		$jsonReport['info']['type'] = $_FILES['fileInfo']['type'];
		$jsonReport['info']['size'] = $_FILES['fileInfo']['size'];
		$jsonReport['info']['modified'] = "";//$info['modified'];
		$jsonReport['info']['subpath'] = $subPath."/";
	}
}

// Get json report
echo $json->getReport($jsonReport);
return;
//#section_end#
?>