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

importer::import("ESS", "Protocol", "server::AsCoProtocol");
use \ESS\Protocol\server\AsCoProtocol;

// Ascop Variables
$GLOBALS['__REQUEST'] = $_REQUEST['__REQUEST'];
unset($_REQUEST['__REQUEST']);

AsCoProtocol::set($_REQUEST['__ASCOP']);
unset($_REQUEST['__ASCOP']);
//#section_end#
//#section#[code]
importer::import("API", "Resources", "url");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "formReport::formErrorNotification");

use \API\Resources\Url;
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
	
	// Login account
	$status = account::login($username, $password, ($remember ? (7 * 24 * 60 * 60) : 0));
	
	// Check status
	if ($status)
	{
		// If login is from any open page, just reload the page otherwise go to my page
		$type = $_POST['lgintype'];
		if ($type == "page")
			echo $actionFactory->getReportReload();
		else
			echo $actionFactory->getReportRedirect("/", "my", $formSubmit = TRUE);
		
		return;
	}
	
	// Create Notification
	$errFormNtf = new formErrorNotification();
	$errFormNtf->build();
	echo $errFormNtf->getReport();
	return;
}


// If script is in GET mode, redirect to login.redback.gr
$url = url::resolve("login", $url = "/", $https = FALSE, $full = FALSE);

// Set headers
ob_end_clean();
ob_start();
header("Location: ".$url);
echo $actionFactory->getReportRedirect("/", "login", $formSubmit = FALSE);
return;
//#section_end#
?>