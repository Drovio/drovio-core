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
importer::import("API", "Profile", "team");
importer::import("API", "Profile", "account");
importer::import("UI", "Content", "HTMLContent");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Login", "loginDialog");
importer::import("UI", "Forms", "formReport/formErrorNotification");

use \ESS\Environment\url;
use \API\Profile\team;
use \API\Profile\account;
use \UI\Content\HTMLContent;
use \UI\Html\DOM;
use \UI\Login\loginDialog;
use \UI\Forms\formReport\formErrorNotification;

// Initialize DOM
DOM::initialize();

// Create Module Page
$pageContent = new HTMLContent();
$actionFactory = $pageContent->getActionFactory();

if (engine::isPost())
{
	$username = engine::getVar('username');
	$password = engine::getVar('password')
	$rememberme = (engine::getVar('rememberme') == "on");
	$logintype = engine::getVar('logintype');
	
	// Login account (set duration in seconds for one week)
	$status = account::login($username, $password, $rememberme);
	
	// Check status
	if ($status)
	{
		// Get default team and switch to that
		$defaultTeam = team::getDefaultTeam();
		$defaultTeamID = $defaultTeam['id'];
		if (isset($defaultTeamID))
			team::switchTeam($defaultTeamID, $password);
		
		// If login is from any open page, just reload the page otherwise go to my page
		if ($logintype == loginDialog::LGN_TYPE_PAGE || $logintype == loginDialog::LGN_TYPE_APP)
			echo $actionFactory->getReportReload();
		else
		{
			// Check if there is specific redirection
			$return_sub = engine::getVar('return_sub');
			$return_sub = (isset($return_sub) ? $return_sub : "my");
			$return_sub = (empty($return_sub) ? "www" : $return_sub);
			
			$return_path = engine::getVar('return_path');
			$return_path = (isset($return_path) ? $return_path : "/");
			
			// Redirect
			echo $actionFactory->getReportRedirect($return_path, $return_sub, $formSubmit = TRUE);
		}
		
		return;
	}
	else
	{
		if ($logintype == "page")
		{
			$url = "/index.php";
			$params = array();
			$params['error'] = "1";
			$params['return_path'] = AsCoProtocol::getPath();
			$params['return_sub'] = AsCoProtocol::getSubdomain();
			$url = url::get($url, $params);
			echo $actionFactory->getReportRedirect($url, "login", $formSubmit = TRUE);
			return;
		}
		else
		{
			// Create Error Notification
			$errFormNtf = new formErrorNotification();
			$errFormNtf->build();
			echo $errFormNtf->getReport();
			return;
		}
	}
}

// Clean and return redirect
ob_clean();
$url = url::resolve("login", $url = "/", $https = FALSE, $full = FALSE);
echo $actionFactory->getReportRedirect($url, "", $formSubmit = FALSE);
return;
//#section_end#
?>