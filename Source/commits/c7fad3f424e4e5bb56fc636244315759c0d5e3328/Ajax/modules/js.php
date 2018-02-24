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
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \ESS\Protocol\BootLoader;
use \DEV\Modules\module;
use \DEV\Modules\modulesProject;
use \DEV\Resources\paths;

// Set header
header('Content-type: text/javascript');

// Load Module JS
$moduleID = engine::getVar('package');
$moduleObject = new module($moduleID);
$moduleJS = $moduleObject->loadJS();

// Resolve project-specific urls
$project = new modulesProject();
$resourcePath = $project->getResourcesFolder()."/media";
$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
$resourceUrl = url::resolve("repo", $resourcePath, "http");
$moduleJS = str_replace("%resources%", $resourceUrl, $moduleJS);
$moduleJS = str_replace("%{resources}", $resourceUrl, $moduleJS);
$moduleJS = str_replace("%media%", $resourceUrl, $moduleJS);
$moduleJS = str_replace("%{media}", $resourceUrl, $moduleJS);

// Resolve all urls
$moduleJS = BootLoader::resolveURLs(NULL, $moduleJS, NULL, $protocol = NULL, "");

// Echo css
echo $moduleJS;
return;
//#section_end#
?>