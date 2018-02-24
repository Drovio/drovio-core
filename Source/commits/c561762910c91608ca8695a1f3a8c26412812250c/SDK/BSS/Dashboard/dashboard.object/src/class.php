<?php
//#section#[header]
// Namespace
namespace BSS\Dashboard;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Profile\team;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

class dashboard
{
	private static $instance = NULL;
	private $serviceFolder = "";
	private $dom_parser;
	private $gridCols = 8;
	private $gridRows = 4;
	
	protected function __construct()
	{
		$this->serviceFolder = team::getServicesFolder("BossDashboard");
		$this->dom_parser = new DOMParser();
		try
		{
			// Load the dashboard file
			$this->dom_parser->load($this->serviceFolder."/dashboard.xml");
		}
		catch (Exception $ex)
		{
			$this->createFile();
		}
	}
	
	public static function getInstance()
	{
		if (empty(self::$instance))
			self::$instance = new dashboard();
		
		return self::$instance;
	}
	
	public function getSlidesCount()
	{
		$slides = $this->dom_parser->evaluate("//slide");
		return $slides->length;
	}
	
	public function getApplications($slide = "")
	{
		$parser = $this->dom_parser;
		$dbApps = array();
		
		// Get all applications for each slide
		$slideElements = $parser->evaluate("//slide");
		$slideCounter = 0;
		foreach ($slideElements as $slideElement)
		{
			// Check if there is a specific request for a slide
			if (!empty($slide) && $slide != $slideCounter)
				continue;
			
			// Get slide applications
			$slideApps = $parser->evaluate("/app", $slideElement);
			foreach ($slideApps as $sApp)
			{
				$appID = $parser->attr($sApp, "id");
				
				$appInfo = array();
				$appInfo['id'] = $appID;
				$appInfo['size'] = $parser->attr($sApp, "size");
				$appInfo['x'] = $parser->attr($sApp, "x");
				$appInfo['y'] = $parser->attr($sApp, "y");
				$appInfo['slide'] = $slideCounter;
				
				$dbApps[$appID] = $appInfo;
			}
			$slideCounter++;
		}
		
		return $dbApps;
	}
	
	public function addApplication($appID)
	{
		$parser = $this->dom_parser;
		
		// Get empty space from all slides
		$slidesCount = $this->getSlidesCount();
		for ($i=0; $i<$slidesCount; $i++)
		{
			$emptyPositions = $this->getSlidesEmptyPositions();
			if (!empty($emptyPositions))
			{
				// Get first position
				$position = $emptyPositions[0];
				
				// Create app element
				$appElement = $parser->create("app", "", $appID);
				$parser->attr($appElement, "size", 1);
				$parser->attr($appElement, "x", $position['x']);
				$parser->attr($appElement, "y", $position['y']);
				$parser->attr($appElement, "slide", $i);
				
				// Get slide element and append application
				$slideElement = $parser->evaluate("//slide")->item($i);
				$parser->append($slideElement, $appElement);
				$parser->update();
				
				// Return position of new application
				return $position;
			}
		}
		
		// Add new slide and add application there
		$this->addSlide();
		return $this->addApplication($appID);
	}
	
	public function setApplicationSize($appID, $size = 1)
	{
	}
	
	public function moveApplication($appID, $posX = 0, $posY = 0)
	{
	}
	
	private function addSlide()
	{
		$parser = $this->dom_parser;
		
		// Get grid root
		$root = $parser->evaluate("/grid")->item(0);
		
		// Create new slide
		$slideElement = $parser->create("slide");
		$parser->append($root, $slideElement);
		
		// Update file
		$parser->update();
	}
	
	private function getSlidesEmptyPositions()
	{
		$slidesCount = $this->getSlidesCount();
		$slidePositions = array();
		for ($i=0; $i<$slidesCount; $i++)
		{
			// Initialize positions
			$positions = array();
			for ($i=0; $i<$this->gridCols; $i++)
				for ($j=0; $j<$this->gridRows; $j++)
					$positions[$i][$j] = 1;
			
			// Get slide applications
			$sApps = $this->getApplications($i);
			foreach ($sApps as $sApp)
				unset($positions[$sApp['posX']][$sApp['posY']]);
			
			$slidePositions[$i] = $positions;
		}
		
		// Set empty positions as a list of all empty positions
		$emptyPositions = array();
		foreach ($slidePositions as $posX => $positions)
			foreach ($positions as $posY => $nothing)
			{
				$posInfo = array();
				$posInfo['x'] = $posX;
				$posInfo['y'] = $posY;
				
				$emptyPositions[] = $posInfo;
			}
		
		return $emptyPositions;
	}
	
	private function createFile()
	{
		$parser = $this->dom_parser;
		
		// Create root element
		$root = $parser->create("grid");
		$parser->append($root);
		
		// Create first empty slide
		$slide = $parser->create("slide");
		$parser->append($root, $slide);
		
		// Create empty file if not exist and save contents of DOMParser
		fileManager::create(systemRoot.$this->serviceFolder."/dashboard.xml", "", TRUE);
		$parser->save(systemRoot.$this->serviceFolder."/dashboard.xml");
	}
}
//#section_end#
?>