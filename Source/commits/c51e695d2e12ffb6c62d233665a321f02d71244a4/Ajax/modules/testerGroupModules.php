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
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;

// Get Attributes
$groupId = trim($_GET['value']);
$populate = trim($_GET['populate']);

$builder = new DOMParser();

// check id
if (empty($groupId) || (!is_string($populate)))
{
	header("Content-Type:text/xml");
	
	$master = $builder->create("master");
	$builder->append($master);
	
	echo $builder->getXML();
	return;
}

$master = $builder->create("master");
$builder->append($master);

$receptor = $builder->create($populate);
$builder->append($master, $receptor);

// Register to Database
$dbc = new interDbConnection();
$dbq = new dbQuery("666615842", "units.modules");
$attr = array();
$attr['gid'] = $groupId;
$moduleGroupRsrc = $dbc->execute($dbq, $attr);

$moduleGroups = $dbc->toArray($moduleGroupRsrc, "id", "title");

foreach ($moduleGroups as $key => $value)
{
	$item = $builder->create("item", $value);
	$builder->attr($item, "value", $key);
	$builder->append($receptor, $item);
}

ob_end_clean();
ob_start();
header("Content-Type:text/xml");
echo $builder->getXML();
//#section_end#
?>