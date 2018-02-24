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
// Import
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Presentation", "tabControl");

// Use
use \API\Resources\DOMParser;
use \UI\Html\DOM;
use \UI\Html\HTMLContent;
use \UI\Presentation\tabControl;

DOM::initialize();
$pageContent = new HTMLContent();

// Get tabControl parameters
$tab_id = "%tabID";
$tabber_id = $_GET['tabber_id'];
$editable = $_GET['editable'];

// Create tabber
$tabber = new tabControl($editable);

// General External Holder for containers
$holder = DOM::create("div", "", $tabber_id, "tabPageContainer");
$pageContent->buildElement($holder);

// Create Tab Container
$container = $tabber->getTabberContainer($tab_id, $tab_header, NULL, $nav_id = $tabber_id, $selected = FALSE);

// Create menu Item
$menuItem = DOM::create("div", $container['menuItem'], "", "menuItem");
$pageContent->append($menuItem);

// Create tab page
$tabPage = DOM::create("div", $container['tabPage'], "", "tabPage");
$pageContent->append($tabPage);

// Return the result
ob_clean();
echo $pageContent->getReport();
return;
//#section_end#
?>