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
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "views/appView");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Protocol\BootLoader;
use \DEV\Apps\application;
use \DEV\Apps\views\appView;
use \DEV\Resources\paths;

// Set content type header
header('Content-type: text/css');
echo "@charset \"UTF-8\";\n";

// Init application
$appID = engine::getVar('library');
$viewName = engine::getVar('package');
if (empty($appID))
{
	echo "/* There is no valid application id. */";
	return;
}

// Create application object
$app = new application($appID);

// Initialize view and get css
$appView = new appView($appID, trim(dirname($viewName), "/"), basename($viewName));
$cssContent = $appView->getCSS($normalCSS = TRUE);

// Resolve project-specific urls
$resourcePath = $app->getResourcesFolder();
$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
$resourceUrl = url::resolve("repo", $resourcePath, "http");
// Remove protocol
$resourceUrl = str_replace("http:", "", $resourceUrl);
// Set Variables
$cssContent = str_replace("%resources%", $resourceUrl, $cssContent);
$cssContent = str_replace("%{resources}", $resourceUrl, $cssContent);
$cssContent = str_replace("%media%", $resourceUrl, $cssContent);
$cssContent = str_replace("%{media}", $resourceUrl, $cssContent);

// Resolve all urls
$cssContent = BootLoader::resolveURLs(NULL, $cssContent, NULL, $protocol = NULL, "");

// Echo css
echo $cssContent;
return;
//#section_end#
?>