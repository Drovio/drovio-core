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
importer::import("API", "Developer", "components::sql::dvbLib");
importer::import("API", "Developer", "components::sql::dvbDomain");
importer::import("API", "Developer", "components::sql::dvbQuery");

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

ob_end_clean();
ob_start();
header("Content-Type:text/json");
echo json_encode($queryNames, TRUE);
//#section_end#
?>