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
importer::import("ESS", "Environment", "url");
importer::import("DEV", "Core", "sdk/sdkPackage");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \DEV\Core\sdk\sdkPackage;
use \DEV\Core\coreProject;
use \DEV\Resources\paths;

// Set header
header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";

// Load SDK CSS
$library = engine::getVar('library');
$package = engine::getVar('package');
$sdkPackage = new sdkPackage();
$packageCSS = $sdkPackage->loadCSS($library, $package);

// Replace resource vars
$project = new coreProject();
$resourcePath = $project->getResourcesFolder()."/media/";
$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
$resourceUrl = url::resolve("repo", $resourcePath);
$packageCSS = str_replace("%resources%", $resourceUrl, $packageCSS);
$packageCSS = str_replace("%{resources}", $resourceUrl, $packageCSS);
$packageCSS = str_replace("%media%", $resourceUrl, $packageCSS);
$packageCSS = str_replace("%{media}", $resourceUrl, $packageCSS);
echo $packageCSS;
return;
//#section_end#
?>