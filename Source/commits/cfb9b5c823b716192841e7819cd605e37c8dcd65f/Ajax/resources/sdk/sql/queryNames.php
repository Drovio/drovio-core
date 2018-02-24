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
importer::import("API", "Profile", "account");
importer::import("DEV", "Core", "sql/sqlDomain");
importer::import("DEV", "Core", "sql/sqlQuery");
importer::import("UI", "Content", "JSONContent");

use \API\Profile\account;
use \DEV\Core\sql\sqlDomain;
use \DEV\Core\sql\sqlQuery;
use \UI\Content\JSONContent;

// Get domains
$queryNames = array();
if (account::validate())
{
	$domains = sqlDomain::getList(TRUE);
	foreach ($domains as $domain)
	{
		// Get domain queries
		$queries = sqlDomain::getQueries($domain);
		$parsedQueries = array();
		foreach ($queries as $id => $qName)
			$parsedQueries[sqlQuery::getName(str_replace("q.", "", $id))] = $qName;
			
		$queryNames = array_merge($queryNames, $parsedQueries);
	}
}

// Return json content
$json = new JSONContent();
echo $json->getReport($queryNames, $allowOrigin = "", $withCredentials = TRUE, $key = "qNames");
return;
//#section_end#
?>