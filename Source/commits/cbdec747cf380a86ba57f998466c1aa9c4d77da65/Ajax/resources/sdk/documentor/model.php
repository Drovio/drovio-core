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
// Import
importer::import("ESS", "Protocol", "server::ServerReport");
importer::import("ESS", "Protocol", "server::HttpResponse");
importer::import("DEV", "Documentation", "classDocumentor");

// Use
use \ESS\Protocol\server\ServerReport;
use \ESS\Protocol\server\HttpResponse;
use \DEV\Documentation\classDocumentor;


ob_end_clean();
ob_start();

// Set headers
ServerReport::setResponseHeaders(HttpResponse::CONTENT_TEXT_XML);

// Get Model
echo classDocumentor::getModel();
return;
//#section_end#
?>