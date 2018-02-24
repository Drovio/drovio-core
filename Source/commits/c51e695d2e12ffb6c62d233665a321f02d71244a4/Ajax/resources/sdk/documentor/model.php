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
importer::import("ESS", "Protocol", "server::ServerReport");
importer::import("ESS", "Protocol", "server::HttpResponse");
importer::import("INU", "Developer", "documentation::classDocumentor");

// Use
use \ESS\Protocol\server\ServerReport;
use \ESS\Protocol\server\HttpResponse;
use \INU\Developer\documentation\classDocumentor;


ob_end_clean();
ob_start();

// Set headers
ServerReport::setResponseHeaders(HttpResponse::CONTENT_TEXT_XML);

// Get Model
echo classDocumentor::getModel();
return;
//#section_end#
?>