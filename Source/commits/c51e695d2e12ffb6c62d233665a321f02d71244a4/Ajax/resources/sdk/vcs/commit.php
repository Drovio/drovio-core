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
//#section_end#
//#section#[code]
//Import
importer::import("UI", "Forms", "formReport::formErrorNotification");
importer::import("UI", "Forms", "formReport::formNotification");
importer::import("UI", "Html", "DOM");
importer::import("INU", "Developer", "vcsControl");

// Use
use \UI\Forms\formReport\formErrorNotification;
use \UI\Forms\formReport\formNotification;
use \UI\Html\DOM;
use \INU\Developer\vcsControl;

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
$commitDescription = $_POST['summary'];
if (empty($commitDescription))
{
	$has_error = TRUE;
		
	// Header
	$err_header = DOM::create("span", "Commit Summary");
	$err = $errFormNtf->addErrorHeader("commitSumm_h", $err_header);
	$errFormNtf->addErrorDescription($err, "commitSumm_desc", $errFormNtf->getErrorMessage("err.required"));
}

// Add extended description
$commitDescription .= "\n".$_POST['description'];
$commitDescription = trim($commitDescription);

// If error, show notification
if ($has_error)
{
	echo $errFormNtf->getReport();
	return;
}

// Initialize vcsControl
$vcsControl = new vcsControl($_POST['vcs_id']);

// Commit Data

$commitItems = array();
$postItems = $_POST['citem'];
if (is_array($postItems))
	foreach ($postItems as $id => $content)
		$commitItems[] = $id;
	
	
$vcs = $vcsControl->getVcs();
$status = $vcs->commit($commitDescription, $commitItems);


// Return form report
$succFormNtf = new formNotification();
$succFormNtf->build($type = "success", $header = TRUE, $footer = FALSE, $timeout = TRUE);

// Notification Message
$errorMessage = $succFormNtf->getMessage("success", "success.save_success");
$succFormNtf->append($errorMessage);
echo $succFormNtf->getReport();
//#section_end#
?>