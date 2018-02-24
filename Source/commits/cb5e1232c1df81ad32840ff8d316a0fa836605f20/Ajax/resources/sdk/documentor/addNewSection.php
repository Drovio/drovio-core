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
importer::import("UI", "Html", "DOM");
importer::import("INU", "Developer", "documentation::documentor");

use \UI\Html\DOM;
use \INU\Developer\documentation\documentor;

DOM::initialize();

$documentor = new documentor();

echo $documentor->getPresenter($_GET['type'], $_GET['fid']);
//#section_end#
?>