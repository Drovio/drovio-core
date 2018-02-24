<?php
// Define Redback Website
define("_RB_WEBSITE_", 1);

// Set Error Reporting Settings
error_reporting(E_ALL & ~(E_STRICT|E_WARNING|E_NOTICE));
ini_set("display_errors", TRUE);

// Define systemRoot for website (if not defined)
define("siteInnerPath", '{siteInnerPath}');
if (!defined(wsystemRoot))
	define("wsystemRoot", $_SERVER['DOCUMENT_ROOT']."/".siteInnerPath);

// Define website constants
define("wecRoot", "/.wec/");
define("wsdkRoot", wecRoot."SDK/");
define("wclibRoot", "/lib/wc/");
define("wcrsrcRoot", wclibRoot."/rsrc/");

define("websiteRoot", "/.website/");
define("websiteSettings", websiteRoot."/settings/");
define("wslibRoot", "/lib/ws/");
define("wsrsrcRoot", wslibRoot."/rsrc/");


// Deprecated
define("systemWEC", "/.wec/");
define("systemWSDK", systemWEC."SDK/");
define("wlibRoot", "/lib/");
define("wrsrcRoot", wlibRoot."/rsrc/");

// Load Importer
require_once(systemRoot.systemWSDK."/WAPI/Platform/importer.php");
use \WAPI\Platform\importer;

// Start website engine
importer::import("WAPI", "Platform", "engine");
use \WAPI\Platform\engine;
engine::start();
?>