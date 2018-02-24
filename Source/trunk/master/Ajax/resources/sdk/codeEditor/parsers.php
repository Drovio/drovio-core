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
importer::import("UI", "Developer", "codeEditor");

use \UI\Html\DOM;
use \UI\Developer\codeEditor;

$parser = trim($_GET['p']);
$content = trim($_GET['c']);


if (!is_string($parser))
	$parser = "";
if (!is_string($content))
	$content = "";

ob_clean();
header("Content-Type:text/xml");
echo codeEditor::getParsersInfo($parser, $content);
//#section_end#
?>