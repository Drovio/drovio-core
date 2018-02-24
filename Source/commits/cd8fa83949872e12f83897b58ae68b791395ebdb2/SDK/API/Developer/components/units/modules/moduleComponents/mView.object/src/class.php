<?php
//#section#[header]
// Namespace
namespace API\Developer\components\units\modules\moduleComponents;

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
 * @namespace	\components\units\modules\moduleComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "DOMParser");

use \API\Developer\misc\vcs;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\resources\paths;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;

/**
 * Module View Controller
 * 
 * Manages a module view
 * 
 * @version	{empty}
 * @created	November 30, 2013, 16:19 (EET)
 * @revised	April 2, 2014, 11:35 (EEST)
 * 
 * @deprecated	Use \DEV\Modules\components\mView instead.
 */
class mView
{
	/**
	 * The view id.
	 * 
	 * @type	string
	 */
	private $id;
	
	/**
	 * The module's views root.
	 * 
	 * @type	string
	 */
	private $viewsRoot;
	
	/**
	 * The current view directory.
	 * 
	 * @type	string
	 */
	private $viewPath;
	
	/**
	 * The module vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The module id.
	 * 
	 * @type	string
	 */
	private $moduleID;
	
	/**
	 * The array of dependencies.
	 * 
	 * @type	array
	 */
	private $dependencies = array();
	
	/**
	 * The array of inner modules.
	 * 
	 * @type	array
	 */
	private $innerModules = array();
	
	/**
	 * Initializes the view.
	 * 
	 * @param	vcs	$vcs
	 * 		The module vcs manager object.
	 * 
	 * @param	integer	$moduleID
	 * 		The module item id.
	 * 
	 * @param	string	$viewsRoot
	 * 		The view's root directory.
	 * 
	 * @param	string	$viewID
	 * 		The view id.
	 * 		(Leave empty for new views).
	 * 
	 * @return	void
	 */
	public function __construct($vcs, $moduleID, $viewsRoot, $viewID = "")
	{
		// Put your constructor method code here.
		$this->viewsRoot = $viewsRoot;
		$this->vcs = $vcs;
		$this->moduleID = $moduleID;
		
		if (!empty($viewID))
		{
			$this->id = $viewID;
			$this->loadInfo();
		}
	}
	
	/**
	 * Create a new view.
	 * 
	 * @param	string	$viewID
	 * 		The view id.
	 * 
	 * @return	void
	 */
	public function create($viewID)
	{
		// Initialize variables
		$this->id = $viewID;
		$this->viewPath = $this->viewsRoot."/".$this->getDirectoryName($this->id);
		
		// Create vcs item
		$itemID = $this->getItemID();
		$itemPath = "/".$this->viewsRoot."/";
		$itemName = $this->getDirectoryName($this->id);
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create structure
		folderManager::create($itemTrunkPath);
		
		$parser = new DOMParser();
		$root = $parser->create("view", "", $this->id);
		$parser->append($root);
		$parser->save($itemTrunkPath."/index.xml");
		
		$dependencies = $parser->create("dependencies");
		$parser->append($root, $dependencies);
		
		$innerModules = $parser->create("inner");
		$parser->append($root, $innerModules);
		$parser->update();
		
		// Update content
		$this->updateView();
		$this->updatePHPCode();
		$this->updateJSCode();
		
		return TRUE;
	}
	
	/**
	 * Update the view's information (including dependencies and inner modules).
	 * 
	 * @param	array	$dependencies
	 * 		An array of dependencies.
	 * 		It has as a key the [Library,Object] value.
	 * 
	 * @param	array	$innerModules
	 * 		An array of inner modules.
	 * 		The key is the module id, the value is either the friendlyName of the module or 'off' in case of deletion.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($dependencies = array(), $innerModules = array())
	{
		// Update vcs item
		$itemTrunkPath = $this->getItemPath(TRUE);
		
		// Load index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath."/index.xml", FALSE);
		
		// Update Dependencies
		$base = $parser->evaluate("//dependencies")->item(0);
		$parser->innerHTML($base, "");
		$this->setDependencies($dependencies);
		foreach ($this->dependencies as $library => $objects)
			foreach ($objects as $object)
			{
				$entry = $parser->create("dependency");
				$parser->attr($entry, "library", $library);
				$parser->attr($entry, "object", $object);
				$parser->append($base, $entry);
			}
		
		// Update Inner Modules
		$base = $parser->evaluate("//inner")->item(0);
		$parser->innerHTML($base, "");
		$this->setInnerModules($innerModules);
		foreach ($this->innerModules as $key => $value)
			if ($value != "off" && $value != "on")
			{
				$entry = $parser->create("module", $value, $key);
				$parser->append($base, $entry);
			}
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Sets the inner modules for this view.
	 * Modules that are marked for deletion have a value of "off".
	 * The rest are formed from their arrays, respectively, which in turn are then unset.
	 * 
	 * @param	array	$inner
	 * 		The array of inner modules given by key and value.
	 * 		As key it's the module id and the value will be either "off" for module deletion or a friendly name.
	 * 
	 * @return	void
	 */
	private function setInnerModules($inner)
	{
		foreach ($inner as $key => $i)
			if (gettype($i) == "array")
			{
				if (!empty($i['title']) && gettype($i['title']) == "string" && is_numeric($i['moduleId']))
					$inner[$i['title']] = $i['moduleId'];
				unset($inner[$key]);
			}

		foreach ($inner as $key => $value)
			$this->innerModules[$key] = $value;
	}
	
	/**
	 * Sets the dependencies as a normalized dependency array.
	 * 
	 * @param	array	$dependencies
	 * 		The dependencies of the array.
	 * 		It's an empty array with keys in the [library, object] form.
	 * 
	 * @return	void
	 */
	private function setDependencies($dependencies)
	{
		// Init dependencies
		$this->dependencies = array();
		
		// Gather up
		foreach ($dependencies as $key => $val)
		{
			// Get Parts
			$parts = explode(",", $key);
			$library = $parts[0];
			$object = $parts[1];
			
			// Build array
			$this->dependencies[$library][] = $object;
		}
		
		return $this->dependencies;
	}
	
	/**
	 * Loads the view's info from the index file.
	 * 
	 * @return	void
	 */
	private function loadInfo()
	{
		// Set view directory
		$itemTrunkPath = $this->getItemPath(FALSE);
		
		// Load index info
		$parser = new DOMParser();
		$parser->load($itemTrunkPath."/index.xml", FALSE);
		
		// Load Dependencies
		$this->dependencies = array();
		$dependencyEntries = $parser->evaluate("//dependency");
		foreach ($dependencyEntries as $entry)
		{
			$library = $parser->attr($entry, "library");
			$object = $parser->attr($entry, "object");
			$this->dependencies[$library][] = $object;
		}
		
		// Load Inner Modules
		$this->innerModules = array();
		$innerEntries = $parser->evaluate("//module");
		foreach ($innerEntries as $entry)
		{
			$friendlyName = $parser->attr($entry, "id");
			$moduleID = $parser->nodeValue($entry);
			$this->innerModules[$friendlyName] = $moduleID;
		}
	}
	
	/**
	 * Gets the view's html code.
	 * 
	 * @param	boolean	$head
	 * 		Get content from head.
	 * 
	 * @return	string
	 * 		The html code.
	 */
	public function getHTML($head = FALSE)
	{
		// Get item
		$itemPath = $this->getItemPath(FALSE, $head);
		
		// Load content
		return fileManager::get($itemPath."/view.html");
	}
	
	/**
	 * Gets the view's css code.
	 * 
	 * @param	boolean	$head
	 * 		Get content from head.
	 * 
	 * @return	string
	 * 		The css code.
	 */
	public function getCSS($head = FALSE)
	{
		// Get item
		$itemPath = $this->getItemPath(FALSE, $head);
		
		// Load content
		return fileManager::get($itemPath."/style.css");
	}
	
	/**
	 * Updates the view's html and css.
	 * 
	 * @param	string	$html
	 * 		The view html.
	 * 
	 * @param	string	$css
	 * 		The view css.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateView($html = "", $css = "")
	{
		// Update vcs item
		$itemTrunkPath = $this->getItemPath(TRUE);
	
		$html = phpParser::clear($html);
		$status1 = fileManager::put($itemTrunkPath."/view.html", $html);
		
		// Update css
		$css = cssParser::clear($css);
		$status2 = fileManager::put($itemTrunkPath."/style.css", $css);
		
		return $status1 && $status2;
	}
	
	/**
	 * Gets the view's php code.
	 * 
	 * @param	boolean	$head
	 * 		Get content from head.
	 * 
	 * @return	string
	 * 		The php code.
	 */
	public function getPHPCode($head = FALSE)
	{
		// Get item
		$itemPath = $this->getItemPath(FALSE, $head);
		
		if (!$head)
		{
			// Load content
			$sourceCode = fileManager::get($itemPath."/view.php");
			$sections = phpCoder::sections($sourceCode);
	
			// Return source code
			return trim($sections["code"]);
		}
		else
		{
			$sourceCode = fileManager::get($itemPath."/view.php");
			return $sourceCode;
		}
	}
	
	/**
	 * Runs the view from the trunk.
	 * 
	 * @return	mixed
	 * 		The view result.
	 */
	public function run()
	{
		// Get item path
		$itemPath = $this->getItemPath();
		
		// Run - Require
		return importer::req($itemPath."/view.php", FALSE);
	}
	
	/**
	 * Updates the view's php code.
	 * 
	 * @param	string	$code
	 * 		The php code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updatePHPCode($code = "")
	{
		// Update vcs item
		$itemTrunkPath = $this->getItemPath(TRUE);
		
		// Set source code (default if not given)
		$sourceCode = (trim($code) == "" ? $this->getDefaultSourceCode() : $code);
		
		// Clear code from unwanted characters
		$sourceCode = phpParser::clearCode($sourceCode);
		// Comment out dangerous commands
		//$sourceCode = phpParser::safe($sourceCode);
		// Set code section
		$sourceCode = phpCoder::section($sourceCode, "code");
		$header = $this->buildHeader(TRUE);
		
		// Merge all source code
		$sourceCode = $header.$sourceCode;
		$sourceCode = phpParser::wrap($sourceCode);
		
		// Get File Path
		$filePath = $itemTrunkPath."/view.php";
		
		// Create temp file to check syntax
		$tempFile = $filePath.".temp";
		fileManager::create($tempFile, $sourceCode);
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);
		fileManager::remove($tempFile);
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;

		// Update File
		return fileManager::put($filePath, $sourceCode);
	}
	
	/**
	 * Gets the view's default source code.
	 * 
	 * @return	string
	 * 		The default template source code.
	 */
	private function getDefaultSourceCode()
	{
		// Get Default Module Code
		$sourceCode = fileManager::get(systemRoot.paths::getDevRsrcPath()."/Content/Modules/Code/default.inc");
		
		// Clear Delimiters
		$sourceCode = phpParser::unwrap($sourceCode);
		
		// Wrap to section
		return trim($sourceCode);
	}
	
	/**
	 * Builds the view's php header.
	 * 
	 * @param	boolean	$wrapped
	 * 		Chooses whether to wrap the code in the header section or not.
	 * 
	 * @return	strings
	 * 		The php header including all inner modules and dependencies.
	 */
	private function buildHeader($wrapped = FALSE)
	{
		// Module ID
		$moduleHeader = "";
		$moduleHeader .= phpParser::comment("Module Declaration", $multi = FALSE)."\n";
		$moduleHeader .= phpParser::variable("moduleID").' = '.$this->moduleID.";\n\n";
		
		// Inner Modules
		$moduleHeader .= phpParser::comment("Inner Module Codes", $multi = FALSE)."\n";
		$innerHeader = phpParser::variable("innerModules").' = array();'."\n";
		foreach ($this->innerModules as $key => $value)
			$innerHeader .= '$innerModules[\''.$key.'\'] = '.$value.";\n";
		$innerHeader .= "\n";
		
		// Get Headers
		$path = systemRoot.paths::getDevRsrcPath()."/Content/Modules/Headers/private.inc";
		$privateHeader = fileManager::get($path);
		$privateHeader = phpParser::unwrap($privateHeader);
		
		// Imports
		$importsHeader = $this->buildImports();
		
		// Merge
		$headerContent = "";
		$headerContent .= $moduleHeader;
		$headerContent .= $innerHeader;
		$headerContent .= $privateHeader;
		$headerContent .= $importsHeader;
		
		return ($wrapped ? phpCoder::section($headerContent, "header") : $headerContent);
	}
	
	/**
	 * Builds the imports section in the header.
	 * 
	 * @return	string
	 * 		The import section of the header.
	 */
	private function buildImports()
	{
		// Build Imports
		$imports = "\n\n";
		$imports .= phpParser::comment("Import Packages", $multi = FALSE)."\n";
		$startups = "\n";
		foreach ($this->dependencies as $library => $objects)
			foreach ($objects as $object)
			{
				$parts = explode("::", $object);
				$package = $parts[0];
				unset($parts[0]);
				$objectName = implode("::", $parts);
				
				if (empty($objectName))
					$imports .= 'importer::import("'.$library.'", "'.$package.'");'."\n";
				else
					$imports .= "importer::import('".$library."', '".$package."', '".$objectName."');"."\n";
			}
		
		// Merge and Clear
		$imports .= $startups;
		return preg_replace('/\n$/', '', $imports);
	}
	
	/**
	 * Gets the view's js code.
	 * 
	 * @param	boolean	$head
	 * 		Get content from head.
	 * 
	 * @return	string
	 * 		The js code.
	 */
	public function getJSCode($head = FALSE)
	{
		// Get item
		$itemTrunkPath = $this->getItemPath(FALSE, $head);
		
		// Load content
		return fileManager::get($itemTrunkPath."/script.js");
	}
	
	/**
	 * Updates the view's code.
	 * 
	 * @param	string	$code
	 * 		The js code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateJSCode($code = "")
	{
		// Update vcs item
		$itemTrunkPath = $this->getItemPath(TRUE);
		
		$code = jsParser::clear($code);
		return fileManager::put($itemTrunkPath."/script.js", $code);
	}
	
	/**
	 * Gets the view dependencies.
	 * 
	 * @return	array
	 * 		An array by library for each package and object (if any)
	 */
	public function getDependencies()
	{
		$this->loadInfo();
		return $this->dependencies;
	}
	
	/**
	 * Gets the view inner modules
	 * 
	 * @return	array
	 * 		An array of inner modules, the name as id and the content as the module id reference.
	 */
	public function getInnerModules()
	{
		$this->loadInfo();
		return $this->innerModules;
	}
	
	/**
	 * Gets the view's item trunk path.
	 * 
	 * @param	boolean	$update
	 * 		Indicates whether the item should be updated in the vcs.
	 * 
	 * @param	boolean	$head
	 * 		Get content from head.
	 * 
	 * @return	string
	 * 		The item trunk path.
	 */
	private function getItemPath($update = FALSE, $head = FALSE)
	{
		$itemID = $this->getItemID();
		if ($update)
			$this->vcs->updateItem($itemID);
		
		if ($head)
			return $this->vcs->getItemHeadPath($itemID);
		else
			return $this->vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Gets the view's vcs item id.
	 * 
	 * @return	string
	 * 		The item id.
	 */
	private function getItemID()
	{
		return "mv".md5("view".$this->moduleID."_".$this->id);
	}
	
	/**
	 * Gets the view's directory name.
	 * 
	 * @param	string	$id
	 * 		The view id.
	 * 
	 * @return	string
	 * 		The directory name.
	 */
	private static function getDirectoryName($id)
	{
		return $id.".view";
	}
}
//#section_end#
?>