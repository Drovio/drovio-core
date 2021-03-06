<?php
//#section#[header]
// Namespace
namespace DEV\WebExtensions\components\source;

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
 * @namespace	\components\source
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Prototype", "classObject");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "WebExtensions", "extension");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Version", "vcs");

use \API\Developer\resources\paths;
use \API\Resources\filesystem\fileManager;
use \DEV\Prototype\classObject;
use \DEV\Prototype\sourceMap;
use \DEV\WebExtensions\extension;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Version\vcs;

/**
 * Extension Source Object
 * 
 * Extension Source Object Manager
 * 
 * @version	{empty}
 * @created	May 22, 2014, 19:17 (EEST)
 * @revised	May 23, 2014, 9:42 (EEST)
 */
class sourceObject extends classObject
{
	/**
	 * The extension object.
	 * 
	 * @type	extension
	 */
	private $ext;
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * The source vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the vcs information for the object and constructs the classObject.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The object's namespace (separated by "::" or "_").
	 * 
	 * @param	string	$objectName
	 * 		The object's name.
	 * 
	 * @return	void
	 */
	public function __construct($extID, $library, $package, $namespace = "", $objectName = NULL)
	{
		// Init application
		$this->ext = new extension($extID);
		$this->vcs = new vcs($extID);
		
		// Load source map
		$this->loadSourceMap();
		
		// Parent construct
		$namespace = str_replace("_", "::", $namespace);
		parent::__construct($library, $package, $namespace, $objectName);
	}
	
	/**
	 * Implementation of the abstract function from the parent class.
	 * 
	 * @return	string
	 * 		The full path of the object inside the repository.
	 */
	protected function getObjectFullPath()
	{
		$itemID = $this->getItemID();
		return $this->vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Create a new extension source object.
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
	 * 		Indicator whether this object will have a javascript file.
	 * 
	 * @param	boolean	$cssFile
	 * 		Indicator whether this object will have a css file.
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
		$itemPath = extension::SOURCE_FOLDER."/".$this->getItemPath();
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
		$path = systemRoot.paths::getDevRsrcPath()."/headers/extSourceObject.inc";
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
	 * Initializes the source map object for getting the source information from the source index.
	 * 
	 * @return	object
	 * 		The sourceMap object.
	 */
	private function loadSourceMap()
	{
		if (empty($this->sourceMap))
		{
			// Get source index file path
			$itemID = $this->ext->getItemID("sourceIndex");
			$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
			$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		}
		
		return $this->sourceMap;
	}
	
	/**
	 * Updates the source map index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Get source index file path
		$itemID = $this->ext->getItemID("sourceIndex");
		$this->vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>