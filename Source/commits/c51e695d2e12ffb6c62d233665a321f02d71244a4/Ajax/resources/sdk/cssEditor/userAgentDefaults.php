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
// Import
importer::import("UI", "Html", "DOM");
importer::import("INU", "Developer", "cssEditor");

// Use
use \UI\Html\DOM;
use \INU\Developer\cssEditor;
//__________ [Initialize Script Objects] __________//

//__________ [Script GET Variables] __________//
$browser = trim($_GET['userAgent']);

//__________ [Script Code] __________//

$browsers = array();
$browsers[] = "firefox";
$browsers[] = "iexplorer";
$browsers[] = "opera";
$browsers[] = "w3c";
$browsers[] = "webkit";

$css = "";
if (isset($browser) && in_array($browser, $browsers))
	$css = cssEditor::getUserAgentCss($browser);

ob_end_clean();
ob_start();
header("Content-Type:text/css");
echo $css;
//#section_end#
?>