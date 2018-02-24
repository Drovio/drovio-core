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
//#section_end#
//#section#[code]
importer::import("ESS", "Protocol", "server::JSONServerReport");
use \ESS\Protocol\server\JSONServerReport;
JSONServerReport::setResponseHeaders();

// Import
importer::import("UI", "Html", "DOM");
importer::import("INU", "Developer", "codeEditor");

// Use
use \UI\Html\DOM;
use \INU\Developer\codeEditor;
//__________ [Initialize Script Objects] __________//

//__________ [Script GET Variables] __________//
$parser = trim($_GET['p']);
$content = trim($_GET['c']);


//__________ [Script Code] __________//
if (!is_string($parser))
	$parser = "";
if (!is_string($content))
	$content = "";

ob_end_clean();
ob_start();
header("Content-Type:text/xml");
echo codeEditor::getParsersInfo($parser, $content);
//#section_end#
?>