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
importer::import("API", "Resources", "storage::session"); 
importer::import("API", "Developer", "resources::documentation::documentor");
importer::import("INU", "Developer", "documentation::documentor");

use \API\Resources\storage\session;
use \API\Developer\resources\documentation\documentor as documentParser;
use \INU\Developer\documentation\documentor;


// For HTML report
importer::import("UI", "Html", "DOM");
use \UI\Html\DOM;

DOM::initialize();


$documentor = new documentor();

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	// Get save path to session
	$varNamespace = 'documentor';
	$key = $_POST['key'];
	$filePath = session::get($key, NULL, $varNamespace);
	
	
	if(is_null($filePath))
	{
		die("error");
	}
	
	$documentParser = new documentParser();
	$documentParser->loadFile($filePath, FALSE);
	
	
	
	$newCount = $documentParser->deleteSection($_POST['pos']);
	$saveFlag = $documentParser->updateFile();
	
	$documentor = new documentor();
	if($saveFlag)
		//echo 'OK';
		echo $documentor->getDeleteReport($_POST['__fid'], $_POST['pos'], $newCount, $_POST['id']);
	else	
		echo 'NOT';
	
	return;
}

echo $documentor->getDeleteConfirmation($_GET['pos']);
return;
//#section_end#
?>