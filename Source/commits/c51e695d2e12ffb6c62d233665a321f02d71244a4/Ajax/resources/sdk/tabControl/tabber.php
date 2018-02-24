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
echo $builder->getHTML();
//#section_end#
?>