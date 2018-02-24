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
importer::import("DEV", "WebEngine", "sdk/webPackage");
importer::import("DEV", "WebEngine", "webCoreProject");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Protocol\BootLoader;
use \DEV\WebEngine\sdk\webPackage;
use \DEV\WebEngine\webCoreProject;
use \DEV\Resources\paths;

// Set header
header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";

// Load SDK CSS
$library = engine::getVar('library');
$package = engine::getVar('package');
$sdkPackage = new webPackage();
$packageCSS = $sdkPackage->loadCSS($library, $package);

// Resolve project-specific urls
$project = new webCoreProject();
$resourcePath = $project->getResourcesFolder();
$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
$resourceUrl = url::resolve("repo", $resourcePath, "http");
// Remove protocol
$resourceUrl = str_replace("http:", "", $resourceUrl);
// Set Variables
$packageCSS = str_replace("%resources%", $resourceUrl, $packageCSS);
$packageCSS = str_replace("%{resources}", $resourceUrl, $packageCSS);
$packageCSS = str_replace("%media%", $resourceUrl, $packageCSS);
$packageCSS = str_replace("%{media}", $resourceUrl, $packageCSS);

// Resolve all urls
$packageCSS = BootLoader::resolveURLs(NULL, $packageCSS, NULL, $protocol = NULL, "");

// Echo css
echo $packageCSS;
return;
//#section_end#
?>