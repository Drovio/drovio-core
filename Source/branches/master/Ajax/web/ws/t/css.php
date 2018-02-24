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
importer::import("DEV", "Websites", "pages/wsPage");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Websites", "templates/wsTemplateTheme");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Protocol\BootLoader;
use \DEV\Websites\pages\wsPage;
use \DEV\Websites\website;
use \DEV\Websites\templates\wsTemplateTheme;
use \DEV\Resources\paths;

// Set header
header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";

// Load Template theme CSS
$websiteID = engine::getVar('wsid');
$templateName = engine::getVar('tname');
$themeName = engine::getVar('thname');
$theme = new wsTemplateTheme($websiteID, $templateName, $themeName);
$themeCSS = $theme->getCSS();

// Resolve project-specific urls
$project = new website($websiteID);
$resourcePath = $project->getResourcesFolder();
$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
$resourceUrl = url::resolve("repo", $resourcePath, "http");
// Remove protocol
$resourceUrl = str_replace("http:", "", $resourceUrl);
// Set Variables
$themeCSS = str_replace("%resources%", $resourceUrl, $themeCSS);
$themeCSS = str_replace("%{resources}", $resourceUrl, $themeCSS);
$themeCSS = str_replace("%media%", $resourceUrl, $themeCSS);
$themeCSS = str_replace("%{media}", $resourceUrl, $themeCSS);

// Resolve all urls
$themeCSS = BootLoader::resolveURLs(NULL, $themeCSS, NULL, $protocol = NULL, "");

// Echo css
echo $themeCSS;
return;
//#section_end#
?>