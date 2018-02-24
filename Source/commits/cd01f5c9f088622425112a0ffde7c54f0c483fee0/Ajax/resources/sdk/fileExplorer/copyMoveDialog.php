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
importer::import("ESS", "Protocol", "reports/JSONServerReport");
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
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem/directory");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "treeView");
importer::import("UI", "Presentation", "frames/dialogFrame");
importer::import("INU", "Views", "fileExplorer");

use \API\Literals\literal;
use \API\Resources\filesystem\directory;
use \UI\Html\DOM;
use \UI\Navigation\treeView;
use \UI\Presentation\frames\dialogFrame;
use \INU\Views\fileExplorer;

// File Explorer ID, used to identify root path
$rootIdentifier = engine::getVar('fexId');
$prettyName = engine::getVar('pName');
$subPath = engine::getVar('curSubP');
$copy = engine::getVar('cp') == "1";

DOM::initialize();

// Create dialogFrame
$frame = new dialogFrame();
$title = ($copy ? literal::dictionary("copy", FALSE) : literal::dictionary("move", FALSE))." Files";
$frame->build($title, $action = "/ajax/resources/sdk/fileExplorer/moveFiles.php", $background = FALSE, $type = dialogFrame::TYPE_OK_CANCEL);

// Get form factory
$form = $frame->getFormFactory();

// root identifier
$input = $form->getInput($type = "hidden", $name = "fexId", $value = $rootIdentifier, $class = "", $autofocus = FALSE, $required = FALSE);
$frame->append($input);

// sub path
$input = $form->getInput($type = "hidden", $name = "subPath", $value = $subPath, $class = "", $autofocus = FALSE, $required = FALSE);
$frame->append($input);

// copy or move
$input = $form->getInput($type = "hidden", $name = "copy", $value = ($copy ? 1 : 0), $class = "", $autofocus = FALSE, $required = FALSE);
$frame->append($input);

// Set filenames
$fNames = engine::getVar("fNames");
foreach ($fNames as $fname)
{
	$input = $form->getInput($type = "hidden", $name = "fNames[]", $value = $fname, $class = "", $autofocus = FALSE, $required = FALSE);
	$frame->append($input);
}


// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$frame->append(fileExplorer::getInvalidRoot());
	echo $frame->getFrame();
	return;
}

// Cannot create folder in read only mode
$readOnly = fileExplorer::isReadOnly($rootIdentifier);
if ($readOnly)
{
	// Needs correct message
	$frame->append(DOM::create("div", "read_only"));
	echo $frame->getFrame();
	return;
}

$prettyName = (empty($prettyName) ? "/" : $prettyName);
$subPath = (empty($subPath) ? "" : $subPath);
$subPath = str_replace("::", "/", $subPath);

// Create file explorer
$fexplorer = new fileExplorer($rootPath, $rootIdentifier, $prettyName, TRUE, FALSE);



// Main Body (Folder view)
$folderTreeWrapper = DOM::create("div", "", "", "fepFolderTreeWrapper");
$frame->append($folderTreeWrapper);

// Folder treeview
$treeView = new treeView();
$folderTree = $treeView->build($id = "folderTree", $class = "fepFolderViewer", $sorting = TRUE)->get();
DOM::append($folderTreeWrapper, $folderTree);
// 
$curSubPath = trim(directory::normalize($subPath), " \t\n\r\0\x0B/");

// Insert root folder
$normalizedRoot = directory::normalize(systemRoot.$rootPath);
$dir = "/";
$normalizedDir = directory::normalize($dir);
$subpath = str_replace($normalizedRoot, "", $normalizedDir);
$id = "feftv_".str_replace("/", "_", $normalizedDir);
$itemName = (empty($prettyName) ? basename($rootPath) : $prettyName);

$item = DOM::create("div", "", "", "rItem");
$radioItem = $form->getInput($type = "radio", $name = "fdest", $value = "/".trim($subpath, " /"), $class = "", $autofocus = FALSE, $required = FALSE);
DOM::append($item, $radioItem);
$inputID = DOM::attr($radioItem, "id");
$radioLabel = $form->getLabel(" ".$itemName, $for = $inputID, $class = "");
DOM::append($item, $radioLabel);
$newParent = $treeView->insertSemiExpandableTreeItem($id, $item, $parent, $open = TRUE);
$treeView->assignSortValue($newParent, $name);


// Build the entire tree under the root
buildFolderTree($treeView, $id, systemRoot.$rootPath, $curSubPath, $rootPath, $form);




echo $frame->getFrame();
return;

function buildFolderTree($view, $parent, $path, $currentSubPath, $rootPath, $form)
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
		$itemName = basename($dir);
		$open = (strpos($currentSubPath."/", trim($subpath, " \t\n\r\0\x0B/")."/") === 0 ? TRUE : FALSE);
		
		$item = DOM::create("div", "", "", "rItem");
		$radioItem = $form->getInput($type = "radio", $name = "fdest", $value = "/".trim($subpath, " /"), $class = "", $autofocus = FALSE, $required = FALSE);
		DOM::append($item, $radioItem);
		$inputID = DOM::attr($radioItem, "id");
		$radioLabel = $form->getLabel(" ".$itemName, $for = $inputID, $class = "");
		DOM::append($item, $radioLabel);
		$newParent = $view->insertSemiExpandableTreeItem($id, $item, $parent, $open);
		$view->assignSortValue($newParent, $name);
		
		buildFolderTree($view, $id, $normalizedDir, $currentSubPath, $rootPath, $form);
	}
}
//#section_end#
?>