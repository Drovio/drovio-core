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
use \API\Developer\appcenter\appPlayer;
use \API\Resources\literals\moduleLiteral;
use \API\Security\account;
use \UI\Html\HTMLModulePage;
use \ACL\Platform\importer as ACLImporter;

// Init application player
appPlayer::init();
//#section_end#
?>