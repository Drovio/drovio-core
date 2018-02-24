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
importer::import("SYS", "Resources", "url");
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Content", "JSONContent");
importer::import("INU", "Views", "fileExplorer");

use \SYS\Resources\url;
use \API\Literals\literal;
use \API\Resources\filesystem\fileManager;
use \UI\Html\DOM;
use \UI\Content\JSONContent;
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
 * Prepares an icon for a file
 * 
 * @param	string	$fileName
 * 		Name of the file
 * 
 * @param	string	$subPath
 * 		SubPath to the file, relative to the fileExplorer's root path.
 * 
 * @return	DOMElement
 * 		The dom element that holds the icon
 */
$iconifyFile = function($fileName, $subPath = "") use ($rootPath, $fexplorer)
{ 
	$wrap = DOM::create("div", "", "", "previewWrapper");
	if (empty($rootPath)){
		DOM::appendAttr($wrap, "class", "brokenIcon");
		return $wrap;
	}
	
	$path = systemRoot.$rootPath."/".$subPath."/".$fileName;
	
	$contents = fileManager::get($path);
	
	// On content not found, return NULL
	if ($contents === FALSE) {
		DOM::appendAttr($wrap, "class", "brokenIcon");
		return $wrap;
	}
	
	$extension = pathinfo($path, PATHINFO_EXTENSION);	
	if ($extension == "svg")
	{
		$embed = DOM::create("embed", "", "", "previewIcon");			
		DOM::attr($embed, "src", Url::resource($url = $rootPath."/".$subPath."/".$fileName));
		DOM::attr($embed, "type", "image/svg+xml");
		DOM::append($wrap, $embed);
	}
	else if ($fexplorer->isImage($extension))
	{
		$img = DOM::create("img", "", "", "previewIcon");
			
		$imgInfo = getimagesize(systemRoot.$rootPath."/".$subPath."/".$fileName);
		if (filesize(systemRoot.$rootPath."/".$subPath."/".$fileName) > 20000
			&& is_array($imgInfo) && $imgInfo[0] != 0)
		{
			$w = $imgInfo[0];
			$h = $imgInfo[1];
			$resampled = imagecreatetruecolor(32, 32);
			imagesavealpha($resampled, TRUE);
			imagealphablending($resampled, FALSE);
			$alpha = imagecolorallocatealpha($resampled, 0, 0, 0, 0);
			imagefill($resampled, 0, 0, $alpha);
			$original = imagecreatefromstring($contents);
			imagecopyresampled($resampled, $original, 0, 0, 0, 0, 32, 32, $w, $h);
			ob_start();
			imagepng($resampled);
			$contents = ob_get_contents();
			ob_end_clean();
			
			imagedestroy($resampled);
			$mimeType = "image/png";
		}
		
		$base64 = $fexplorer->getBase64Representation($contents);
		
		DOM::attr($img, "src", $base64['src']); 
		DOM::append($wrap, $img);
	}
	else
	{ 
		DOM::appendAttr($wrap, "class", "unsupportedType");
	}
	
	return $wrap;
};

DOM::append($htmlContent, $iconifyFile($fileName, $subPath));

$jsonReport['status'] = TRUE;
$jsonReport['htmlContent'] = DOM::innerHTML($htmlContent);

ob_clean();
echo $json->getReport($jsonReport);
//#section_end#
?>