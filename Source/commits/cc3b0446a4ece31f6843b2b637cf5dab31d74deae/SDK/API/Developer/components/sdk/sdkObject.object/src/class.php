<?php
//#section#[header]
// Namespace
namespace API\Developer\components\sdk;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::classObject2");
importer::import("API", "Developer", "components::prime::classMap");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Developer", "resources::documentation::documentor");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\components\prime\classObject2;
use \API\Developer\components\prime\classMap;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\projects\project;
use \API\Developer\resources\paths;
use \API\Developer\resources\documentation\documentor as documentParser;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * SDK Object Manager
 * 
 * Handles all the information for SDK Objects.
 * 
 * @version	{empty}
 * @created	March 21, 2013, 13:27 (EET)
 * @revised	April 1, 2014, 16:54 (EEST)
 * 
 * @deprecated	Use \DEV\Core\sdk\sdkObject instead.
 */
class sdkObject extends classObject2
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $classMap;
	/**
	 * Constructor method.
	 * Initializes the object.
	 * 
	 * @param	string	$libName
	 * 		The object's library.
	 * 
	 * @param	string	$packageName
	 * 		The object's package.
	 * 
	 * @param	string	$nsName
	 * 		The object's namespace.
	 * 
	 * @param	string	$objectName
	 * 		The object's name.
	 * 
	 * @return	void
	 */
	public function __construct($libName, $packageName, $nsName = "", $objectName = NULL)
	{
		// Parent Constructor
		$nsName = str_replace("_", "::", $nsName);
		$repository = project::getRepository(1);
		$this->classMap = new classMap($repository, FALSE, "SDK");
		parent::__construct($repository, FALSE, $libName, $packageName, $nsName, $objectName);
	}
	
	/**
	 * Updates the source code of the object.
	 * 
	 * @param	string	$code
	 * 		The new source code.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function updateSourceCode($code = "")
	{
		// Create object (for objects not transferred)
		parent::create($this->name, TRUE, "SDK");
		
		// Get SDK Object Class Header
		$header = $this->buildHeader();
		
		// Update Source Code
		return parent::updateSourceCode($header, $code);
	}
	
	
	/**
	 * MIGRATION
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateSourceDoc($content = "")
	{
		// Create object (for objects not transferred)
		parent::create($this->name, TRUE, "SDK");
		
		return parent::updateSourceDoc($content);
	}
	
	/**
	 * MIGRATION
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateJSCode($code = "")
	{
		// Create object (for objects not transferred)
		parent::create($this->name, TRUE, "SDK");
		
		return parent::updateJSCode($code);
	}
	
	/**
	 * MIGRATION
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateCSSCode($code = "")
	{
		// Create object (for objects not transferred)
		parent::create($this->name, TRUE, "SDK");
		
		return parent::updateCSSCode($code);
	}
	
	/**
	 * MIGRATION
	 * 
	 * @param	{type}	$model
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateCSSModel($model = "")
	{
		// Create object (for objects not transferred)
		parent::create($this->name, TRUE, "SDK");
		
		return parent::updateCSSModel($model);
	}
	
	/**
	 * Build the source's header and returns it.
	 * 
	 * @return	string
	 * 		{description}
	 */
	private function buildHeader()
	{  
		$path = systemRoot.paths::getDevRsrcPath()."/Content/Objects/Headers/private.inc";
		$header = fileManager::get($path);
		return phpParser::unwrap($header);
	}
	
	/**
	 * Runs (includes) the trunk's source code file and returns the include result.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function loadSourceCode()
	{
		return parent::includeSourceCode();
	}
	
	/**
	 * Create a new SDK Object.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @param	string	$title
	 * 		The object's title.
	 * 
	 * @param	boolean	$phpFile
	 * 		Indicator whether this object will have a php source code file
	 * 
	 * @param	boolean	$jsFile
	 * 		Indicator whether this object will have a javascript file
	 * 
	 * @param	boolean	$cssFile
	 * 		Indicator whether this object will have a css file
	 * 
	 * @return	sdkObject
	 * 		{description}
	 */
	public function create($name, $title, $phpFile = TRUE, $jsFile = FALSE, $cssFile = FALSE)
	{
		$this->name = $name;
		$this->title = ($title == "" ? $name : $title);

		// Create Index 
		$proceed = $this->createIndex();
		
		// In case the object already exists, return FALSE
		if (!$proceed)
			return FALSE;

		// Create class Object
		parent::create($this->name, TRUE, "SDK");

		// Initialize Object Files
		$this->initFiles($phpFile, $jsFile, $cssFile);
		
		return $this;
	}
	
	/**
	 * Creates object index entry to developer's map.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	protected function createIndex()
	{
		// Set Library Path and create index
		return $this->classMap->createObject($this->library, $this->package, $this->namespace, $this->name, $this->name);
	}
	
	/**
	 * Export object files to latest.
	 * Inner release.
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Only project manager can deploy.
	 */
	public function export()
	{
	/*
		// Export Source Code
		parent::export(systemRoot.systemSDK);
		
		// Export Documentation
		$headPath = $this->getVCSHeadPath();
		$parser = new DOMParser();
		try
		{
			// Export Class Documentation
			$docFile = $headPath."/".parent::SOURCE_FOLDER."/doc.xml";
			$parser->load($docFile, FALSE);
			$sourceDocObjectPath = systemRoot."/System/Resources/Documentation/SDK/".$this->library."/".$this->package."/".str_replace("::", "/", $this->namespace);
			$context = $parser->getXML();
			$status = fileManager::create($sourceDocObjectPath."/".$this->name.".php.xml", $context, TRUE);
			
			// Export Object Manual
		}
		catch (Exception $ex)
		{
		}
		
		// Export CSS Model
		try
		{
			// Export Model
			$modelFile = $headPath."/".parent::MODEL_FOLDER."/model.xml";
			$parser->load($modelFile, FALSE);
			$sourceModelObjectPath = systemRoot."/System/Resources/Model/SDK/".$this->library."/".$this->package."/".str_replace("::", "/", $this->namespace);
			$context = $parser->getXML();
			$status = fileManager::create($sourceModelObjectPath."/".$this->name.".xml", $context, TRUE);
		}
		catch (Exception $ex)
		{
		}*/
	}
	
	/**
	 * Returns the head's javascript code.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getHeadJSCode()
	{
		$headPath = $this->getVCSHeadPath();
		
		try
		{
			return fileManager::get($headPath."/script.js");
		}
		catch (Exception $ex)
		{
			return "";
		}
	}
	
	/**
	 * Returns the head's css code.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getHeadCSSCode()
	{
		$headPath = $this->getVCSHeadPath();
		
		try
		{
			return fileManager::get($headPath.parent::MODEL_FOLDER."/style.css");
		}
		catch (Exception $ex)
		{
			return "";
		}
	}
	
	/**
	 * Return a path to the objects's manual file. Creates the file if it does not exist.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getManual()
	{
		// Create object (for objects not transferred)
		parent::create($this->name, TRUE, "SDK");
		
		$objectFolder = $this->getVCSTrunkPath();
		
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
}
//#section_end#
?>