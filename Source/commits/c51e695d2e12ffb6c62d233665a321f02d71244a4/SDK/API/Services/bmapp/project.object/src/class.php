<?php
//#section#[header]
// Namespace
namespace API\Services\bmapp;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("API", "Profile", "ServiceManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Profile\ServiceManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;

class project
{
	private $projectID = NULL;
	private $projectFolder = "";
	
	public function __construct($projectID = NULL)
	{
		// Initialize project
		if (!empty($projectID))
		{
			$this->projectID = $projectID;
			$this->init();
		}
	}
	
	private function init()
	{
		// Initialize services
		$services = new ServiceManager("BMaPP");
		$this->projectFolder = $services->getFolder()."/Projects/Project.".$this->projectID;
	}
	
	public function create($projectName)
	{
		// Create project to database
		
		// Init
		$this->projectID = $projectID;
		$this->init();
		
		// Create project folders
		folderManager::create(systemRoot.$this->getFolder());
	}
	
	public function createSection($sectionName)
	{
		// (This will go to a new class, this is temporary)
		// Create section to database
		
		// Create section folder
		folderManager::create(systemRoot.$this->getFolder()."/".$sectionName."/");
	}
	
	public function getFolder()
	{
		return $this->projectFolder;
	}
	
	public function getContents($path)
	{
		return fileManager::get($path);
	}
}
//#section_end#
?>