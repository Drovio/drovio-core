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
importer::import("API", "Model", "modules/module");
importer::import("API", "Resources", "DOMParser");

use \API\Model\modules\module;
use \API\Resources\DOMParser;

// Get Attributes
$groupID = trim($_GET['value']);
$populate = trim($_GET['populate']);

$builder = new DOMParser();

// check id
if (empty($groupID) || (!is_string($populate)))
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

// Get group modules
$groupModules = module::getAllModules($groupID);
$groupModulesRsrc = array();
foreach ($groupModules as $mInfo)
	$groupModulesRsrc[$mInfo['id']] = $mInfo['title'];
asort($groupModulesRsrc);

foreach ($groupModulesRsrc as $key => $value)
{
	$item = $builder->create("item", $value);
	$builder->attr($item, "value", $key);
	$builder->append($receptor, $item);
}

ob_clean();
header("Content-Type:text/xml");
echo $builder->getXML();
//#section_end#
?>