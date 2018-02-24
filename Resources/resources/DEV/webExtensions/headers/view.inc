<?php
// Use Important Headers
use \AEL\Platform\classLoader as importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");

// Import DOM, HTML
importer::importCore("UI", "Html", "DOM");
importer::importCore("UI", "Html", "HTML");

use \UI\Html\DOM;
use \UI\Html\HTML;
?>