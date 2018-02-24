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
/**
 * @library	BSS
 * @package	Dashboard
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Profile", "team");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");

use \API\Profile\team;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * BOSS Dashboard Application Decorator Manager
 * 
 * This class is responsible for saving the state of the application grid from the BOSS dashboard.
 * 
 * @version	0.1-3
 * @created	April 3, 2015, 11:40 (EEST)
 * @updated	June 9, 2015, 18:16 (EEST)
 */
class appGrid
{
	/**
	 * The appGrid instance.
	 * 
	 * @type	appGrid
	 */
	private static $instance = NULL;
	
	/**
	 * The team's service folder to store the state file.
	 * 
	 * @type	string
	 */
	private $serviceFolder = "";
	
	/**
	 * The parser responsible for parsing the state xml file.
	 * 
	 * @type	DOMParser
	 */
	private $xmlParser;
	
	/**
	 * Initialize the appGrid object.
	 * 
	 * @return	void
	 */
	final protected function __construct()
	{
		// Initialize service folder
		$this->serviceFolder = team::getServicesFolder("BossDashboard", $teamID = "", $systemAppData = TRUE);
		
		// Load file
		$this->loadFile();
	}
	
	/**
	 * Get the appGrid instance.
	 * 
	 * @return	appGrid
	 * 		The appGrid object.
	 */
	public static function getInstance()
	{
		if (empty(self::$instance))
			self::$instance = new appGrid();
		
		return self::$instance;
	}
	
	/**
	 * Get the current state of the application grid.
	 * 
	 * @return	array
	 * 		An array of all applications per slide.
	 * 		For each slide there is an array of all applications and their information, including:
	 * 		- application_id (id)
	 * 		- size (size)
	 * 		- position x (pos_x)
	 * 		- position y (pos_y)
	 */
	public function getState()
	{
		// Initialize current state
		$currentState = array();
		
		// Get all applications for each slide
		$slideElements = $this->xmlParser->evaluate("//slide");
		$slideCounter = 0;
		foreach ($slideElements as $slideElement)
		{
			// Get slide applications
			$slideApps = $parser->evaluate("/app", $slideElement);
			foreach ($slideApps as $sApp)
			{
				$appID = $parser->attr($sApp, "id");
				
				$appInfo = array();
				$appInfo['id'] = $appID;
				$appInfo['size'] = $parser->attr($sApp, "size");
				$appInfo['pos_x'] = $parser->attr($sApp, "pos_x");
				$appInfo['pos_y'] = $parser->attr($sApp, "pos_y");
				
				$currentState[$slideCounter][] = $appInfo;
			}
			
			// Go to next slide
			$slideCounter++;
		}
		
		// Return current state
		return $currentState;
	}
	
	/**
	 * Set the current state of the application grid.
	 * 
	 * @param	array	$gridState
	 * 		An array of all applications per slide.
	 * 		For each slide there is an array of all applications and their information, including:
	 * 		- application_id (id)
	 * 		- size (size)
	 * 		- position x (pos_x)
	 * 		- position y (pos_y)
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setState($gridState = array())
	{
		// Clear all slides
		$root = $this->xmlParser->evaluate("//grid");
		$this->xmlParser->innerHTML($root, "");
		
		// Set state
		foreach ($gridState as $slideNum => $applications)
		{
			// Create slide
			$slideElement = $this->xmlParser->create("slide");
			$this->xmlParser->append($root, $slideElement);
			
			// Add applications
			foreach ($applications as $appInfo)
			{
				// Create application element
				$appElement = $this->xmlParser->create("app", "", $appInfo['id']);
				$this->xmlParser->append($slideElement, $appElement);
				
				// Add application info
				$this->xmlParser->attr($appElement, "size", $appInfo['size']);
				$this->xmlParser->attr($appElement, "pos_x", $appInfo['pos_x']);
				$this->xmlParser->attr($appElement, "pos_y", $appInfo['pos_y']);
			}
		}
		
		// Update file
		return $this->xmlParser->update();
	}
	
	/**
	 * Load the state file.
	 * The file will be created if it doesn't exist.
	 * 
	 * @return	void
	 */
	private function loadFile()
	{
		// Initialize xmlParser
		$this->xmlParser = new DOMParser();
		
		// Load file (or create if doesn't exist)
		try
		{
			$this->xmlParser->load($this->serviceFolder."/grid_state.xml");
		}
		catch (Exception $ex)
		{
			// Create root element
			$root = $this->xmlParser->create("grid");
			$this->xmlParser->append($root);
			
			// Create first empty slide
			$slide = $this->xmlParser->create("slide");
			$this->xmlParser->append($root, $slide);
			
			// Create empty file if not exist and save contents of DOMParser
			fileManager::create(systemRoot.$this->serviceFolder."/grid_state.xml", "", TRUE);
			$this->xmlParser->save(systemRoot.$this->serviceFolder."/grid_state.xml");
		}
	}
}
//#section_end#
?>