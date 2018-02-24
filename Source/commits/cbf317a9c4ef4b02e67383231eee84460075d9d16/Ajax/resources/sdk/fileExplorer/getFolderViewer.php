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

// Set the default request headers
\ESS\Protocol\server\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem::directory");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "treeView");
importer::import("UI", "Forms", "form");
importer::import("UI", "Html", "HTMLContent");
importer::import("UI", "Presentation", "popups::popup");
importer::import("INU", "Views", "fileExplorer");

use \API\Literals\literal;
use \API\Resources\filesystem\directory;
use \UI\Html\DOM;
use \UI\Navigation\treeView;
use \UI\Forms\form;
use \UI\Html\HTMLContent;
use \UI\Presentation\popups\popup;
use \INU\Views\fileExplorer;

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$prettyName = $_GET['pName'];
$subPath = $_GET['curSubP'];
$copy = $_GET['cp'] !== "0";

DOM::initialize();

// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

$pp = new popup();
$pp->background(TRUE);
$pos = array("top" => "center", "left" => "center", "position" => "absolute");
$pp->position($pos);
$pp->parent($rootIdentifier);

$viewWrapper = DOM::create("div", "", "", "folderViewWrapper");

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	DOM::append($viewWrapper, fileExplorer::getInvalidRoot());
	$pp->build($viewWrapper);
	echo $pp->getReport();
	return;
}

$readOnly = fileExplorer::isReadOnly($rootIdentifier);
// Cannot create folder in read only mode
if ($readOnly)
{
	// Needs correct message
	DOM::append($viewWrapper, DOM::create("div", "read_only"));
	$pp->build($viewWrapper);
	echo $pp->getReport();
	return;
}

if (empty($prettyName))
	$prettyName = "/";

if (empty($subPath))
	$subPath = "";
 
$subPath = str_replace("::", "/", $subPath);

function buildFolderTree($view, $parent, $path, $currentSubPath, $rootPath)
{
	$contents = directory::getContentList($path, TRUE);
	if (empty($contents) || (!isset($contents['dirs'])))
		return;
	$contents = $contents['dirs'];
	$normalizedRoot = directory::normalize(systemRoot.$rootPath);
	foreach ((array)$contents as $dir)
	{
		$normalizedDir = directory::normalize($dir);
		$subpath = str_replace($normalizedRoot, "", $normalizedDir);
		$id = "feftv_".str_replace("/", "_", $normalizedDir); 
		$name = basename($dir);
		$open = (strpos($currentSubPath."/", trim($subpath, " \t\n\r\0\x0B/")."/") === 0 ? TRUE : FALSE);
		$newParent = $view->insertSemiExpandableTreeItem($id, DOM::create("div", $name), $parent, $open);
		$view->assignSortValue($newParent, $name);
		DOM::attr($newParent, "subPath", trim($subpath, " /"));
		
		buildFolderTree($view, $id, $normalizedDir, $currentSubPath, $rootPath);
	}
}

$fexplorer = new fileExplorer($rootPath, $rootIdentifier, $prettyName, TRUE, FALSE);

// Header
if ($copy)
{
	$copyTo = DOM::create("h2", literal::dictionary("copy", FALSE), "", "fepHeader copy");
	DOM::append($viewWrapper, $copyTo);
}
else
{
	$moveTo = DOM::create("h2", literal::dictionary("move", FALSE), "", "fepHeader move");
	DOM::append($viewWrapper, $moveTo);
}

// Main Body (Folder view)
$folderTreeWrapper = DOM::create("div", "", "", "fepFolderTreeWrapper");
DOM::append($viewWrapper, $folderTreeWrapper);

// Folder treeview
$treeView = new treeView();
$folderTree = $treeView->build($id = "folderTree", $class = "fepFolderViewer", $sorting = TRUE)->get();
DOM::append($folderTreeWrapper, $folderTree);
// 
$curSubPath = trim(directory::normalize($subPath), " \t\n\r\0\x0B/");

// Scan folders
$name = (empty($prettyName) ? basename($rootPath) : $prettyName);
$id = "feftv_".basename($rootPath);
$rootParent = $treeView->insertSemiExpandableTreeItem($id, DOM::create("div", $name), $parentId = "", TRUE);
$treeView->assignSortValue($rootParent, $name);
DOM::attr($rootParent, "subPath", "");
buildFolderTree($treeView, $id, systemRoot.$rootPath, $curSubPath, $rootPath);

// Controls
$controlsWrapper = DOM::create("div", "", "", "fepControlsWrapper");
DOM::append($viewWrapper, $controlsWrapper);		
// Confirm Button
$confirm = form::button("button", "confirm", "", "confirm", TRUE);
DOM::nodeValue($confirm, literal::dictionary("ok", FALSE));
if ($copy)
	DOM::attr($confirm, "data-copy", TRUE);
DOM::append($controlsWrapper, $confirm);
// Cancel Button
$cancel = form::button("button", "cancel", "", "cancel");
DOM::nodeValue($cancel, literal::dictionary("cancel", FALSE));
DOM::append($controlsWrapper, $cancel);
 
//ob_end_clean();
//ob_start();
$pp->build($viewWrapper);
echo $pp->getReport();
//#section_end#
?>