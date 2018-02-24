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
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Resources", "paths");
importer::import("UI", "Content", "JSONContent");

use \ESS\Environment\url;
use \DEV\Projects\project;
use \DEV\Resources\paths;
use \UI\Content\JSONContent;

// Get all account projects
$fullProjects = array();
$projects = project::getAccountProjects();
foreach ($projects as $project)
{
	$prj = new project($project['id']);
	$rootFolder = $prj->getRootFolder();
	$resourcesFolder = $prj->getResourcesFolder();
	
	// Resolve paths
	$rootFolder = str_replace(paths::getRepositoryPath(), "", $rootFolder);
	$project['dev_root'] = url::resolve("repo", $rootFolder);
	
	$resourcesFolder = str_replace(paths::getRepositoryPath(), "", $resourcesFolder);
	$project['dev_resources'] = url::resolve("repo", $resourcesFolder);
	
	// Add to list
	$fullProjects[$project['id']] = $project;
}

// Create json content
$jsContent = new JSONContent();
echo $jsContent->getReport($fullProjects, $allowOrigin = "", $withCredentials = TRUE, $key = "projects");
return;
//#section_end#
?>