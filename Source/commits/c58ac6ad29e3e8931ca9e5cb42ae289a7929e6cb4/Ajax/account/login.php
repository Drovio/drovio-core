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
importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::JSONServerReport");
use \ESS\Protocol\server\AsCoProtocol;

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

// Set the headers at least once
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
importer::import("API", "Resources", "url");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "formReport::formErrorNotification");

use \API\Resources\url;
use \API\Security\account;
use \UI\Html\HTMLContent;
use \UI\Html\DOM;
use \UI\Forms\formReport\formErrorNotification;

// Initialize DOM
DOM::initialize();

// Create Module Page
$pageContent = new HTMLContent();
$actionFactory = $pageContent->getActionFactory();

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	$username = $_POST['username'];
	$password = $_POST['password'];
	$remember = ($_POST['rememberme'] == "on");
	$logintype = $_POST['logintype'];
	
	// Login account (set duration in seconds for one week)
	$duration = (7 * 24 * 60 * 60);
	$status = account::login($username, $password, ($remember ? ($duration) : 0));
	
	// Check status
	if ($status)
	{
		// If login is from any open page, just reload the page otherwise go to my page
		if ($logintype == "page")
			echo $actionFactory->getReportReload();
		else
		{
			// Check if there is specific redirection
			$return_path = (isset($_POST['return_path']) ? $_POST['return_path'] : "/");
			$return_sub = (isset($_POST['return_sub']) ? $_POST['return_sub'] : "my");
			$return_sub = (empty($return_sub) ? "www" : $return_sub);
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