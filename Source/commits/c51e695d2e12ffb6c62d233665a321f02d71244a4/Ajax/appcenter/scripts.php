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
//importer::import("API", "Developer", "appcenter::application");

// Use
//use \API\Developer\appcenter\application;


// Get Application Parameters
$appName = $_GET['app'];
$token = $_GET['token'];

// Initialize application and check for validation
$runningApp = new application($appName);
//#section_end#
?>