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
importer::import("API", "Profile", "account");
importer::import("API", "Model", "modules/module");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Modules", "modulesProject");
importer::import("UI", "Content", "JSONContent");

use \API\Model\modules\module;
use \API\Profile\account;
use \DEV\Modules\module as DEVModule;
use \DEV\Modules\modulesProject;
use \UI\Content\JSONContent;

// Get modules
$moduleViews = array();
$moduleQueries = array();
$project = new modulesProject();
if (account::validate() && $project->validate())
{
	$moduleList = module::getAllModules();
	foreach ($moduleList as $moduleInfo)
	{
		// Add module
		$modules[$moduleInfo['id']] = $moduleInfo['title'];
		
		// Get module
		$moduleObject = new DEVModule($moduleInfo['id']);
		
		// Get views
		$views = $moduleObject->getViews();
		$moduleViews = array_merge($moduleViews, $views);
		
		// Get queries
		$queries = $moduleObject->getQueries();
		$moduleQueries = array_merge($moduleQueries, $queries);
	}
}

// Return json content
$json = new JSONContent();
$json->addReportContent($moduleViews, $key = "mViews");
$json->addReportContent($moduleQueries, $key = "mQueries");
echo $json->getReport($modules, $allowOrigin = "", $withCredentials = TRUE, $key = "mNames");
return;
//#section_end#
?>