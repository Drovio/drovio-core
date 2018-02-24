<?php
//#section#[header]
// Namespace
namespace DEV\Prototype;

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
 * @package	Prototype
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Documentation", "classDocumentor");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Documentation\classDocumentor;

/**
 * Abstract Class Object Class
 * 
 * Manages a class smart object, including css (xml model + css code), javascript, documentation and manual.
 * 
 * @version	{empty}
 * @created	March 31, 2014, 13:48 (EEST)
 * @revised	July 8, 2014, 16:11 (EEST)
 */
abstract class classObject
{
	/**
	 * The item extension.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "object";
	/**
	 * The source folder name.
	 * 
	 * @type	string
	 */
	const SOURCE_FOLDER = "src/";
	
	/**
	 * The model folder name.
	 * 
	 * @type	string
	 */
	const MODEL_FOLDER = "model/";
	
	/**
	 * The library name.
	 * 
	 * @type	string
	 */
	protected $library = "";
	/**
	 * The package name.
	 * 
	 * @type	string
	 */
	protected $package = "";
	/**
	 * The namespace name.
	 * 
	 * @type	string
	 */
	protected $namespace = "";
	/**
	 * The object's name.
	 * 
	 * @type	string
	 */
	protected $name = NULL;
	
	/**
	 * Initializes the class object.
	 * 
	 * @param	string	$library
	 * 		The object's library.
	 * 
	 * @param	string	$package
	 * 		The object's packge.
	 * 
	 * @param	string	$namespace
	 * 		The object's namespace.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 		Leave empty for new objects and then call create().
	 * 
	 * @return	void
	 */
	public function __construct($library, $package, $namespace = "", $name = NULL)
	{
		// Init variables
		$this->library = $library;
		$this->package = $package;
		$this->namespace = $namespace;
		$this->name = $name;
	}
	
	/**
	 * Abstract function for getting the object's full path from the inherited class.
	 * 
	 * @return	string
	 * 		The object's full path.
	 */
	abstract protected function getObjectFullPath();
	
	/**
	 * Create a new class object.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name)
	{
		$this->name = $name;
		$objectFolder = $this->getObjectFullPath();
		
		// If the item doesn't exist, proceed
		if ($objectFolder && !is_dir($objectFolder))
		{
			// Create folders
			folderManager::create($objectFolder);
			folderManager::create($objectFolder, self::SOURCE_FOLDER);
			folderManager::create($objectFolder, self::MODEL_FOLDER);
			
			// Update Source Code
			$this->updateSourceCode();
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Updates the source code of this object.
	 * 
	 * @param	string	$header
	 * 		The header code of the object.
	 * 
	 * @param	string	$code
	 * 		The code of the object's class.
	 * 
	 * @return	mixed
	 * 		Returns TRUE on success or FALSE on failure.
	 * 		Returns a string telling whether there is a syntax error in the php file.
	 */
	public function updateSourceCode($header = "", $code = "")
	{
		// Get Item trunk folder
		$itemFolder = $this->getObjectFullPath();

		// Create default code (if empty)
		if ($code == "")
			$code = phpParser::getClassCode($this->name);
		
		// Clear Code
		$code = phpParser::clear($code);
		
		// Form Header
		$headerCode = "";
		$headerCode .= phpParser::comment("Namespace")."\n";
		$headerCode .= "namespace ".$this->getNamespace(TRUE).";\n\n";
		$headerCode .= $header;
		
		// Build full source code
		$finalCode = $this->buildSourceCode($headerCode, $code);
		
		// Create temp file to check syntax
		$tempFile = $itemFolder.self::SOURCE_FOLDER."/class.php.temp";
		fileManager::create($tempFile, $finalCode, TRUE);
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);

		// Remove temp file
		fileManager::remove($tempFile);
		
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;
		
		// Create file
		return (fileManager::create($itemFolder.self::SOURCE_FOLDER."/class.php", $finalCode) !== FALSE);
	}
	
	/**
	 * Builds the source code in sections for easy parsing.
	 * 
	 * @param	string	$header
	 * 		The header code.
	 * 
	 * @param	string	$classCode
	 * 		The class code.
	 * 
	 * @return	string
	 * 		The full source code in php format (including php tags).
	 */
	public function buildSourceCode($header, $classCode)
	{
		// Build Sections
		$headerCodeSection = phpCoder::section($header, "header");
		$classCodeSection = phpCoder::section($classCode, "class");
		
		// Merge all pieces
		$completeCode = trim($headerCodeSection.$classCodeSection);
			
		// Complete php code
		return phpParser::wrap($completeCode);
	}
	
	/**
	 * Gets the object's source code.
	 * 
	 * @param	boolean	$full
	 * 		If true, returns the entire php code without unwrap and un-section it.
	 * 		Otherwise, it returns only the class section.
	 * 
	 * @return	string
	 * 		Returns the class code section of the object's source code.
	 */
	public function getSourceCode($full = FALSE)
	{
		// Get Item trunk folder
		$itemFolder = $this->getObjectFullPath();

		// Get Code
		$code = fileManager::get($itemFolder.self::SOURCE_FOLDER."/class.php");
		
		if (!$full)
		{
			// Unwrap php code
			$code = phpParser::unwrap($code);
			$sections = phpCoder::sections($code);
			return $sections['class'];
		}
		else
			return $code;
	}
	
	/**
	 * Includes the object's source code file.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function includeSourceCode()
	{
		// Get Item trunk folder
		$itemFolder = $this->getObjectFullPath();

		// Import
		return importer::incl($itemFolder.self::SOURCE_FOLDER."/class.php", FALSE, TRUE);
	}
	
	/**
	 * Updates the documentation of the object's source code.
	 * 
	 * @param	string	$content
	 * 		The documentation content in string format.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function updateSourceDoc($content = "")
	{
		// Get Item trunk folder
		$itemFolder = $this->getObjectFullPath();
		
		$filepath = $itemFolder.self::SOURCE_FOLDER."/doc.xml";
		$classDocumentor = new classDocumentor();
		try
		{
			$classDocumentor->loadFile($filepath, FALSE);
		}
		catch (Exception $ex)
		{
			$classDocumentor->create($this->library, $this->package, $this->namespace);
		}
		
		// Temporary For updating manual files
		$classDocumentor->structUpdate($this->library, $this->package, $this->namespace);
		$classDocumentor->update($this->name, $content);
		
		$manual = $classDocumentor->getDoc();
		$parser = new DOMParser();		
		$parser->loadContent($manual, "XML");
		return $parser->save($filepath, '', TRUE);
	}
	
	/**
	 * Gets the object's documentation.
	 * 
	 * @return	string
	 * 		The object's documentation in XML format.
	 */
	public function getSourceDoc()
	{
		// Get Item trunk folder
		$itemFolder = $this->getObjectFullPath();
		
		// Parse File
		$parser = new DOMParser();
		$filepath = $itemFolder.self::SOURCE_FOLDER."/doc.xml";
		try
		{
			$parser->load($filepath, FALSE);
		}
		catch (Exception $ex)
		{
			$classDocumentor = new classDocumentor();
			$classDocumentor->create($this->library, $this->package, $this->namespace);
			
			$manual = $classDocumentor->getDoc();				
			$parser->loadContent($manual, "XML");
			$parser->save($filepath, "", TRUE);
		}
		
		return $parser->getXML();
	}
	
	/**
	 * Gets the object's javascript code.
	 * 
	 * @return	string
	 * 		The object's javascript code.
	 */
	public function getJSCode()
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		
		try
		{
			// Get Class File contents
			$code = fileManager::get($itemFolder."script.js");
		}
		catch (Exception $ex)
		{
			// Create empty Javascript File
			$code = phpParser::comment("Write Your Javascript Code Here", $multi = TRUE);
		}
		
		return $code;
	}
	
	/**
	 * Updates the object's javascript code
	 * 
	 * @param	string	$code
	 * 		The new javascript code.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function updateJSCode($code = "")
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		
		// If code is empty, create an empty Javascript file
		if ($code == "")
			$code = phpParser::comment("Write Your Javascript Code Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clear($code);
		
		// Save javascript file
		return fileManager::create($itemFolder."/script.js", $code);
	}
	
	/**
	 * Loads the object's javascript code into the output buffer.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function loadJSCode()
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		
		// Import
		return importer::incl($itemFolder."script.js", FALSE, FALSE);
	}
	
	/**
	 * Gets the object's css code.
	 * 
	 * @return	string
	 * 		The object's css code.
	 */
	public function getCSSCode()
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		
		try
		{
			// Get Class File contents
			$code = fileManager::get($itemFolder.self::MODEL_FOLDER."/style.css");
		}
		catch (Exception $ex)
		{
			// Create empty CSS File
			$code = phpParser::comment("Write Your CSS Style Rules Here", $multi = TRUE);
		}
		
		return $code;
	}
	
	/**
	 * Updates the object's css code.
	 * 
	 * @param	string	$code
	 * 		The new css code.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function updateCSSCode($code = "")
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		
		// If code is empty, create an empty CSS file
		if (empty($code))
			$code = phpParser::comment("Write Your CSS Style Rules Here", $multi = TRUE);
		
		// Save css file
		$code = phpParser::clear($code);
		return fileManager::create($itemFolder.self::MODEL_FOLDER."/style.css", $code, $recursive = TRUE);
	}
	
	/**
	 * Loads the object's css code into the output buffer.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function loadCSSCode()
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		
		// Import
		importer::incl($itemFolder.self::MODEL_FOLDER."/style.css", FALSE, FALSE);
	}
	
	/**
	 * Gets the object's css model.
	 * 
	 * @return	string
	 * 		The object's css model.
	 */
	public function getCSSModel()
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		
		// Parse File
		$parser = new DOMParser();
		try
		{
			$parser->load($itemFolder.self::MODEL_FOLDER."/model.xml", FALSE, TRUE);
			$root = $parser->evaluate("//model")->item(0);
		}
		catch (Exception $ex)
		{
			return "";
		}
		
		return $parser->innerHTML($root);
	}
	
	/**
	 * Updates the object's css model.
	 * 
	 * @param	string	$model
	 * 		The new css model in xml format.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function updateCSSModel($model = "")
	{
		// Get Object Folder Path
		$itemFolder = $this->getObjectFullPath();
		$parser = new DOMParser();
		
		// Clear Code
		$model = phpParser::clear($model);
		try
		{
			$parser->load($itemFolder.self::MODEL_FOLDER."/model.xml", FALSE);
			$root = $parser->evaluate("//model")->item(0);
			if (is_null($root))
				throw new Exception("Model root doesn't exist.");
		}
		catch (Exception $ex)
		{
			$root = $parser->create("model");
			$parser->append($root);
			$parser->save($itemFolder.self::MODEL_FOLDER."/", "model.xml", TRUE);
		}
		
		// Update file
		$parser->innerHTML($root, $model);
		return $parser->update(TRUE);
	}
	
	/**
	 * Gets the item's library.
	 * 
	 * @return	string
	 * 		The library name.
	 */
	public function getLibrary()
	{
		return $this->library;
	}
	
	/**
	 * Gets the item's package.
	 * 
	 * @return	string
	 * 		The package name.
	 */
	public function getPackage()
	{
		return $this->package;
	}
	
	/**
	 * Gets the item's namespace.
	 * 
	 * @param	boolean	$full
	 * 		If TRUE, includes the library and the package in the return value.
	 * 
	 * @return	string
	 * 		The object's namespace.
	 */
	public function getNamespace($full = FALSE)
	{
		$namespace = str_replace("::", "\\", $this->namespace);
		$namespace = (empty($namespace) ? "" : "\\".$namespace);
		$namespace = ($full ? $this->library."\\".$this->package : "").$namespace;
		return $namespace;
	}
	
	/**
	 * Gets the item's full name (including the extension).
	 * 
	 * @return	string
	 * 		The item's fullname.
	 */
	protected function getItemFullname()
	{
		return $this->name.".".self::FILE_TYPE;
	}
}
//#section_end#
?>