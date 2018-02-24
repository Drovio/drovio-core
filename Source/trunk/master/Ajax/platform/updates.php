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
importer::import("DEV", "Projects", "projectLibrary");
importer::import("UI", "Content", "JSONContent");

use \DEV\Projects\projectLibrary;
use \UI\Content\JSONContent;

// Get platform live project versions
$projects = array(1, 2);
$platformVersions = array();
foreach ($projects as $projectID)
	$platformVersions[$projectID] = projectLibrary::getLastProjectVersion($projectID, $live = TRUE);

// Return json content
$json = new JSONContent();
echo $json->getReport($platformVersions, $allowOrigin = "", $withCredentials = TRUE, $key = "platform.versions");
return;
//#section_end#
?>