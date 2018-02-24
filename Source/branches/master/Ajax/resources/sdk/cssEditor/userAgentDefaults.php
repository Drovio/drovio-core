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
importer::import("UI", "Html", "DOM");
importer::import("INU", "Developer", "cssEditor");

use \UI\Html\DOM;
use \INU\Developer\cssEditor;

// Get browser
$browser = trim($_GET['userAgent']);

// Init all browsers
$browsers = array();
$browsers[] = "firefox";
$browsers[] = "iexplorer";
$browsers[] = "opera";
$browsers[] = "w3c";
$browsers[] = "webkit";

$css = "";
if (isset($browser) && in_array($browser, $browsers))
	$css = cssEditor::getUserAgentCss($browser);

ob_clean();
header("Content-Type:text/css");
echo $css;
//#section_end#
?>