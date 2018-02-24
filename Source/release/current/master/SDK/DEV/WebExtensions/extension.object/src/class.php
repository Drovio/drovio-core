<?php
//#section#[header]
// Namespace
namespace DEV\WebExtensions;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	WebExtensions
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Geoloc", "locale");
//importer::import("DEV", "WebExtensions", "components::extSettings");
importer::import("DEV", "WebExtensions", "components::extView");
importer::import("DEV", "WebExtensions", "components::source::sourceLibrary");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Geoloc\locale;
//use \DEV\WebExtensions\components\extSettings;
use \DEV\WebExtensions\components\extView;
use \DEV\WebExtensions\components\source\sourceLibrary;
use \DEV\Version\vcs;
use \DEV\Prototype\sourceMap;
use \DEV\Projects\project;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;

/**
 * Redback Web Extension
 * 
 * This class is responsible for the development of a web extension.
 * Extends the project class.
 * 
 * @version	{empty}
 * @created	April 24, 2014, 12:30 (EEST)
 * @revised	April 24, 2014, 12:30 (EEST)
 */
class extension extends project
{
	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * The source index file name.
	 * 
	 * @type	string
	 */
	const SOURCE_INDEX = "source.xml";
	
	/**
	 * The extension source folder path.
	 * 
	 * @type	string
	 */
	const SOURCE_FOLDER = "/src";
	
	/**
	 * The extension themes folder path.
	 * 
	 * @type	string
	 */
	const THEMES_FOLDER = "/themes";
	
	/**
	 * The extension scripts folder path.
	 * 
	 * @type	string
	 */
	const SCRIPTS_FOLDER = "/scripts";
	
	/**
	 * The extension views folder path.
	 * 
	 * @type	string
	 */
	const VIEWS_FOLDER = "/views";
	
	/**
	 * The source vcs object manager.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the extension.
	 * 
	 * @param	integer	$extID
	 * 		The extension project id.
	 * 
	 * @param	string	$extName
	 * 		The extension unique name.
	 * 
	 * @return	void
	 */
	public function __construct($extID = "", $extName = "")
	{
		// Init project
		parent::__construct($extID, $extName);
		
		// Init vcs
		$this->vcs = new vcs($extID);
		
		// Create app structure (if first time)
		$this->createExtStructure();
	}
	
	/**
	 * Gets the vcs manager object.
	 * 
	 * @return	vcs
	 * 		The vcs manager object.
	 */
	public function getVCS()
	{
		return $this->vcs;
	}
	
	/**
	 * Creates the extension structure.
	 * 
	 * @return	void
	 */
	private function createExtStructure()
	{
		// Check if structure is already there
		if (file_exists(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE))
			return;
		
		// Create index file
		fileManager::create(systemRoot.$this->getRootFolder()."/".self::INDEX_FILE, "", TRUE);
		
		// Create Application Index
		$this->createExtIndex();
		$this->createSourceMap();
		
		// Create Main source library
		$sourceLibrary = new sourceLibrary($this->id);
		$status = $sourceLibrary->create("Main");
		
		// Create Settings File
		//$extSettings = new extSettings($this->id);
		//$extSettings->create();
		
		// Create Main View
		$extView = new extView($this->id);
		$extView->create("MainView");
		
		// Set init settings
		//$extSettings->set("STARTUP_VIEW", "MainView");
		//$extSettings->set("DEFAULT_LOCALE", locale::getDefault());
	}
	
	/**
	 * Creates the extension index file.
	 * 
	 * @return	void
	 */
	private function createExtIndex()
	{
		// Create vcs item
		$itemID = $this->getItemID("extIndex");
		$itemPath = "/";
		$itemName = self::INDEX_FILE;
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $smart = FALSE);
		
		// Create Application index root
		$parser = new DOMParser();
		$root = $parser->create("extension");
		$parser->append($root);
		$parser->save($itemTrunkPath, FALSE);
		
		// Populate index
		$themesElement = $parser->create("themes");
		$parser->append($root, $themesElement);
		
		$scriptsElement = $parser->create("scripts");
		$parser->append($root, $scriptsElement);
		
		$viewsElement = $parser->create("views");
		$parser->append($root, $viewsElement);
		
		$parser->update();
	}
	
	/**
	 * Creates the extension source map index file.
	 * 
	 * @return	void
	 */
	private function createSourceMap()
	{
		// Create vcs item
		$itemID = $this->getItemID("sourceIndex");
		$itemPath = "/";
		$itemName = self::SOURCE_INDEX;
		$mapFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create source map
		$sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		$sourceMap->createMapFile();
	}
	
	/**
	 * Adds an object's index to the extension index file.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$type
	 * 		The object type.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	boolean
	 * 		True on success, false if object already exist.
	 */
	public function addObjectIndex($group, $type, $name)
	{
		// Get vcs item and update
		$itemID = $this->getItemID("extIndex");
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Load application index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Check if item already exists
		$item = $parser->evaluate("//".$group."/".$type."[@name='".$name."']")->item(0);
		if (!is_null($item))
			return FALSE;
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);
		
		// Create object
		$obj = $parser->create($type);
		$parser->attr($obj, "name", $name);
		$parser->append($root, $obj);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Gets all extension themes.
	 * 
	 * @return	array
	 * 		An array of themes by value.
	 */
	public function getThemes()
	{
		return $this->getIndexObjects("themes", "theme");
	}
	
	/**
	 * Gets all extension scripts.
	 * 
	 * @return	array
	 * 		An array of scripts by value.
	 */
	public function getScripts()
	{
		return $this->getIndexObjects("scripts", "script");
	}
	
	/**
	 * Gets all extension views.
	 * 
	 * @return	array
	 * 		An array of views by value.
	 */
	public function getViews()
	{
		return $this->getIndexObjects("views", "view");
	}
	
	/**
	 * Get objects from the extension index.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$name
	 * 		The object tag name.
	 * 
	 * @return	array
	 * 		An array of all object names by group.
	 */
	private function getIndexObjects($group, $name)
	{
		// Get vcs item and load index
		$itemID = $this->getItemID("extIndex");
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);

		// Get all packages
		$objects = array();
		$elements = $parser->evaluate($name, $root);
		foreach ($elements as $elm)
			$objects[] = $parser->attr($elm, "name");
		
		return $objects;
	}
	
	/**
	 * Get the vcs item id.
	 * 
	 * @param	string	$suffix
	 * 		The id suffix.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	public function getItemID($suffix)
	{
		return "ext".md5("ext_".$this->id."_".$suffix);
	}
}
//#section_end#
?>