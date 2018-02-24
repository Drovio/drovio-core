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
importer::import("API", "Model", "modules/mGroup");
importer::import("API", "Profile", "account");
importer::import("DEV", "Modules", "modulesProject");
importer::import("UI", "Content", "JSONContent");

use \API\Model\modules\mGroup;
use \API\Profile\account;
use \DEV\Modules\modulesProject;
use \UI\Content\JSONContent;

// Get moduleGroups
$moduleGroups = array();
$project = new modulesProject();
if (account::validate() && $project->validate())
{
	$groupList = mGroup::getAllGroups();
	foreach ($groupList as $groupInfo)
		$moduleGroups[$groupInfo['id']] = $groupInfo['description'];
}

// Return json content
$json = new JSONContent();
echo $json->getReport($moduleGroups, $allowOrigin = "", $withCredentials = TRUE, $key = "mGroups");
return;
//#section_end#
?>