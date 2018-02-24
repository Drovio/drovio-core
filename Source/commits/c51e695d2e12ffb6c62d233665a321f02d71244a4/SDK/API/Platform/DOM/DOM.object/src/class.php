<?php
//#section#[header]
// Namespace
namespace API\Platform\DOM;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("UI", "Html", "DOM");

use \UI\Html\DOM as uiDOM;

class DOM extends uiDOM {}
//#section_end#
?>