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

// Set the default request headers
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
// Import
importer::import("UI", "Html", "DOM");
importer::import("INU", "Views", "fileExplorer");
importer::import("API", "Literals", "literal");

// Use
use \UI\Html\DOM;
use \INU\Views\fileExplorer;
use \API\Literals\literal;

DOM::initialize();

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];
$fileName = $_GET['fn'];
$mode = $_GET['mode'];

header("Content-Type:application/json");
$jsonReport = array();
// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$jsonReport['status'] = "invalid_path";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_invalidPath", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

if (!is_string($fileName) || empty($fileName)){
	$jsonReport['status'] = "invalid_filename";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_invalidFilename", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

$subPath = str_replace("::", "/", $subPath);

if (!file_exists(systemRoot.$rootPath.$subPath)){
	$jsonReport['status'] = "path_not_exists";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_pathNotExists", NULL, FALSE);
	echo json_encode($jsonReport, JSON_FORCE_OBJECT);
	return;
}

$fexplorer = new fileExplorer($rootPath, $rootIdentifier, null, TRUE, FALSE);
$htmlContent = DOM::create("div");

$previewHtml = "";
if ($mode == "icon")
	$wrap = $fexplorer->iconifyFile($fileName, $subPath);
else {
	$wrap = $fexplorer->previewFile($fileName, $subPath);
}

DOM::append($htmlContent, $wrap);

$jsonReport['status'] = TRUE;
$jsonReport['htmlContent'] = DOM::innerHTML($htmlContent);

ob_clean();
echo json_encode($jsonReport, JSON_FORCE_OBJECT);
//#section_end#
?>