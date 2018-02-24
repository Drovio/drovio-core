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
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Literals", "literal");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("API", "Resources", "forms/inputValidator");
importer::import("UI", "Content", "HTMLContent");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Login", "registerDialog");
importer::import("UI", "Forms", "formReport/formErrorNotification");

use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \API\Literals\literal;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \API\Resources\forms\inputValidator;
use \UI\Content\HTMLContent;
use \UI\Html\DOM;
use \UI\Login\registerDialog;
use \UI\Forms\formReport\formErrorNotification;

// Initialize DOM
DOM::initialize();

// Create Module Page
$pageContent = new HTMLContent();
$actionFactory = $pageContent->getActionFactory();

if (engine::isPost())
{
	$has_error = FALSE;
	
	// Create form Notification
	$errFormNtf = new formErrorNotification();
	$formNtfElement = $errFormNtf->build()->get();
	
	// Check Firstname
	if (empty($_POST['firstname']))
	{
		$has_error = TRUE;
		
		// Header
		$err_header = literal::get("sdk.UI.register", "lbl_firstname");
		$err = $errFormNtf->addHeader($err_header);
		$errFormNtf->addDescription($err, $errFormNtf->getErrorMessage("err.required"));
	}
	
	// Check Lastname
	if (empty($_POST['lastname']))
	{
		$has_error = TRUE;
		
		// Header
		$err_header = literal::get("sdk.UI.register", "lbl_lastname");
		$err = $errFormNtf->addHeader($err_header);
		$errFormNtf->addDescription($err, $errFormNtf->getErrorMessage("err.required"));
	}
	
	// Check Email
	$empty = empty($_POST['email']);
	$valid = inputValidator::checkEmail($_POST['email']);
	if ($empty || !$valid)
	{
		$has_error = TRUE;
		
		// Empty
		
		$err_header = literal::get("sdk.UI.register", "lbl_email");
		$err = $errFormNtf->addHeader($err_header);
		if ($empty)
			$errFormNtf->addDescription($err, $errFormNtf->getErrorMessage("err.required"));
		if (!$valid)
			$errFormNtf->addDescription($err, $errFormNtf->getErrorMessage("err.invalid"));
	}
	
	// Check Email match
	$match = ($_POST['email'] == $_POST['email2']);
	if (!$match)
	{
		$has_error = TRUE;
		
		// Header
		$err_header = literal::get("sdk.UI.register", "lbl_email");
		$err = $errFormNtf->addHeader($err_header);
		$errFormNtf->addDescription($err, $errFormNtf->getErrorMessage("err.validate"));
	}
	
	// Check Password
	if (empty($_POST['password']))
	{
		$has_error = TRUE;
		
		// Header
		$err_header = literal::get("sdk.UI.register", "lbl_password");
		$err = $errFormNtf->addHeader($err_header);
		$errFormNtf->addDescription($err, $errFormNtf->getErrorMessage("err.required"));
	}
	
	// If error, show notification
	if ($has_error)
	{
		echo $errFormNtf->getReport();
		return;
	}
	
	
	// Initialize db connection
	$dbc = new dbConnection();
	
	// Check if there is a non-activated account with the provided email.
	$dbq = new dbQuery("27857013413845", "profile.account");
	
	// Set attributes
	$attr = array();
	$attr["firstname"] = $_POST['firstname'];
	$attr["lastname"] = $_POST['lastname'];
	$attr["password"] = hash("SHA256", $_POST['password']);
	$attr['accountTitle'] = $_POST['firstname']." ".$_POST['lastname'];
	$attr["email"] = $_POST['email'];
	
	// Get non activated person
	$result = $dbc->execute($dbq, $attr);
	if ($dbc->get_num_rows($result) > 0)
	{
		// Register Existing Person
		$person = $dbc->fetch($result);
		$attr['pid'] = $person['id'];
		
		$dbq = new dbQuery("17681219549219", "profile.account");
		$result = $dbc->execute($dbq, $attr);
	}
	else
	{
		// Register New Person
		$dbq = new dbQuery("24129740479924", "profile.account");
		$result = $dbc->execute($dbq, $attr);
	}
	
	// If there is an error in registration, show it
	if (!$result)
	{
		$err_header = literal::get("sdk.UI.register", "lbl_register");
		$err = $errFormNtf->addHeader($err_header);
		$errSpan = literal::get("sdk.UI.register", "lbl_registerError");
		$errFormNtf->addDescription($err, $errSpan);
		echo $errFormNtf->getReport();
		return;
	}
	
	// Successful Registration, login account
	account::login($_POST["email"], $_POST['password']);
	
	// Get register type
	$regtype = engine::getVar('regtype');
	// If login is from any open page, just reload the page otherwise go to my page
	if ($regtype == registerDialog::REG_TYPE_APP)
		echo $actionFactory->getReportReload();
	else
	{
		// Check for return url or redirect to my
		$return_url = engine::getVar('return_url');
		if (!empty($return_url))
			echo $actionFactory->getReportRedirect($return_url);
		else
			echo $actionFactory->getReportRedirect("/profile/", "www", TRUE);
	}
	return;
}

// Return redirect
$url = url::resolve("my", $url = "/register/");
echo $actionFactory->getReportRedirect($url, "", $formSubmit = FALSE);
return;
//#section_end#
?>