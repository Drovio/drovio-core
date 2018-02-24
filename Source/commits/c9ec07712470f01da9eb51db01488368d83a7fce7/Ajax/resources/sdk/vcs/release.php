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

// Set the default request headers
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
//Import
importer::import("UI", "Forms", "formReport::formErrorNotification");
importer::import("UI", "Forms", "formReport::formNotification");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Version", "tools::releaseManager");

// Use
use \UI\Forms\formReport\formErrorNotification;
use \UI\Forms\formReport\formNotification;
use \UI\Html\DOM;
use \DEV\Version\tools\releaseManager;

DOM::initialize();

// Create form Notification
$errFormNtf = new formErrorNotification();
$formNtfElement = $errFormNtf->build()->get();

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	// Check release title
	$releaseTitle = trim($_POST['title']);
	if (empty($releaseTitle))
	{
		$has_error = TRUE;
			
		// Header
		$err_header = DOM::create("span", "Release Title");
		$err = $errFormNtf->addErrorHeader("releaseTitle_h", $err_header);
		$errFormNtf->addErrorDescription($err, "releaseTitle_desc", $errFormNtf->getErrorMessage("err3.required"));
	}
	
	// Check release version
	$releaseVersion = trim($_POST['version']);
	if (empty($releaseVersion))
	{
		$has_error = TRUE;
			
		// Header
		$err_header = DOM::create("span", "Release Version");
		$err = $errFormNtf->addErrorHeader("releaseVersion_h", $err_header);
		$errFormNtf->addErrorDescription($err, "releaseVersion_desc", $errFormNtf->getErrorMessage("err.required"));
	}
	
	// If error, show notification
	if ($has_error)
	{
		echo $errFormNtf->getReport();
		return;
	}
	
	// Initialize vcsControl
	$vcsControl = new releaseManager($_POST['vcs_id']);
	$vcs = $vcsControl->getVcs();
	
	// Release branch
	$branchName = $_POST['branchName'];
	$releaseDescription = trim($_POST['description']);
	$releaseVersion = str_replace(",", ".", $releaseVersion);
	$status = $vcs->release($branchName, $releaseVersion, $releaseTitle, $releaseDescription);
	
	if (!$status)
	{
		$err_header = DOM::create("span", "Release");
		$err = $errFormNtf->addErrorHeader("releaseTitle_h", $err_header);
		$errFormNtf->addErrorDescription($err, "releaseTitle_desc", "Error occured in project release.");
		echo $errFormNtf->getReport();
	}
	
	// Return form report
	$succFormNtf = new formNotification();
	$succFormNtf->build($type = "success", $header = TRUE, $footer = FALSE, $timeout = TRUE);
	
	// Notification Message
	$errorMessage = $succFormNtf->getMessage("success", "success.save_success");
	$succFormNtf->append($errorMessage);
	echo $succFormNtf->getReport();
}
//#section_end#
?>