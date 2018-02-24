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
//Import
importer::import("UI", "Forms", "formReport::formErrorNotification");
importer::import("UI", "Forms", "formReport::formNotification");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Version", "commitManager");

// Use
use \UI\Forms\formReport\formErrorNotification;
use \UI\Forms\formReport\formNotification;
use \UI\Html\DOM;
use \DEV\Version\commitManager;

DOM::initialize();

// Create form Notification
$errFormNtf = new formErrorNotification();
$formNtfElement = $errFormNtf->build()->get();

if ($_SERVER['REQUEST_METHOD'] != "POST")
{
	$has_error = TRUE;
		
	// Header
	$err_header = DOM::create("span", "Request Method");
	$err = $errFormNtf->addErrorHeader("rqmethod_h", $err_header);
	$errFormNtf->addErrorDescription($err, "rqmethod_desc", "Request Method Error");
}

// Check commit summary
$commitSummary = trim($_POST['summary']);
if (empty($commitSummary))
{
	$has_error = TRUE;
		
	// Header
	$err_header = DOM::create("span", "Commit Summary");
	$err = $errFormNtf->addErrorHeader("commitSumm_h", $err_header);
	$errFormNtf->addErrorDescription($err, "commitSumm_desc", $errFormNtf->getErrorMessage("err.required"));
}

// If error, show notification
if ($has_error)
{
	echo $errFormNtf->getReport();
	return;
}

// Initialize vcsControl
$vcsControl = new commitManager($_POST['vcs_id']);

// Commit Data

$commitItems = array();
$postItems = $_POST['citem'];
if (is_array($postItems))
	foreach ($postItems as $id => $content)
		$commitItems[] = $id;
	
$vcs = $vcsControl->getVcs();
$commitDescription = trim($_POST['description']);
$status = $vcs->commit($commitSummary, $commitDescription, $commitItems);


// Return form report
$succFormNtf = new formNotification();
$succFormNtf->build($type = "success", $header = TRUE, $footer = FALSE, $timeout = TRUE);

// Notification Message
$errorMessage = $succFormNtf->getMessage("success", "success.save_success");
$succFormNtf->append($errorMessage);
echo $succFormNtf->getReport();
//#section_end#
?>