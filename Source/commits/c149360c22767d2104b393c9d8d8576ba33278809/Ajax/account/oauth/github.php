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
importer::import("API", "Login", "social/githubAccount");
importer::import("API", "Profile", "team");
importer::import("UI", "Content", "HTMLContent");
importer::import("UI", "Html", "DOM");

use \ESS\Environment\url;
use \API\Login\social\githubAccount;
use \API\Profile\team;
use \UI\Content\HTMLContent;
use \UI\Html\DOM;

// Initialize DOM
DOM::initialize();

// Create Module Page
$pageContent = new HTMLContent();
$actionFactory = $pageContent->getActionFactory();

// Get github code
$code = engine::getVar("code");
$status = githubAccount::getInstance()->login($code);
if ($status)
{
	// Get default team and switch to that
	$defaultTeam = team::getDefaultTeam();
	$defaultTeamID = $defaultTeam['id'];
	if (isset($defaultTeamID))
		team::switchTeam($defaultTeamID, $password);

	// Check for return url
	$return_url = engine::getVar('return_url');
	if (empty($return_url))
	{
		// Check if there is specific redirection
		$return_sub = engine::getVar('return_sub');
		$return_sub = (empty($return_sub) ? "www" : $return_sub);

		$return_path = engine::getVar('return_path');
		$return_path = (isset($return_path) ? $return_path : "/");

		// Form return url
		$return_url = url::resolve($return_sub, $return_path);
	}

	// Redirect
	echo $actionFactory->getReportRedirect("/", "www", $formSubmit = TRUE);
	return;
}
else
{
	$params = array();
	$params['error'] = "1";
	$params['return_path'] = AsCoProtocol::getPath();
	$params['return_sub'] = AsCoProtocol::getSubdomain();
	$url = url::resolve("www", "/login/index.php", $params);
	echo $actionFactory->getReportRedirect($url, "", $formSubmit = TRUE);
	return;
}

// Return redirect
$url = url::resolve("login", $url = "/");
echo $actionFactory->getReportRedirect($url, "", $formSubmit = FALSE);
return;
//#section_end#
?>