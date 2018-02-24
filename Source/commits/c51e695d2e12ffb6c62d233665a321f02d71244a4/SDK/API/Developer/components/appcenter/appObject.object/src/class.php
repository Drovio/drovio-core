<?php
//#section#[header]
// Namespace
namespace API\Developer\components\appcenter;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\appcenter
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::classObject2");
importer::import("API", "Developer", "components::prime::indexing::classIndex");
importer::import("API", "Developer", "components::prime::indexing::packageIndex");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Profile", "tester");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\components\prime\classObject2;
use \API\Developer\components\prime\indexing\classIndex;
use \API\Developer\components\prime\indexing\packageIndex;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Profile\tester;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * AppCenter SDK Object
 * 
 * Manages all SDK Objects of Application center.
 * 
 * @version	{empty}
 * @created	September 19, 2013, 14:00 (EEST)
 * @revised	November 3, 2013, 13:22 (EET)
 */
class appObject extends classObject2
{
	/**
	 * The model folder path.
	 * 
	 * @type	string
	 */
	const MODEL_FOLDER = "model/";
	/**
	 * Constructor method.
	 * Initializes Object.
	 * 
	 * @param	string	$library
	 * 		The object's library.
	 * 
	 * @param	string	$package
	 * 		The object's package.
	 * 
	 * @param	string	$namespace
	 * 		The object's namespace (if any).
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	void
	 */
	public function __construct($library, $package, $namespace = "", $name = NULL)
	{
		// Construct classObject
		parent::__construct(paths::getDevPath()."/Repository/Library/devKit/appCenter/", FALSE, $library, $package, $namespace, $name);
	}
	
	/**
	 * Creates a new application object.
	 * 
	 * @param	string	$name
	 * 		The object's name (unique for the namespace).
	 * 
	 * @param	string	$title
	 * 		The object's title.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure or if an object with the same name already exists.
	 */
	public function create($name, $title = "")
	{
		// Set object title
		$title = (empty($title) ? $name : $title);
		
		// Create index
		$filePath = paths::getDevRsrcPath()."/Mapping/Library/appCenter/".$this->library.".xml";
		$status = classIndex::createIndex($filePath, $this->library, $this->package, $this->namespace, $name, $title);
		if (!$status)
			return FALSE;
		
		// Create class object item
		parent::create($name, TRUE);
		
		return TRUE;
	}
	
	/**
	 * Gets the object's source code.
	 * 
	 * @return	string
	 * 		The class section of the source code.
	 */
	public function getSourceCode()
	{
		return parent::getSourceCode();
	}
	
	/**
	 * Updates the source code of this object.
	 * 
	 * @param	string	$code
	 * 		The class code.
	 * 
	 * @return	mixed
	 * 		Returns TRUE on success or FALSE on failure. It returns a string telling whether there is a syntax error in the php file.
	 */
	public function updateSourceCode($code = "")
	{
		// Get SDK Object Class Header
		$header = $this->buildHeader();
		
		// Update Source Code
		return parent::updateSourceCode($header, $code);
	}
	
	/**
	 * Builds the header of the class code.
	 * 
	 * @return	string
	 * 		The header of the class code.
	 */
	private function buildHeader()
	{  
		$path = systemRoot.paths::getDevRsrcPath()."/Content/Objects/Headers/appCenterPrivate.inc";
		$header = fileManager::get($path);
		return phpParser::unwrap($header);
	}
	
	/**
	 * Loads the object's source code from the trunk.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function loadSourceCode()
	{
		return parent::includeSourceCode();
	}
	
	/**
	 * Gets the object's documentation manual.
	 * 
	 * @return	string
	 * 		The documentation xml.
	 */
	public function export()
	{
		// Get Trunk Path
		$itemFolder = $this->getTrunkPath();
		
		// Export Source Code
		$sourceContent = fileManager::get($itemFolder."/".self::SOURCE_FOLDER."/class.php");
		$sourceObjectPath = systemRoot."/System/Library/devKit/appCenter/".$this->library."/".$this->package."/".str_replace("::", "/", $this->namespace)."/".$this->name.".php";
		fileManager::create($sourceObjectPath, $sourceContent, TRUE);
		
		$parser = new DOMParser();
		try
		{
			// Export Class Documentation
			$docFile = $itemFolder."/".self::SOURCE_FOLDER."/doc.xml";
			$parser->load($docFile, FALSE);
			$sourceDocObjectPath = systemRoot."/System/Resources/Documentation/devKit/appCenter/".$this->library."/".$this->package."/".str_replace("::", "/", $this->namespace);
			$context = $parser->getXML();
			$status = fileManager::create($sourceDocObjectPath."/".$this->name.".php.xml", $context, TRUE);
			
			// Export Object Documentation Index
			packageIndex::addObjectReleaseEntry("/System/Resources/Documentation/devKit/appCenter/", $this->library, $this->package, $this->namespace, $this->name);
			
			// Export Object Manual
		}
		catch (Exception $ex)
		{
		}
		
		try
		{
			// Export Model
			$modelFile = $itemFolder."/".self::MODEL_FOLDER."/model.xml";
			$parser->load($modelFile, FALSE);
			$sourceModelObjectPath = systemRoot."/System/Resources/Model/devKit/appCenter/".$this->libName."/".$this->packageName."/".str_replace("::", "/", $this->namespace);
			$context = $parser->getXML();
			$status = fileManager::create($sourceModelObjectPath."/".$this->name.".xml", $context, TRUE);
		}
		catch (Exception $ex)
		{
		}
		
		// Add Release Package Entry
		$this->addReleasePackageEntry();
		
		return TRUE;
	}
	
	/**
	 * Adds an index entry to release package.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function addReleasePackageEntry()
	{
		// Package Path
		$indexFilePath = "/System/Library/devKit/appCenter/".$this->library."/".$this->package."/";
		
		// Get Library Root Index
		$parser = new DOMParser();
		try
		{
			$parser->load($indexFilePath."index.xml");
		}
		catch (Exception $ex)
		{
			$root = $parser->create("Package");
			$parser->attr($root, "name", $this->package);
			$parser->append($root);
			$parser->save(systemRoot.$indexFilePath, "index.xml", TRUE);
		}
		$root = $parser->evaluate("//Package")->item(0);
		
		// Create Package Entry
		$objectFullName = ($this->namespace == "" ? "" : $this->namespace."::").$this->name;
		$objectEntry = $parser->create("object");
		$parser->attr($objectEntry, "name", $objectFullName);
		$parser->append($root, $objectEntry);
		
		// Update File
		return $parser->update();
	}
}
//#section_end#
?>