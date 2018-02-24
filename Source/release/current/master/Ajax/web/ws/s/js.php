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
importer::import("ESS", "Protocol", "BootLoader");
importer::import("DEV", "Websites", "source/srcPackage");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Protocol\BootLoader;
use \DEV\Websites\source\srcPackage;
use \DEV\Websites\website;
use \DEV\Resources\paths;

// Set header
header('Content-type: text/javascript');

// Load SDK JS
$websiteID = engine::getVar('wsid');
$library = engine::getVar('library');
$package = engine::getVar('package');
$sdkPackage = new srcPackage($websiteID);
$packageJS = $sdkPackage->loadJS($library, $package);

// Resolve project-specific urls
$project = new website($websiteID);
$resourcePath = $project->getResourcesFolder();
$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
$resourceUrl = url::resolve("repo", $resourcePath, "http");
// Remove protocol
$resourceUrl = str_replace("http:", "", $resourceUrl);
// Set Variables
$packageJS = str_replace("%resources%", $resourceUrl, $packageJS);
$packageJS = str_replace("%{resources}", $resourceUrl, $packageJS);
$packageJS = str_replace("%media%", $resourceUrl, $packageJS);
$packageJS = str_replace("%{media}", $resourceUrl, $packageJS);

// Resolve all urls
$packageJS = BootLoader::resolveURLs(NULL, $packageJS, NULL, $protocol = NULL, "");

// Echo css
echo $packageJS;
return;
//#section_end#
?>