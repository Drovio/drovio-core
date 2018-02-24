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
importer::import("ESS", "Environment", "session");
importer::import("ESS", "Environment", "url");
importer::import("API", "Geoloc", "datetimer");
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Presentation", "dataGridList");
importer::import("UI", "Content", "HTMLContent");
importer::import("INU", "Views", "fileExplorer");

use \ESS\Environment\session;
use \ESS\Environment\url;
use \API\Geoloc\datetimer;
use \API\Literals\literal;
use \API\Resources\filesystem\fileManager;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Presentation\dataGridList;
use \UI\Content\HTMLContent;
use \INU\Views\fileExplorer;

// Init variables
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];

DOM::initialize();
$rootPath = fileExplorer::getSessionPath($rootIdentifier);
$readOnly = fileExplorer::isReadOnly($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	// Create HTMLContent
	$htmlc = new HTMLContent();
	$htmlc->build("", "invalid");
	
	// Build invalid content
	$fileViewWrapper = DOM::create("div", "", "", "fileViewerContents");
	$htmlc->append($fileViewWrapper);
	
	// Create invalid message
	$msg = DOM::create("div", "", "", "invalidMessage");
	
	$h2_msg = literal::get("sdk.INU.Views", "lbl_requestError");
	$h2 = DOM::create("h2", $h2_msg);
	DOM::append($msg, $h2);
	
	$p_msg = literal::get("sdk.INU.Views", "msg_invalidRoot");
	$p = DOM::create("p", $p_msg);
	DOM::append($msg, $p);
	
	// Create tile
	$invalidTile = DOM::create("div", $msg, "", "stateViewer");
	if (gettype($state) == "string")
		DOM::data($invalidTile, "state", "invalid_root");
	DOM::append($fileViewWrapper, $invalidTile);
	
	// Return result
	echo $htmlc->getReport("#".$rootIdentifier."_filev");
	return;
}
	
$subPath = str_replace("::", "/", $subPath);

$fexplorer = new fileExplorer($rootPath, $rootIdentifier, null, TRUE, $readOnly);

$fviewer = DOM::create("div", "", "", "fileViewerContents");
$path = $rootPath."/".$subPath."/";
if (!file_exists(systemRoot.$path))
{
	$htmlc = new HTMLContent();
	$msg = literal::get("sdk.INU.Views", "msg_pathNotExists");
	$htmlc->buildElement($fexplorer->getStateTile($msg));
	echo $htmlc->getReport("#".$rootIdentifier."_filev");
	return;
}

$dtGridList = new dataGridList();
$glist = $dtGridList->build("", !$readOnly, $withBorder = FALSE)->get();

$cols = array();
$cols["name"] = 0.6;
$cols["size"] = 0.1;
$cols["type"] = 0.1;
$cols["time"] = 0.2;
$dtGridList->setColumnRatios($cols);

$headers = array();
$headers["name"] = literal::dictionary("name", FALSE);
$headers["size"] = literal::dictionary("size", FALSE);
$headers["type"] = literal::dictionary("type", FALSE);
$headers["time"] = literal::get("sdk.INU.Views", "lbl_modified", NULL, FALSE);
$dtGridList->setHeaders($headers);


// Get file explorer mask names (if any)
$maskItems = fileExplorer::getMaskItems($rootIdentifier);

// List all directories first
$count = 0;
$list = array();
$contents = $fexplorer->getDirectoryContents($subPath);
usort($contents['dirs'], dsort);
foreach ($contents['dirs'] as $details)
{	
	$count++;
	$gridRow = array();
	
	// Name
	$wrap = DOM::create("div");
	$ficon = DOM::create("div", "", "", "previewWrapper previewIcon folderIcon");
	
	// Check for mapping function
	$viewName = $details['name'];
	if (isset($maskItems[$viewName]))
		$viewName = $maskItems[$viewName];
	$fname = DOM::create("span", $viewName, "", "folderName");
	DOM::data($fname, "fxsbp", $details['name']);
	
	DOM::append($wrap, $ficon);
	DOM::append($wrap, $fname);
	
	// Insert row
	$gridRow["name"] = $wrap;
	$gridRow["size"] = "";
	$gridRow["type"] = "folder";
	$gridRow["time"] = date("F j, Y, G:i (T)", $details['lastModified']);
	$dtGridList->insertRow($gridRow, "files[]", FALSE);
	
	// Append name to list
	$list[] = $details['name'];
}

// List all files afterwards
usort($contents['files'], dsort);
foreach ((array)$contents['files'] as $details)
{	
	$count++;
	$gridRow = array();
	
	$nameWrapper = DOM::create("div");
	$fileClass = "fileName";
	// Name
	$a = DOM::create("a");

	$url = "preview.php?ri=".urlencode($rootIdentifier)."&sp=".urlencode($subPath."/")."&fn=".urlencode($details['name']);
	DOM::attr($a, "href", $url);
	DOM::attr($a, "target", "_blank");
	
	$wrap = DOM::create("div", "", "", "previewWrapper");
	
	$type = $fexplorer->getFileType($details['name'], $subPath);
	// Find icon class
	if (!empty($type))
		DOM::appendAttr($wrap, "class", $type."fi");
	// Check if file can have live preview
	if ($fexplorer->isIconified($details['extension']))
		DOM::appendAttr($wrap, "class", "initialize");
		
	// Provide extension to css as element's data
	DOM::data($wrap, "ext", substr($details['extension'], 0, 4));
	$fname = DOM::create("span", $details['name'], "", $fileClass);
	
	DOM::append($a, $fname);
	
	DOM::append($nameWrapper, $wrap);
	DOM::append($nameWrapper, $a);
	
	$gridRow["name"] = $nameWrapper;
	$gridRow["size"] = DOM::create("span", $fexplorer->getFileSize($details['name'], $subPath));
	$gridRow["type"] = (empty($type) ? "file" : $type);
	$gridRow["time"] = datetimer::live($details['lastModified'], "F j, Y, G:i (T)");
	
	$dtGridList->insertRow($gridRow, "files[]", FALSE);
	
	$list[] = $details['name'];
}

DOM::data($fviewer, "fileList", $list);

$dtGridListWrapper = DOM::create("div", "", "", "fileViewerWrapper");
DOM::append($dtGridListWrapper, $glist);

DOM::append($fviewer, $dtGridListWrapper);
$msg = literal::get("sdk.INU.Views", "msg_emptyFolder");
$emptyTile = $fexplorer->getStateTile($msg, "empty");
DOM::append($fviewer, $emptyTile);
if ($count == 0)
	HTML::addClass($dtGridListWrapper, "noDisplay");
else
	HTML::addClass($emptyTile, "noDisplay");


// Create content and return
$htmlc = new HTMLContent();
$htmlc->buildElement($fviewer);
echo $htmlc->getReport("#".$rootIdentifier."_flv");
return;

// Sorting function
function dsort($a, $b) {
    return ($a['name'] > $b['name']);
}
//#section_end#
?>