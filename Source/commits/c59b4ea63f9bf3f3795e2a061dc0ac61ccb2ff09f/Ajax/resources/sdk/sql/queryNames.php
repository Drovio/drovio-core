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

importer::import("ESS", "Protocol", "server::AsCoProtocol");
use \ESS\Protocol\server\AsCoProtocol;

// Ascop Variables
$GLOBALS['__REQUEST'] = $_REQUEST['__REQUEST'];
unset($_REQUEST['__REQUEST']);

AsCoProtocol::set($_REQUEST['__ASCOP']);
unset($_REQUEST['__ASCOP']);
//#section_end#
//#section#[code]
importer::import("ESS", "Protocol", "server::JSONServerReport");
importer::import("API", "Developer", "components::sql::dvbLib");
importer::import("API", "Developer", "components::sql::dvbDomain");
importer::import("API", "Developer", "components::sql::dvbQuery");

use \ESS\Protocol\server\JSONServerReport;
use \API\Developer\components\sql\dvbLib;
use \API\Developer\components\sql\dvbDomain;
use \API\Developer\components\sql\dvbQuery;


// Get domains
$domains = dvbLib::getDomainList(TRUE);

$queryNames = array();
foreach ($domains as $domain)
{
	// Get domain queries
	$queries = dvbDomain::getQueries($domain);
	$parsedQueries = array();
	foreach ($queries as $id => $qName)
		$parsedQueries[dvbQuery::getName(str_replace("q.", "", $id))] = $qName;
		
	$queryNames = array_merge($queryNames, $parsedQueries);
}

// Set Headers
ob_end_clean();
ob_start();
JSONServerReport::setResponseHeaders();
echo json_encode($queryNames, TRUE);
//#section_end#
?>