<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\sdklib;

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
 * @namespace	\model\units\sdklib
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Developer", "model::units::sdklib::sdkManager");
importer::import("API", "Developer", "content::resources::mapping");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "model::version::vcs");
importer::import("API", "Developer", "content::resources");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\fileManager;
use \API\Content\filesystem\folderManager;
use \API\Developer\model\units\sdklib\sdkManager;
use \API\Developer\content\resources\mapping;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\model\version\vcs;
use \API\Developer\content\resources;

/**
 * SDK Object Manager
 * 
 * Handles all the information for SDK Objects.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 12:58 (EEST)
 * @revised	July 3, 2013, 12:58 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\sdk\sdkObject instead.
 */
class sdkObject extends vcs
{
	/**
	 * The object's file type
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "object";
	/**
	 * The object's source folder path
	 * 
	 * @type	string
	 */
	const SOURCE_FOLDER = "/src";
	/**
	 * The object's model folder path
	 * 
	 * @type	string
	 */
	const MODEL_FOLDER = "/model";
	
	/**
	 * The object's library name
	 * 
	 * @type	string
	 */
	protected $libName;
	/**
	 * The object's package name
	 * 
	 * @type	string
	 */
	protected $packageName;
	/**
	 * The object's namespaces (separated by "::")
	 * 
	 * @type	string
	 */
	protected $nsName;
	
	/**
	 * The object's title
	 * 
	 * @type	string
	 */
	protected $title;
	
	/**
	 * The SDK's production directory
	 * 
	 * @type	string
	 */
	protected $prdDirectory;
	
	/**
	 * Constructor method.
	 * Initializes the object.
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @param	string	$packageName
	 * 		The package name
	 * 
	 * @param	string	$nsName
	 * 		The namespace
	 * 
	 * @param	string	$objectName
	 * 		The object's name
	 * 
	 * @return	void
	 */
	public function __construct($libName, $packageName, $nsName = "", $objectName = NULL)
	{
		// Set VCS Properties
		$this->fileType = self::FILE_TYPE;
		
		// Set Library Properties
		$this->libName = $libName;
		$this->packageName = $packageName;
		$this->nsName = str_replace("_", "::", $nsName);
		
		// Set Object Properties
		$this->name = $objectName;

		if (!is_null($objectName))
			$this->load();
	}
	
	/**
	 * Loads the object's information
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	void
	 */
	public function load($branch = "")
	{
		// Normalize namespace path
		$nsName = str_replace("::", "/", $this->nsName);
		
		// Initialize VCS
		$this->VCS_initialize("/Library/SDK/".$this->libName."/".$this->packageName."/".$nsName."/", $this->name, self::FILE_TYPE);
		
		// Load Index Info
		$this->load_indexInfo($branch);
		
		// TEMP
		// Create Release Branch
		$this->vcsBranch->create("release");
	}
	
	/**
	 * Returns the php source code of the object
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_sourceCode($branch = "")
	{
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		
		try
		{
			$code = fileManager::get_contents($objectFolder.self::SOURCE_FOLDER."/class.php");
			
			// Unwrap php code
			$code = phpParser::unwrap($code);
		}
		catch (Exception $ex)
		{
			$code = phpParser::get_classCode($this->name);
		}
		$sections = phpCoder::sections($code);
		if (isset($sections['class']))
			return $sections['class'];
		
		return $code;
	}
	
	/**
	 * Returns the documentation of the source code of the object in xml format.
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_sourceMan($branch = "")
	{
		// Get Object Folder
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		
		$parser = new DOMParser();
		try
		{
			$parser->load($objectFolder.self::SOURCE_FOLDER."/doc.xml", FALSE);
		}
		catch (Exception $ex)
		{
			$root = $parser->create("manual");
			$parser->append($root);
		}
		
		return $parser->getXML();
	}
	
	/**
	 * Returns the javascript code of the object
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_jsCode($branch = "")
	{
		// Get Object Folder
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		
		try
		{
			// Get Class File contents
			$code = fileManager::get_contents($objectFolder."script.js");
		}
		catch (Exception $ex)
		{
			$code = phpParser::get_comment("Write Your Javascript Code Here", $multi = TRUE);
		}
		
		return $code;
	}
	
	/**
	 * Returns the CSS code of the object
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_modelCSS($branch = "")
	{
		// Get Object Folder
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		
		try
		{
			// Get Class File contents
			$code = fileManager::get_contents($objectFolder."/model/style.css");
		}
		catch (Exception $ex)
		{
			$code = phpParser::get_comment("Write Your CSS Style Rules Here", $multi = TRUE);
		}
		
		return $code;
	}
	
	/**
	 * Create a new SDK Object
	 * 
	 * @param	string	$name
	 * 		The object's name
	 * 
	 * @param	string	$title
	 * 		The object's title
	 * 
	 * @param	boolean	$phpFile
	 * 		Indicator whether this object will have a php source code file
	 * 
	 * @param	boolean	$jsFile
	 * 		Indicator whether this object will have a javascript file
	 * 
	 * @param	boolean	$cssFile
	 * 		Indicator whether this object will have a CSS file
	 * 
	 * @return	sdkObject
	 * 		{description}
	 */
	public function create($name, $title, $phpFile = TRUE, $jsFile = FALSE, $cssFile = FALSE)
	{
		$this->name = $name;
		$this->title = $title;

		// Create Index 
		$proceed = mapping::create_object($this->libName, $this->packageName, $this->nsName, $this->name, $this->description);
		if (!$proceed)
			return FALSE;
		sdkManager::addObjectEntry($this->libName, $this->packageName, $this->nsName, $this->name);

		// Normalize namespace path
		$nsName = str_replace("::", "/", $this->nsName);
		
		// Initialize VCS
		$this->VCS_initialize("/Library/SDK/".$this->libName."/".$this->packageName."/".$nsName."/", $this->name, self::FILE_TYPE);

		// Create Object
		$this->VCS_create_object();
		
		// Update Object
		$this->update();
		
		// Initialize Object Files
		$this->initFiles($phpFile, $jsFile, $cssFile);
		
		return $this;
	}
	
	/**
	 * Delete the entire object and all its resource files
	 * 
	 * @return	sdkObject
	 * 		{description}
	 */
	public static function delete()
	{
		return $this;
	}
	
	/**
	 * Updates the status of the object by creating the necessary folders
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	void
	 */
	public function update($branch = "")
	{
		$builder = new DOMParser();
		
		// Update Index Info
		$newBase = $this->get_indexInfo($builder);
		$this->vcsTrunk->update_indexBase($builder, $newBase, $branch);
		
		// Update Structure (if not exists)
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		
		//_____ Create root folder
		if (!file_exists($objectFolder."/"))
			folderManager::create($objectFolder."/");
		
		//_____ Create src folder
		if (!file_exists($objectFolder.self::SOURCE_FOLDER."/"))
			folderManager::create($objectFolder."/", self::SOURCE_FOLDER);
		
		//_____ Create model folder
		if (!file_exists($objectFolder.self::MODEL_FOLDER."/"))
			folderManager::create($objectFolder."/", self::MODEL_FOLDER);
		
		return $this;
	}
	
	/**
	 * Export object files to latest
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function export()
	{
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		$filePath = $objectFolder.self::SOURCE_FOLDER."/class.php";
		if (!phpParser::check_syntax($filePath))
			return FALSE;
		
		// Object Path
		$sourceCodeObjectPath = systemRoot.systemSDK."/".$this->libName."/".$this->packageName."/".str_replace("::", "/", $this->nsName)."/".$this->name.".php";
		//sdkManager::createLibraryIndex($this->libName);
		// Export Source Code
		$finalCode = $this->wrap_sourceCode($this->get_sourceCode());
		fileManager::create($sourceCodeObjectPath, $finalCode);
		
		return TRUE;
	}
	
	/**
	 * Initialize object files
	 * 
	 * @param	boolean	$phpFile
	 * 		Indicator whether the object has php source code
	 * 
	 * @param	boolean	$jsFile
	 * 		Indicator whether the object has javascript code
	 * 
	 * @param	boolean	$cssFile
	 * 		Indicator whether the object has CSS code
	 * 
	 * @return	void
	 */
	private function initFiles($phpFile = TRUE, $jsFile = FALSE, $cssFile = FALSE)
	{
		// If Object has php class file, update the file
		if ($phpFile)
		{
			// Update Source Code
			$this->update_sourceCode();
			
			// Update Source Code Documentation
			$this->update_sourceMan();
			
			// Export Source Code
			$this->export();
		}
	}
	
	/**
	 * Updates the object's source code
	 * 
	 * @param	string	$code
	 * 		The source code
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function update_sourceCode($code = "", $branch = "")
	{
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		if (!file_exists($objectFolder.self::SOURCE_FOLDER."/"))
			folderManager::create($objectFolder."/", self::SOURCE_FOLDER);
			
		
		if ($code == "")
			$code = phpParser::get_classCode($this->name);
		
		$code = html_entity_decode($code);
		
		$code = str_replace(chr(160), " ", $code);
			
		$finalCode = $this->wrap_sourceCode($code);
		
		$objectFolder = $this->vcsTrunk->get_itemPath();
		fileManager::create($objectFolder.self::SOURCE_FOLDER."/class.php", $finalCode);

		return TRUE;
	}
	
	/**
	 * Wraps the source code with the proper delimiters and inserts the header.
	 * 
	 * @param	string	$code
	 * 		The source code body.
	 * 
	 * @return	void
	 */
	protected function wrap_sourceCode($code)
	{
		$headerCode = $this->buildHeader();
		
		$headerCodeSection = phpCoder::section($headerCode, "header");
		$classCodeSection = phpCoder::section($code, "class");
		
		// Final Complete Source Code
		$completeCode = trim($headerCodeSection.$classCodeSection);
			
		// Wrap php code
		$finalCode = phpParser::wrap($completeCode);
		
		return $finalCode;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function buildHeader()
	{  
		$path = systemRoot.resources::PATH."/Content/Objects/Headers/private.inc";
		$header = fileManager::get_contents($path);
		$header = phpParser::unwrap($header);
		
		// Namespace
		$nsName = $this->libName."\\".$this->packageName.($this->nsName != "" ? "\\".$this->nsName : "");
		$nsName = str_replace("::", "\\", $nsName);
		
		// Form Header
		$headerCode = "";
		$headerCode .= phpParser::get_comment("Namespace", $multi = FALSE)."\n";
		$headerCode .= "namespace $nsName;\n\n";
		$headerCode .= $header;
		
		return $headerCode;
	}
	
	/**
	 * Updates the object's documentation
	 * 
	 * @param	string	$man
	 * 		The documentation in xml format
	 * 
	 * @return	void
	 */
	public function update_sourceMan($man = "")
	{
		// Update Documentation file
		$parser = new DOMParser();
		$objectFolder = $this->vcsTrunk->get_itemPath();
	
		if (!file_exists($objectFolder.self::SOURCE_FOLDER."/doc.xml"))
		{
			// If file doesn't exist, create it
			$root = $parser->create("manual");
			$parser->append($root);
		}
		else
		{
			$parser->load($objectFolder.self::SOURCE_FOLDER."/doc.xml", FALSE);
			$root = $parser->evaluate("//manual")->item(0);
		}
		
		// Set inner HTML
		$parser->innerHTML($root, $man);
		
		// Set namespace
		$class = $parser->evaluate("class", $root)->item(0);
		$parser->attr($class, "namespace", $this->getNamespace());
		
		/*
		$now = time();
		*/
		$now = date("F j, Y, G:i (T)");
		// Set Date Created if needed
		$dateCreated = $parser->evaluate("class/info/datecreated", $root)->item(0);
		if (empty($dateCreated->nodeValue))
			$parser->nodeValue($dateCreated, $now);
		/*
		if (!is_numeric($dateCreated->nodeValue))
		{
			$created = strtotime($dateCreated->nodeValue);
			if ($created !== FALSE && $created != -1)
				$parser->nodeValue($dateCreated, $created);
		}
		*/
		// Set Date Revised
		$dateRevised = $parser->evaluate("class/info/daterevised", $root)->item(0);
		$parser->nodeValue($dateRevised, $now);
		
		// Save manual file
		$parser->save($objectFolder.self::SOURCE_FOLDER."/", "doc.xml", TRUE);
	}
	
	/**
	 * Updates the object's javascript code
	 * 
	 * @param	string	$code
	 * 		The javascript code
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function update_JSCode($code = "", $branch = "")
	{
		// Update Javascript code file
		
		// If code is empty, create an empty Javascript file
		if ($code == "")
			$code = "// Write Your Javascript Code Here";
			
		// Decode
		$code = trim(html_entity_decode($code));
		
		// Clear
		$code = str_replace(chr(160), " ", $code);
		
		// Save javascript file
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		return fileManager::create($objectFolder."/script.js", $code);
	}
	
	/**
	 * Updates the object's CSS
	 * 
	 * @param	string	$code
	 * 		The CSS code
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function update_modelCSS($code = "", $branch = "")
	{
		// Get Object Folder
		$objectFolder = $this->vcsTrunk->get_itemPath($branch);
		//_____ Create model folder
		if (!file_exists($objectFolder.self::MODEL_FOLDER."/"))
			folderManager::create($objectFolder."/", self::MODEL_FOLDER);
			
		// Update CSS Style file
		
		// If code is empty, create an empty CSS file
		if ($code == "")
			$code = "// Write Your CSS Style Code Here";
		
		// Decode
		$code = html_entity_decode($code);
		
		// Clear
		$code = str_replace(chr(160), " ", $code);
		
		// Save javascript file
		$objectFolder = $this->vcsTrunk->get_itemPath();
		return fileManager::create($objectFolder."/model/style.css", $code);
	}
	
	/**
	 * Updates the object's model
	 * 
	 * @param	string	$model
	 * 		The object's model in xml format.
	 * 
	 * @return	void
	 */
	public function update_objModel($model = "")
	{
		// Update Object Model file
		$parser = new DOMParser();
		$objectFolder = $this->vcsTrunk->get_itemPath();
		
		if (!file_exists($objectFolder.self::MODEL_FOLDER."/model.xml"))
		{
			// If file doesn't exist, create it
			$root = $parser->create("model");
			$parser->append($root);
		}
		else
		{
			$parser->load($objectFolder.self::MODEL_FOLDER."/model.xml", FALSE);
			$root = $parser->evaluate("//model");
		}
		
		// Set inner HTML
		$parser->innerHTML($root, $model);
		
		// Save manual file
		$parser->save($objectFolder.self::MODEL_FOLDER."/", "model.xml", TRUE);
	}
	
	/**
	 * {description}
	 * 
	 * @return	{empty}
	 * 		{description}
	 */
	public function generate_sourceMan()
	{
		// Load source code
	}
	
	/**
	 * Get the object's index Base
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser to parse the index file
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function get_indexInfo($builder)
	{
		// Get the current base to update
		$newBase = $this->vcsTrunk->get_base($builder);
		
		// Clear base
		DOMParser::innerHTML($newBase, "");
		
		// Set Object Attributes (Name, Title)
		DOMParser::attr($newBase, "name", $this->name);
		DOMParser::attr($newBase, "title", $this->title);
		
		return $newBase;
	}
	
	/**
	 * Load all the index info
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	void
	 */
	protected function load_indexInfo($branch = "")
	{
		$parser = new DOMParser();
		$base = $this->vcsTrunk->get_base($parser, $branch);
		
		if (is_null($base))
			return FALSE;

		$this->title = $parser->attr($base, "title");
	}
	
	/**
	 * Sets the object's title
	 * 
	 * @param	string	$value
	 * 		The title value
	 * 
	 * @return	void
	 */
	public function set_title($value)
	{
		$this->title = $value;
	}
	/**
	 * Returns the object's title
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function get_title()
	{
		return $this->title;
	}
	
	/**
	 * Returns the object's library
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getLibrary()
	{
		return $this->libName;
	}
	
	/**
	 * Returns the object's package
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getPackage()
	{
		return $this->packageName;
	}
	
	/**
	 * Returns the object's namespace (separated with ::)
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getNamespace()
	{
		return "\\".str_replace("::", "\\", $this->nsName);
	}
}
//#section_end#
?>