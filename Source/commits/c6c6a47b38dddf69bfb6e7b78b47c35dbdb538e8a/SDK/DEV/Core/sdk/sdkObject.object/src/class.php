<?php
//#section#[header]
// Namespace
namespace DEV\Core\sdk;

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
 * @package	Core
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Developer", "resources::documentation::documentor");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Prototype", "classObject");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Core", "sdk::sdkLibrary");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Version", "vcs");

use \API\Developer\resources\paths;
use \API\Developer\resources\documentation\documentor as documentParser;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \DEV\Prototype\classObject;
use \DEV\Prototype\sourceMap;
use \DEV\Core\sdk\sdkLibrary;
use \DEV\Projects\project;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Version\vcs;

/**
 * Core SDK Object Manager
 * 
 * Handles all the information for SDK Objects.
 * 
 * @version	{empty}
 * @created	April 1, 2014, 16:29 (EEST)
 * @revised	April 7, 2014, 23:56 (EEST)
 */
class sdkObject extends classObject
{
	/**
	 * The developer's project.
	 * 
	 * @type	project
	 */
	private static $project;
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Constructor method. Initializes the vcs information for the object and constructs the classObject.
	 * 
	 * @param	string	$library
	 * 		The object's library.
	 * 
	 * @param	string	$package
	 * 		The object's package.
	 * 
	 * @param	string	$namespace
	 * 		The object's namespace (separated by "::" or "_").
	 * 
	 * @param	string	$objectName
	 * 		The object's name.
	 * 
	 * @return	void
	 */
	public function __construct($library, $package, $namespace = "", $objectName = NULL)
	{
		// Initialize new vcs
		if (empty($project))
			self::$project = new project(1);
		
		$repository = self::$project->getRepository();
		$this->vcs = new vcs($repository);
		
		// Get map file trunk path
		$itemID = sdkLibrary::getMapfileID();
		$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
		$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		
		// Parent construct
		$namespace = str_replace("_", "::", $namespace);
		parent::__construct($library, $package, $namespace, $objectName);
	}
	
	/**
	 * Implementation of the abstract function from the parent class.
	 * Returns the full path of the object inside the repository.
	 * 
	 * @return	string
	 * 		The object's full path.
	 */
	protected function getObjectFullPath()
	{
		$itemID = $this->getItemID();
		return $this->vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Create a new Core SDK Object.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @param	string	$title
	 * 		The object's title.
	 * 
	 * @param	boolean	$phpFile
	 * 		Indicator whether this object will have a php source code file.
	 * 
	 * @param	boolean	$jsFile
	 * 		Indicator whether this object will have a javascript file
	 * 
	 * @param	boolean	$cssFile
	 * 		Indicator whether this object will have a css file
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $title, $phpFile = TRUE, $jsFile = FALSE, $cssFile = FALSE)
	{
		// Set vars
		$this->name = $name;
		$this->title = ($title == "" ? $name : $title);
		
		// Create index
		$proceed = $this->sourceMap->createObject($this->library, $this->package, $this->namespace, $this->name, $this->title);
		if (!$proceed)
			return FALSE;
		
		// Update map file vcs item
		$this->updateMapFile();
		
		// Create vcs item
		$itemID = $this->getItemID();
		$itemPath = "/SDK/".$this->getItemPath();
		$itemName = $this->getItemFullname();
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create classObject
		parent::create($name);
		
		// Init files
		$this->initFiles($phpFile, $jsFile, $cssFile);
		
		return TRUE;
	}
	
	/**
	 * Gets the id of the current vcs item.
	 * 
	 * @return	string
	 * 		The item's id.
	 */
	protected function getItemID()
	{
		return "cl".hash("md5", $this->library."_".$this->package."_".$this->namespace."_".$this->name);
	}
	
	/**
	 * Updates the source code of the object.
	 * 
	 * @param	string	$code
	 * 		The new source code.
	 * 
	 * @return	mixed
	 * 		Returns TRUE on success or FALSE on failure.
	 * 		Returns a string telling whether there is a syntax error in the php file.
	 */
	public function updateSourceCode($code = "")
	{
		// Create object (for objects not transferred)
		$this->createVcsItem($this->name);
		
		// Update vcs item
		$this->updateItem();
		
		// Get SDK Object Class Header
		$header = $this->buildHeader();
		
		// Update Source Code
		return parent::updateSourceCode($header, $code);
	}
	
	
	/**
	 * Updates the source documentation of the object.
	 * 
	 * @param	string	$content
	 * 		The documentation in xml format.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateSourceDoc($content = "")
	{
		// Create object (for objects not transferred)
		$this->createVcsItem($this->name);
		
		// Update vcs item
		$this->updateItem();
		
		return parent::updateSourceDoc($content);
	}
	
	/**
	 * Updates the javascript code of the object.
	 * 
	 * @param	string	$code
	 * 		The new javascript code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateJSCode($code = "")
	{
		// Create object (for objects not transferred)
		$this->createVcsItem($this->name);
		
		// Update vcs item
		$this->updateItem();
		
		return parent::updateJSCode($code);
	}
	
	/**
	 * Updates the css code of the object.
	 * 
	 * @param	string	$code
	 * 		The new css code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCSSCode($code = "")
	{
		// Create object (for objects not transferred)
		$this->createVcsItem($this->name);
		
		// Update vcs item
		$this->updateItem();
		
		return parent::updateCSSCode($code);
	}
	
	/**
	 * Updates the css model of the object.
	 * 
	 * @param	string	$model
	 * 		The new css model in html format.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCSSModel($model = "")
	{
		// Create object (for objects not transferred)
		$this->createVcsItem($this->name);
		
		// Update vcs item
		$this->updateItem();
		
		return parent::updateCSSModel($model);
	}
	
	/**
	 * Build the source's header and returns it.
	 * 
	 * @return	string
	 * 		The object's header code.
	 */
	private function buildHeader()
	{  
		$path = systemRoot.paths::getDevRsrcPath()."/headers/sdkObject.inc";
		$header = fileManager::get($path);
		return phpParser::unwrap($header);
	}
	
	/**
	 * Runs (includes) the trunk's source code file and returns the include result.
	 * 
	 * @return	void
	 */
	public function loadSourceCode()
	{
		return parent::includeSourceCode();
	}
	
	/**
	 * Update the item in the working index of the vcs.
	 * 
	 * @param	boolean	$forceCommit
	 * 		Tells whether the object will be included in the next commit.
	 * 
	 * @return	void
	 */
	public function updateItem($forceCommit = FALSE)
	{
		// Get item ID
		$itemID = $this->getItemID();

		// Update item working index
		$this->vcs->updateItem($itemID, $forceCommit);
	}
	
	/**
	 * Return a path to the objects's manual file. Creates the file if it does not exist.
	 * 
	 * @return	void
	 */
	public function getManual()
	{
		// Create object (for objects not transferred)
		$this->createVcsItem($this->name);
		$objectFolder = $this->getObjectFullPath();
		
		// Check for object folder existance
		// Due to repository changes the correct structure may be created after the fist save
		if (!file_exists($objectFolder) and !is_dir($objectFolder))
		{
			if (!file_exists($objectFolder."/manual.xml"))
			{
			/*
				$documentParser = new documentParser();
				$documentParser->create($this->library, $this->package, $this->namespace, $this->name);
				
				$manual = $documentParser->getDoc();
				$parser = new DOMParser();		
				$parser->loadContent($manual, "XML");
				$status = $parser->save($objectFolder."/manual.xml", '', TRUE);
			*/
			}
		}
		else
		{
			return '';
		}
		
		return $objectFolder."/manual.xml";
	}
	
	/**
	 * Init all object's files.
	 * 
	 * @param	boolean	$phpFile
	 * 		Indicator whether to build php source code file.
	 * 
	 * @param	boolean	$jsFile
	 * 		Indicator whether to build javascript file.
	 * 
	 * @param	boolean	$cssFile
	 * 		Indicator whether to build css file.
	 * 
	 * @return	void
	 */
	private function initFiles($phpFile = TRUE, $jsFile = FALSE, $cssFile = FALSE)
	{
		// If Object has php class file, update the file
		if ($phpFile)
		{
			// Update Source Code
			$this->updateSourceCode();
			
			// Update Source Code Documentation
			$this->updateSourceDoc();
		}
	}
	
	/**
	 * Gets the item's path (with library, package, namespace etc) for the vcs.
	 * 
	 * @return	string
	 * 		The item's path.
	 */
	private function getItemPath()
	{
		$namespace = str_replace("::", "/", $this->namespace);
		return "/".$this->library."/".$this->package."/".$namespace."/";
	}
	
	/**
	 * Only for migration.
	 * It creates the item in the vcs (in case there is in the map) and it is used only for the old objects not transferred in the new repository.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	void
	 */
	private function createVcsItem($name)
	{
		// Create vcs item
		$itemID = $this->getItemID();
		$itemPath = "/SDK/".$this->getItemPath();
		$itemName = $this->getItemFullname();
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		if ($itemTrunkPath && !is_dir($itemTrunkPath))
			parent::create($name);
	}
	
	/**
	 * Updates the source map index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Update map file
		$itemID = sdkLibrary::getMapfileID();
		$this->vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>