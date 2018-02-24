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
importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::JSONServerReport");
use \ESS\Protocol\server\AsCoProtocol;

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

// Set the headers at least once
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
// Import
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "tabControl");

// Use
use \API\Resources\DOMParser;
use \UI\Html\DOM;
use \UI\Presentation\tabControl;

DOM::initialize();
$builder = new DOMParser();

// Get tabControl parameters
$tab_id = $_GET['tab_id'];
$tabber_id = $_GET['holder_id'];
$tab_header = $_GET['header'];
$editable = $_GET['editable'];

// Create tabber
$tabber = new tabControl($editable);

// General External Holder for containers
$holder = $builder->create("div", "", "", "holder");
$builder->append($holder);

// Tab Container
$container = $tabber->get_tabContainer($tab_id, $tab_header, NULL, $nav_id = $tabber_id, $selected = FALSE);
$container['menuItem'] = $builder->import($container['menuItem']);
$container['tabPage'] = $builder->import($container['tabPage']);

// Create menu Item
$menuItem = $builder->create("div", "", "", "menuItem");
$builder->append($menuItem, $container['menuItem']);
$builder->append($holder, $menuItem);

// Create tab page
$tabPage = $builder->create("div", "", "", "tabPage");
$builder->append($tabPage, $container['tabPage']);
$builder->append($holder, $tabPage);

// Return the result
ob_clean();
echo $builder->getHTML();
return;
//#section_end#
?>