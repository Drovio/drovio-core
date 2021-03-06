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
importer::import("API", "Developer", "components::ajax::ajaxPage");
importer::import("API", "Resources", "filesystem::directory");

use \API\Developer\components\ajax\ajaxPage;
use \API\Resources\filesystem\directory;

// Get file path
$filePath = $_REQUEST['__AJAX']['path'];
if (empty($filePath))
	return;
	
// Normalize path
$filePath = str_replace("ajax/", "", $filePath);

// Split, get directory and page
$parts = explode("/", $filePath);

// Get page name
$pageName = $parts[count($parts)-1];
$pageName = str_replace(".php", "", $pageName);
unset($parts[count($parts)-1]);

// Normalize directory for developer
if ($parts[0] == "")
	unset($parts[0]);
$pageDirectory = directory::normalize(implode("/", $parts));
$pageDirectory = ($pageDirectory == "/" ? "" : $pageDirectory);

// Clean output
ob_end_clean();

// Initialize ajax page
$page = new ajaxPage($pageName, $pageDirectory);
$page->run();
return;
//#section_end#
?>