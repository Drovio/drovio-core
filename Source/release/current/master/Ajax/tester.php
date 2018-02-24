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
importer::import("ESS", "Environment", "url");
importer::import("DEV", "Core", "ajax/ajaxPage");
importer::import("API", "Resources", "filesystem/directory");

use \ESS\Environment\url;
use \DEV\Core\ajax\ajaxPage;
use \API\Resources\filesystem\directory;

// Get file path
$filePath = $_REQUEST['__AJAX']['path'];
$filePath = (empty($filePath) ? $_REQUEST['__AJAX_PATH'] : $filePath);
if (empty($filePath))
	return;

// Clear path
$filePath = str_replace("http://", "", $filePath);
$filePath = str_replace("https://", "", $filePath);
$filePath = str_replace(url::getDomain(), "", $filePath);
$filePath = directory::normalize($filePath);
$filePath = str_replace("ajax/", "", $filePath);

// Split, get directory and page
$parts = explode("/", $filePath);

// Get page name
$pageName = $parts[count($parts)-1];
$pageName = str_replace(".php", "", $pageName);
unset($parts[count($parts)-1]);

// Normalize directory for developer
if ($parts[0] == "")
	unset($parts[0]);
$pageDirectory = directory::normalize(implode("/", $parts));
$pageDirectory = ($pageDirectory == "/" ? "" : $pageDirectory);

// Clean output
ob_clean();

// Initialize ajax page
$page = new ajaxPage($pageName, $pageDirectory);
$page->run();
return;
//#section_end#
?>