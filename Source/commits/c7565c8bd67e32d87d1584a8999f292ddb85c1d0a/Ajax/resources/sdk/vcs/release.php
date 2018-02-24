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
//Import
importer::import("UI", "Forms", "formReport::formErrorNotification");
importer::import("UI", "Forms", "formReport::formNotification");
importer::import("UI", "Html", "DOM");
importer::import("INU", "Developer", "vcs::releaseManager");

// Use
use \UI\Forms\formReport\formErrorNotification;
use \UI\Forms\formReport\formNotification;
use \UI\Html\DOM;
use \INU\Developer\vcs\releaseManager;

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

// Check release title
$releaseTitle = trim($_POST['title']);
if (empty($releaseTitle))
{
	$has_error = TRUE;
		
	// Header
	$err_header = DOM::create("span", "Release Title");
	$err = $errFormNtf->addErrorHeader("releaseTitle_h", $err_header);
	$errFormNtf->addErrorDescription($err, "releaseTitle_desc", $errFormNtf->getErrorMessage("err.required"));
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

// Get branches to commit
$branches = $_POST['branches'];
foreach ($branches as $commitBranch)
{
	$version = $_POST['version'][$commitBranch];
	$releaseDescription = trim($_POST['description']);
	//$vcs->release($commitBranch, $version, $releaseTitle, $releaseDescription);
}


// Return form report
$succFormNtf = new formNotification();
$succFormNtf->build($type = "success", $header = TRUE, $footer = FALSE, $timeout = TRUE);

// Notification Message
$errorMessage = $succFormNtf->getMessage("success", "success.save_success");
$succFormNtf->append($errorMessage);
echo $succFormNtf->getReport();
//#section_end#
?>