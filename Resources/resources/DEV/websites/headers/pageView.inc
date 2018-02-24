<?php
// Check Website Platform (prevent outside executions)
if (!defined("_RB_WEBSITE_"))
	throw new Exception("Page is not loaded properly!");

// Use Platform classes
use \WAPI\Platform\importer;
use \WAPI\Platform\engine;

// Import Web Platform class
importer::import("WEB", "Platform", "website");
use \WEB\Platform\website;

// Import Initial Libraries
importer::import("WUI", "Html", "HTML");

// Use
use \WUI\Html\HTML;
?>