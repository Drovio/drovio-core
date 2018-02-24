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
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("UI", "Content", "JSONContent");
importer::import("UI", "Html", "DOM");
importer::import("INU", "Views", "fileExplorer");

use \API\Literals\literal;
use \API\Resources\filesystem\fileManager;
use \UI\Content\JSONContent;
use \UI\Html\DOM;
use \INU\Views\fileExplorer;

DOM::initialize();

// File Explorer ID, used to identify root path
$rootIdentifier = $_GET['fexId'];
$subPath = $_GET['subPath'];
$fileName = $_GET['fn'];

$json = new JSONContent();
$jsonReport = array();
// Get root path from session
$rootPath = fileExplorer::getSessionPath($rootIdentifier);

// No rootPath found in session with given 'rootIdentifier'
if (empty($rootPath))
{
	$jsonReport['status'] = "invalid_path";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_invalidPath", NULL, FALSE);
	echo $json->getReport($jsonReport);
	return;
}

if (!is_string($fileName) || empty($fileName)){
	$jsonReport['status'] = "invalid_filename";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_invalidFilename", NULL, FALSE);
	echo $json->getReport($jsonReport);
	return;
}

$subPath = str_replace("::", "/", $subPath);

if (!file_exists(systemRoot.$rootPath.$subPath)){
	$jsonReport['status'] = "path_not_exists";
	$jsonReport['msg'] = literal::get("sdk.INU.Views", "msg_pathNotExists", NULL, FALSE);
	echo $json->getReport($jsonReport);
	return;
}

$fexplorer = new fileExplorer($rootPath, $rootIdentifier, null, TRUE, FALSE);
$htmlContent = DOM::create("div");

/**
 * Get a DOM representation of a file's contents.
 * 
 * @param	string	$fileName
 * 		Name of the file
 * 
 * @param	string	$subPath
 * 		File's path, relative to the fileExplorer's rootPath.
 * 
 * @return	DOMElement
 * 		The DOM element that represents the contents of a file
 */
$previewFile = function($fileName, $subPath = "") use ($fexplorer, $rootPath, $rootIdentifier)
{
	$wrap = DOM::create("div", "", "", "filePreviewWrapper");
	
	if (empty($rootPath)){
		DOM::append($wrap, $fexplorer->getInvalidRoot());
		return $wrap;
	}
	
	// Preview
	$prev = DOM::create("div", "", "", "fpContentWrapper");
	DOM::append($wrap, $prev);
	
	try
	{
		$element = $fexplorer->getDOMRepresentation($fileName, $subPath);
		if (is_null($element))
		{
			$w = DOM::create("div");
			DOM::innerHTML($w, literal::get("global.notifications.messages.debug", "dbg.content_not_found", NULL, FALSE));
			DOM::append($prev, $w);
		}
		else if($element === FALSE)
		{
			// File type icon
			$iconPrev = DOM::create("div", "", "", "fepw");
			$type = $fexplorer->getFileType($fileName, $subPath);
			// Find icon class
			if (!empty($type))
				DOM::appendAttr($iconPrev, "class", $type."fi");
			$ext = $fexplorer->getFileType($fileName, $subPath, TRUE);
			DOM::attr($iconPrev, "data-ext", substr($ext, 0, 4));
			DOM::attr($iconPrev, "title", $type.(empty($ext) ? "" : " (.".$ext.")"));
			DOM::append($prev, $iconPrev);
		}
		else
			DOM::append($prev, $element);
	}
	catch (Exception $e)
	{
		DOM::append($prev, DOM::create("div", $e->getMessage()));
	}
	
	$p = systemRoot.$rootPath."/".$subPath."/".$fileName;
	
	//Details
	$details = DOM::create("div", "", "", "fpDetailsWrapper soloScrollable");
	DOM::append($wrap, $details);
	
	// File type icon
	$icon = DOM::create("div", "", "", "fepw");
	$type = $fexplorer->getFileType($fileName, $subPath);
	// Find icon class
	if (!empty($type))
		DOM::appendAttr($icon, "class", $type."fi");
	$ext = $fexplorer->getFileType($fileName, $subPath, TRUE);
	DOM::attr($icon, "data-ext", substr($ext, 0, 4));
	DOM::attr($icon, "title", $type.(empty($ext) ? "" : " (.".$ext.")"));
	DOM::append($details, $icon);
	
	// Name
	$nameWrap = DOM::create("span", "", "", "fpDetailsName");
	DOM::append($nameWrap, DOM::create("span", basename($fileName)));
	$rpath = fileExplorer::getRootFriendlyName($rootIdentifier)."/".(trim($subPath, "/ \t\n\r\0\x0B"));
	$pathWrap = DOM::create("span", $rpath);
	DOM::attr($pathWrap, "title", $rpath);
	DOM::append($nameWrap, $pathWrap);
	DOM::append($details, $nameWrap);
	
	$typeLabel = (empty($type) ? "document" : $type);
	
	// File info
	$fInfo = DOM::create("div", "", "", "fpDetailsFile");
	DOM::append($details, $fInfo);
	
	// File type
	$typeWrap = DOM::create("span", "", "", "fpDetailsType");
	DOM::append($typeWrap, DOM::create("span", $typeLabel));
	DOM::append($typeWrap, DOM::create("span", "type"));
	DOM::append($fInfo, $typeWrap);
	
	// File size
	$sizeWrap = DOM::create("span", "", "", "fpDetailsSize");
	DOM::append($sizeWrap, DOM::create("span", fileManager::getSize($p, TRUE)));
	DOM::append($sizeWrap, DOM::create("span", "size"));
	DOM::append($fInfo, $sizeWrap);
	
	// File modification time
	$mtimeWrap = DOM::create("span", "", "", "fpDetailsMTime");
	DOM::append($mtimeWrap, DOM::create("span", date("j/n/Y (H:i)", filemtime($p))));
	DOM::append($mtimeWrap, DOM::create("span", "last modified"));
	DOM::append($fInfo, $mtimeWrap);
	
	// Image size
	$imgInfo = getimagesize($p);
	if (!empty($imgInfo)){
		$iInfo = DOM::create("div", "", "", "fpDetailsImage");
		DOM::append($details, $iInfo);
		
		// Image dimensions
		$dimWrap = DOM::create("span", "", "", "fpDetailsDimensions");
		DOM::append($dimWrap, DOM::create("span", $imgInfo[0]." x ".$imgInfo[1]));
		DOM::append($dimWrap, DOM::create("span", "dimensions"));
		DOM::append($iInfo, $dimWrap);
	
		DOM::attr($prev, "data-imgw", $imgInfo[0]);
		DOM::attr($prev, "data-imgh", $imgInfo[1]);
	}
	
	// X button
	$x = DOM::create("div", "x", "", "fpClose");
	DOM::attr($x, "title", "ESC to close");
	DOM::append($details, $x);
	
	return $wrap;
};

DOM::append($htmlContent, $previewFile($fileName, $subPath));

$jsonReport['status'] = TRUE;
$jsonReport['htmlContent'] = DOM::innerHTML($htmlContent);

ob_clean();
echo $json->getReport($jsonReport);
//#section_end#
?>