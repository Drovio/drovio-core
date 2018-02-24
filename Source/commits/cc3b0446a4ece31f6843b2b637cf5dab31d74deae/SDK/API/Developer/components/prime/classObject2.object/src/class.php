<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime;

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
 * @namespace	\components\prime
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "resources::documentation::classDocumentor");
importer::import("API", "Developer", "components::prime::indexing::classIndex");
importer::import("API", "Developer", "components::prime::indexing::packageIndex");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Profile", "tester");

use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\misc\vcs;
use \API\Developer\resources\documentation\classDocumentor;
use \API\Developer\components\prime\indexing\classIndex;
use \API\Developer\components\prime\indexing\packageIndex;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Profile\tester;

/**
 * Prime Class Object
 * 
 * Manages a class object at any project with version control.
 * 
 * @version	{empty}
 * @created	September 18, 2013, 14:15 (EEST)
 * @revised	April 1, 2014, 16:54 (EEST)
 * 
 * @deprecated	Use \DEV\Prototype\classObject instead.
 */
class classObject2
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
	const MODEL_FOLDER = "/model";
	
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
	 * The VCS controller.
	 * 
	 * @type	vcs
	 */
	protected $vcs;
	
	/**
	 * Initializes the class object.
	 * 
	 * @param	string	$repository
	 * 		The vcs repository.
	 * 
	 * @param	boolean	$includeRelease
	 * 		Sets whether this repository will include project release/head objects.
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
	 * 
	 * @return	void
	 */
	public function __construct($repository, $includeRelease, $library, $package, $namespace = "", $name = NULL)
	{
		// Init vcs
		$this->vcs = new vcs($repository, $includeRelease);
		$this->vcs->createStructure();
		
		// Init variables
		$this->library = $library;
		$this->package = $package;
		$this->namespace = $namespace;
		$this->name = $name;
	}
	
	/**
	 * Create a new class object.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @param	boolean	$includeLibraryPath
	 * 		Defines whether the path of the object will include the library.
	 * 
	 * @param	string	$innerPath
	 * 		The inner path of the object (in case of complicated projects).
	 * 
	 * @return	void
	 */
	public function create($name, $includeLibraryPath = TRUE, $innerPath = "")
	{
		$this->name = $name;
		
		// Create vcs item
		$itemID = $this->getItemID();
		$itemPath = "/".$innerPath."/".$this->getItemPath($includeLibraryPath);
		$itemName = $this->getItemFullname();
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// If the item doesn't exist, proceed
		if ($itemTrunkPath && !is_dir($itemTrunkPath))
		{
			// Create folders
			folderManager::create($itemTrunkPath);
			folderManager::create($itemTrunkPath, self::SOURCE_FOLDER);
			folderManager::create($itemTrunkPath, self::MODEL_FOLDER);
			
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
	 * 		The class code of the object.
	 * 
	 * @return	mixed
	 * 		Returns TRUE on success or FALSE on failure. It returns a string telling whether there is a syntax error in the php file.
	 */
	public function updateSourceCode($header = "", $code = "")
	{
		// Get Item trunk folder
		$itemFolder = $this->getVCSTrunkPath();

		// Create default code (if empty)
		if ($code == "")
			$code = phpParser::getClassCode($this->name);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Form Header
		$headerCode = "";
		$headerCode .= phpParser::comment("Namespace")."\n";
		$headerCode .= "namespace ".$this->getFullNamespace().";\n\n";
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
		$status = (fileManager::create($itemFolder.self::SOURCE_FOLDER."/class.php", $finalCode) !== FALSE);
		$this->updateItem();
		
		return $status;
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
	 * 
	 * @return	string
	 * 		Returns the class code section of the object's source code.
	 */
	public function getSourceCode($full = FALSE)
	{
		// Get Item trunk folder
		$itemFolder = $this->getVCSTrunkPath();

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
	 * Includes the object's trunk source code file.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function includeSourceCode()
	{
		// Get Item trunk folder
		$itemFolder = $this->getVCSTrunkPath();
		//$itemFolder = $this->getTrunkPath();

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
		$itemFolder = $this->getVCSTrunkPath();
		
		$filepath = $itemFolder.self::SOURCE_FOLDER."/doc.xml";
		$classDocumentor = new classDocumentor();
		$loaded = $classDocumentor->loadFile($filepath, FALSE);
		if (!$loaded)
		{
			 $classDocumentor->create($this->library, $this->package, $this->namespace);
		}
		else
		{
			// Temporary For updating manual files
			 $classDocumentor->structUpdate($this->library, $this->package, $this->namespace);
		}		
		$classDocumentor->update($this->name, $content);
		
		$manual = $classDocumentor->getDoc();
		$parser = new DOMParser();		
		$parser->loadContent($manual, "XML");
		$status = $parser->save($filepath, '', TRUE);
		
		// Update VCS Item
		$this->updateItem();
		
		return $status;
		
		/*
		
		// Create file (if not exists)
		$parser = new DOMParser();
		try
		{
			$parser->load($itemFolder.self::SOURCE_FOLDER."/doc.xml", FALSE);
			$root = $parser->evaluate("//manual")->item(0);
		}
		catch (Exception $ex)
		{
			// If file doesn't exist, create new empty
			$root = $parser->create("manual");
			$parser->append($root);
			$parser->save($itemFolder.self::SOURCE_FOLDER, "doc.xml", TRUE);
		}
		
		// Set inner HTML
		$parser->innerHTML($root, $content);
		
		// Set namespace
		$class = $parser->evaluate("class", $root)->item(0);
		$namespace = "\\".str_replace("::", "\\", $this->namespace);
		$parser->attr($class, "namespace", $namespace);
		
		// Get datetime
		$now = time();
		
		// Set Date Created if needed
		$dateCreated = $parser->evaluate("class/info/datecreated", $root)->item(0);
		if (empty($dateCreated->nodeValue))
			$parser->nodeValue($dateCreated, $now);
		
		// For backward compatibility
		if (!is_numeric($dateCreated->nodeValue))
		{
			$dc = strtotime($dateCreated->nodeValue);
			$parser->nodeValue($dateCreated, $dc);
		}
		
		// Set Date Revised
		$dateRevised = $parser->evaluate("class/info/daterevised", $root)->item(0);
		$parser->nodeValue($dateRevised, $now);
		
		// Update manual file
		$status = $parser->update(TRUE);
		
		return $status;
		
		*/
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
		$itemFolder = $this->getVCSTrunkPath();
		
		// Parse File
		$parser = new DOMParser();
		$filepath = $itemFolder.self::SOURCE_FOLDER."/doc.xml";
		try
		{
			$parser->load($filepath, FALSE);
		}
		catch (Exception $ex)
		{
			// If file doesn't exist, create new empty
			/*
			$root = $parser->create("manual");
			$parser->append($root);
			$parser->save($itemFolder.self::SOURCE_FOLDER, "doc.xml", TRUE);
			*/
			
			
			$classDocumentor = new classDocumentor();
			$classDocumentor->create($this->library, $this->package, $this->namespace);
			
			$manual = $classDocumentor->getDoc();				
			$parser->loadContent($manual, "XML");
			$status = $parser->save($filepath, '', TRUE);
			
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
		$itemFolder = $this->getVCSTrunkPath();
		
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
		$itemFolder = $this->getVCSTrunkPath();
		
		// If code is empty, create an empty Javascript file
		if ($code == "")
			$code = phpParser::comment("Write Your Javascript Code Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Save javascript file
		$status = fileManager::create($itemFolder."/script.js", $code);
		$this->updateItem();
		
		return $status;
	}
	
	/**
	 * Loads the object's javascript code.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function loadJSCode()
	{
		// Get Object Folder Path
		$itemFolder = $this->getVCSTrunkPath();
		
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
		$itemFolder = $this->getVCSTrunkPath();
		
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
		$itemFolder = $this->getVCSTrunkPath();
		
		// If code is empty, create an empty CSS file
		if (empty($code))
			$code = phpParser::comment("Write Your CSS Style Rules Here", $multi = TRUE);
		
		// Save css file
		$code = phpParser::clearCode($code);
		$status = fileManager::create($itemFolder.self::MODEL_FOLDER."/style.css", $code, $recursive = TRUE);
		$this->updateItem();
		
		return $status;
	}
	
	/**
	 * Loads the object's css code.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function loadCSSCode()
	{
		// Get Object Folder Path
		$itemFolder = $this->getVCSTrunkPath();
		
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
		$itemFolder = $this->getVCSTrunkPath();
		
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
		$itemFolder = $this->getVCSTrunkPath();
		$parser = new DOMParser();
		
		// Clear Code
		$model = phpParser::clearCode($model);
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
		$status = $parser->update(TRUE);
		$this->updateItem();
		
		return $status;
	}
	
	/**
	 * Gets the path to the vcs trunk.
	 * 
	 * @return	string
	 * 		The trunk path of the working branch.
	 */
	protected function getVCSTrunkPath()
	{
		// Get item id
		$itemID = $this->getItemID();

		// Get item trunk path
		return $this->vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Gets the path to the vcs branch.
	 * 
	 * @return	string
	 * 		The branch path of the head branch.
	 */
	protected function getVCSHeadPath()
	{
		// Get item id
		$itemID = $this->getItemID();

		// Get item trunk path
		return $this->vcs->getItemHeadPath($itemID);
	}
	
	/**
	 * Exports the object to the given
	 * 
	 * @param	string	$exportPath
	 * 		The export path for the source code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	protected function export($exportPath)
	{
		// Get Head Object Path
		$headPath = $this->getVCSHeadPath();
		$sourceFile = $headPath."/".self::SOURCE_FOLDER."/class.php";
		
		$phpCheck = phpParser::syntax($sourceFile);
		if (!$phpCheck)
			return $phpCheck;
		
		// Export Object Path
		$sourceCodeObjectPath = $exportPath."/".$this->library."/".$this->package."/".str_replace("::", "/", $this->namespace)."/".$this->name.".php";

		// Export Source Code
		$finalCode = fileManager::get($sourceFile);
		fileManager::create($sourceCodeObjectPath, $finalCode, TRUE);
		
		return TRUE;
	}
	
	/**
	 * Update the item in the working index.
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
	 * 		The namespace name.
	 */
	public function getNamespace($full = FALSE)
	{
		$namespace = str_replace("::", "\\", $this->namespace);
		$namespace = (empty($namespace) ? "" : "\\".$namespace);
		$namespace = ($full ? $this->library."\\".$this->package : "").$namespace;
		return $namespace;
	}
	
	/**
	 * Gets the id of the current item.
	 * 
	 * @return	string
	 * 		The item's id.
	 */
	protected function getItemID()
	{
		return "cl".hash("md5", $this->library."_".$this->package."_".$this->namespace."_".$this->name);
	}
	
	/**
	 * Gets the item's path (with library, package, namespace etc).
	 * 
	 * @param	boolean	$includeLibraryPath
	 * 		Defines whether the path will include the item's library.
	 * 
	 * @return	string
	 * 		The item's path.
	 */
	protected function getItemPath($includeLibraryPath = TRUE)
	{
		$library = ($includeLibraryPath ? $this->library : "");
		$namespace = str_replace("::", "/", $this->namespace);
		return "/".$library."/".$this->package."/".$namespace."/";
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
	
	/**
	 * Returns the full namespace of the item for code usage.
	 * 
	 * @return	string
	 * 		The full namespace of the item.
	 */
	protected function getFullNamespace()
	{
		$namespace = str_replace("::", "\\", $this->namespace);
		return $this->library."\\".$this->package.(empty($namespace) ? "" : "\\".$namespace);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getDependencies()
	{
		// useMap[incl:namespace] -> refs
		$code = $this->getSourceCode();
		// Strip Comments
		$code = phpParser::removeComments($code);
		
		// Imports are ignored! Only SDK uses are included. 
		// Get uses
		preg_match_all("/^[ \s\t]*\buse\b([^\n\r;]*)/m", $code, $matches);
		// Get actual uses
		preg_match_all("/\bnew\b[ \s\t]+([a-zA-Z_\x7f-\xff\\\\][a-zA-Z0-9_\x7f-\xff\\\\]*)[ \s\t]*\(/", $code, $newMatches);
		preg_match_all("/([a-zA-Z_\x7f-\xff\\\\][a-zA-Z0-9_\x7f-\xff\\\\]*)::(?=[^\)\n\r]+\()/", $code, $staticMatches);
		
		// Build uses map
		$useMap = array();
		// Add namespace
		$fns = "\\".$this->getFullNamespace();
		$useMap['namespace']['path'] = $fns;
		$useMap['namespace']['alias'] = NULL;
		
		// Get defined uses
		foreach ($matches[1] as $hits) 
		{
			$h = explode(',', $hits);
			foreach ($h as $hit)
			{
				$parts = preg_split("/ [aA][sS] /", $hit);
				$path = trim($parts[0]);
				$head = strstr($path, "\\", TRUE);
				if (!empty($head) && empty($useMap[$head]['path']))
					$head = "namespace";
					
				$path = $useMap[$head]['path'].($head == "namespace" ? "\\".$path : strstr($path, "\\"));
				$alias = trim($parts[1]);
				$ref = (empty($alias) ? array_pop(explode("\\", $path)) : $alias);
				
				$useMap[$ref]['path'] = $path;
				$useMap[$ref]['alias'] = $alias;
			}
		}
		
		// Get/filter unique uses
		$uses = array_merge($newMatches[1], $staticMatches[1]);
		$uses = array_flip($uses);
		unset($uses['parent']);
		unset($uses['self']);
		if (!empty($uses['importer']))
		{
			$uses['\\API\\Platform\\importer'] = $uses['importer'];
			unset($uses['importer']);
		}
		$uses = array_keys($uses);
		
		$realUses = array();
		// Create uses array. For each use, keep name (path), type, and alias if available...
		foreach ($uses as $idx => $use)
		{
			$realUses[$idx] = $this->normalizeUse($use, $useMap);
			$realUses[$idx]['type'] = "uses";
		}
		
		// Get extends
		preg_match("/^[ \t\s]*(?:class|interface).*\bextends\b[ \t\s]*([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/m", $code, $matches);
		if (!empty($matches))
		{
			$last = count($realUses);
			$realUses[$last] = $this->normalizeUse($matches[1], $useMap);
			$realUses[$last]['type'] = "extends";
		}
		
		// Get implements
		preg_match("/^[ \t\s]*(?:class|interface).*\bimplements\b[ \t\s]*([^\n\r\{]*)/m", $code, $matches);
		if (!empty($matches))
		{
			$m = emplode(",", $matches[1]);
			foreach ($m as $hit)
			{
				$last = count($realUses);
				$realUses[$last] = $this->normalizeUse($hit, $useMap);
				$realUses[$last]['type'] = "implements";
			}
		}
		
		// Check deprecated
		// if (strpos(" * @deprecated") !== FALSE)
		
		// Update dependencies file
		$parser = new DOMParser();
		$root = $parser->create("dependencies");
		$parser->attr($root, "name", "\\".$this->getFullNamespace()."\\".$this->name);
		$parser->append($root);
		
		$nodes = array();
		$nodes['uses'] = $parser->create("uses");
		$parser->append($root, $nodes['uses']);
		
		$nodes['extends'] = $parser->create("extends");
		$parser->append($root, $nodes['extends']);
		
		$nodes['implements'] = $parser->create("implements");
		$parser->append($root, $nodes['implements']);
		
		foreach ($realUses as $ref => $use)
		{
			$reference = $parser->create("reference");
			$parser->attr($reference, "name", $use['path']);
			$parser->attr($reference, "alias", $use['alias']);
			$parser->append($nodes[$use['type']], $reference);
		}
		
		/*$dependPath = systemRoot.tester::getTrunk()."/dependencies.xml";
		$parser->save($dependPath, '', TRUE);*/
		
		//Analysis/Dependencies/Core/SDK/
		//systemRoot.tester::getTrunk()."/temp/dependencies.xml";
		//return print_r($realUses, TRUE);
		
		return print_r($parser->getXML(TRUE), TRUE);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$use
	 * 		{description}
	 * 
	 * @param	{type}	$useMap
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function normalizeUse($use, $useMap)
	{
		$info = array();
		$pos = strpos("\\", $use);
		if ($pos === 0)
		{
			$info['path'] = $use;
			$info['alias'] = NULL;
			return $info;
		}
		
		$head = array_shift(explode("\\", $use));
		if (!empty($head) && empty($useMap[$head]['path']))
			$head = "namespace";
		
		$info['path'] = $useMap[$head]['path'].($head == "namespace" ? "\\".$use : strstr($use, "\\"));
		$info['alias'] = $useMap[$head]['alias'];
		return $info;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$inbound
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function aqcuireDependencies($inbound = TRUE)
	{
		$p = new DOMParser();
		$p->load(tester::getTrunk()."/temp/dependencies.xml", $rootRelative = TRUE, $preserve = FALSE);
		
		$ds = $p->evaluate("//dependencies");
		
		$dependencies = array();
		foreach ($ds as $d)
		{
			$c = $p->attr($d, "name");
			$dependencies[$c] = array();
			
			$ref = $p->evaluate("*//reference/@name", $d);
			foreach ($ref as $name)
			{
				$index = ($inbound ? $name->value : $c);
				$value = ($inbound ? $c : $name->value);
				$dependencies[$index][] = $value;
			}
		}
		
		// Sort by count
		uasort($dependencies, function($a, $b){
			$x = count($a);
			$y = count($b);
			
			return ($x > $y) ? -1 : 1;
		});
 
		return $dependencies;
	}
}
//#section_end#
?>