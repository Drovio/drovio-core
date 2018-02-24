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
importer::import("INU", "Developer", "codeEditor");

// Use
use \UI\Html\DOM;
use \INU\Developer\codeEditor;
//__________ [Initialize Script Objects] __________//

//__________ [Script GET Variables] __________//
$name = trim($_GET['n']);

//__________ [Script Code] __________//
if (!is_string($name))
	$name = "";

ob_end_clean();
ob_start();
header("Content-Type:text/javascript");
//#section_end#
?>