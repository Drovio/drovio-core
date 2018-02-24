<?php
// Define systemRoot for website
define("siteInnerPath", '{siteInnerPath}');
define("systemRoot", $_SERVER['DOCUMENT_ROOT']."/".siteInnerPath);

// Include website configuration file
require_once(systemRoot."/__websiteConfig.inc");

// Use Importer
use \WAPI\Platform\importer;

// Import Page Loader class
importer::import("WEB", "Platform", "pageLoader");
use \WEB\Platform\pageLoader;

// Load Page
echo pageLoader::load();
?>