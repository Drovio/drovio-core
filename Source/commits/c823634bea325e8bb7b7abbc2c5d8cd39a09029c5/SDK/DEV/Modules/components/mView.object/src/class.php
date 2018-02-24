<?php
//#section#[header]
// Namespace
namespace DEV\Modules\components;

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
 * @package	Modules
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Tools", "parsers/phpParser");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");
importer::import("DEV", "Tools", "coders/phpCoder");
importer::import("DEV", "Resources", "paths");

use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Resources\paths;

/**
 * Module View Controller
 * 
 * Manages a module view
 * 
 * @version	2.0-2
 * @created	April 2, 2014, 11:35 (EEST)
 * @updated	April 27, 2015, 11:29 (EEST)
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
	 * The module vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The module id.
	 * 
	 * @type	integer
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
	 * 		The module id.
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($viewID)
	{
		// Initialize variables
		$this->id = $viewID;
		
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
	 * Sets the dependencies as a normalized dependency array.
	 * 
	 * @param	array	$dependencies
	 * 		The array of dependencies.
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
	 * @return	string
	 * 		The html code.
	 */
	public function getHTML()
	{
		// Get item
		$itemPath = $this->getItemPath(FALSE);
		
		// Load content
		return fileManager::get($itemPath."/view.html");
	}
	
	/**
	 * Gets the view's css code.
	 * 
	 * @param	boolean	$normalCss
	 * 		If true, return the parsed css code, else it returns the initial scss.
	 * 		It is FALSE by default.
	 * 
	 * @return	string
	 * 		The css code.
	 */
	public function getCSS($normalCss = FALSE)
	{
		// Get item
		$itemPath = $this->getItemPath(FALSE);
	
		// Get scss
		$scss = fileManager::get($itemPath."/style.scss");
		if (empty($scss) || $normalCss)
		{
			// If the scss is empty or the user requested the css specificly
			return fileManager::get($itemPath."/style.css");
		}
		
		// Return scss
		return $scss;
	}
	
	/**
	 * Updates the view's html and css.
	 * 
	 * @param	string	$html
	 * 		The view html.
	 * 
	 * @param	string	$scss
	 * 		The view css in scss format.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateView($html = "", $scss = "")
	{
		// Update vcs item
		$itemTrunkPath = $this->getItemPath(TRUE);
	
		$html = phpParser::clear($html);
		$status1 = fileManager::put($itemTrunkPath."/view.html", $html);
		
		// Update scss and css
		$scss = cssParser::clear($scss);
		$status2 = fileManager::put($itemTrunkPath."/style.scss", $scss);
		
		// Compile scss to css
		$css = $scss;//scssParser::compile($scss);
		$status3 = fileManager::put($itemTrunkPath."/style.css", $css);
		
		return $status1 && $status2 && $status3;
	}
	
	/**
	 * Gets the view's php code.
	 * 
	 * @return	string
	 * 		The php code.
	 */
	public function getPHPCode()
	{
		// Get item
		$itemPath = $this->getItemPath(FALSE);
		
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
		$sourceCode = phpParser::clear($sourceCode);
		
		// Comment out dangerous commands
		$sourceCode = phpCoder::safe($sourceCode);

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
		
		// Update single dependencies and metrics
		$this->updateSingleDependencies($sourceCode);
		$this->updateMetrics($sourceCode);

		// Update File
		return fileManager::put($filePath, $sourceCode);
	}
	
	/**
	 * Builds the view's php header.
	 * 
	 * @param	boolean	$wrapped
	 * 		Chooses whether to wrap the code in the header section or not.
	 * 
	 * @return	string
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
		$path = systemRoot.paths::getDevRsrcPath()."/modules/moduleViewHeader.inc";
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
				$imports .= 'importer::import("'.$library.'", "'.$package.'");'."\n";
			}
		
		// Merge and Clear
		$imports .= $startups;
		return preg_replace('/\n$/', '', $imports);
	}
	
	/**
	 * Get all dependencies from the object given the dependencies' file.
	 * 
	 * @param	string	$depFile
	 * 		The dependencies' xml file.
	 * 		You can load the trunk's file or the release's file.
	 * 
	 * @param	boolean	$rootRelative
	 * 		Whether the given path is root relative or not.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function getSingleDependencies($depFile, $rootRelative = FALSE)
	{
		// Load file
		$parser = new DOMParser();
		try
		{
			$parser->load($depFile, $rootRelative);
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		// Get dependencies
		$dependencies = array();
		$uses = $parser->evaluate("//use");
		foreach ($uses as $use)
		{
			$useInfo = array();
			$useInfo['path'] = $parser->attr($use, "path");
			$useInfo['alias'] = $parser->attr($use, "alias");
			
			$dependencies[] = $useInfo;
		}
		
		// Return array of uses
		return $dependencies;
	}
	
	/**
	 * Update source code dependencies.
	 * 
	 * @param	string	$code
	 * 		The source code to parse the dependencies from.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function updateSingleDependencies($code)
	{
		// Get Item trunk folder
		$itemFolder = $this->getItemPath(TRUE);
		
		// Get uses
		$uses = phpParser::getUses($code);
		
		// Create xml file for uses
		$parser = new DOMParser();
		$root = $parser->create("dependencies");
		$parser->append($root);
		
		foreach ($uses as $use)
		{
			$dep_entry = $parser->create("use");
			$parser->append($root, $dep_entry);
			
			// Set name and alias
			$parser->attr($dep_entry, "path", $use['path']);
			$parser->attr($dep_entry, "alias", $use['alias']);
		}
		
		// Save file
		return $parser->save($itemFolder."/dependencies.xml");
	}
	
	/**
	 * Validate module view dependencies comparing the uses with the imports.
	 * 
	 * @return	mixed
	 * 		TRUE if everything is OK.
	 * 		An array of missing dependencies if there are missing dependencies.
	 */
	public function validateSingleDependencies()
	{
		// Set predefined dependencies to skip in the check
		$predefinedDependencies = array();
		$predefinedDependencies["API"]["Platform"] = 1;
		$predefinedDependencies["ESS"]["Protocol"] = 1;
		$predefinedDependencies["UI"]["Html"] = 1;
		$predefinedDependencies["DEV"]["Profiler"] = 1;
		
		// Get single dependencies from trunk
		// Get Item trunk folder
		$itemFolder = $this->getItemPath(TRUE);
		$singleDependencies = $this->getSingleDependencies($itemFolder."/dependencies.xml", FALSE);
		
		// Get package dependencies
		$packageDependencies = $this->getDependencies();
		
		// Validate cross-reference
		$missingDependencies = array();
		foreach ($singleDependencies as $depInfo)
		{
			// Skip exception
			if ($depInfo['path'] == "\\\\Exception" || $depInfo['path'] == "\\Exception")
				continue;
				
			// Get use library and package
			$parts = explode("\\", $depInfo['path']);
			$sidx = 0;
			if (empty($parts[0]))
				$sidx = 1;
			$library = $parts[$sidx];
			$package = $parts[$sidx + 1];
			
			// Check for predefined header
			if ($predefinedDependencies[$library][$package])
				continue;
			
			// Check if imports include it
			if (!in_array($package, $packageDependencies[$library]))
				$missingDependencies[$library][$package] = 1;
		}
		
		// Return missing dependencies (if not empty)
		if (!empty($missingDependencies))
			return $missingDependencies;
		
		// Everything is valid
		return TRUE;
	}
	
	/**
	 * Get all source code's metrics data for the given object.
	 * 
	 * @param	string	$metFile
	 * 		The metrics' xml file.
	 * 		You can load the trunk's file or the release's file.
	 * 
	 * @return	void
	 */
	public static function getMetrics($metFile)
	{
		// Load file
		$parser = new DOMParser();
		try
		{
			$parser->load($metFile, FALSE);
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		// Get metrics
		$metrics = array();
		$metrics['LOC'] = $parser->evaluate("//metrics/loc")->item(0)->nodeValue;
		$metrics['CLOC'] = $parser->evaluate("//metrics/cloc")->item(0)->nodeValue;
		$metrics['SLOC-P'] = $parser->evaluate("//metrics/slocp")->item(0)->nodeValue;
		$metrics['NOF'] = $parser->evaluate("//metrics/nof")->item(0)->nodeValue;
		$metrics['LOC-PF'] = $parser->evaluate("//metrics/locpf")->item(0)->nodeValue;
		
		// Return metrics data
		return $metrics;
	}
	
	/**
	 * Update the source code's metrics data.
	 * 
	 * @param	string	$code
	 * 		The source code to parse the metrics from.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function updateMetrics($code)
	{
		// Get Item trunk folder
		$itemFolder = $this->getItemPath(TRUE);
		
		// Get metrics
		$metrics = phpParser::getMetrics($code);
		
		// Create xml file for uses
		$parser = new DOMParser();
		$root = $parser->create("metrics");
		$parser->append($root);
		
		$m = $parser->create("loc", "".$metrics['LOC']);
		$parser->append($root, $m);
		$m = $parser->create("cloc", "".$metrics['CLOC']);
		$parser->append($root, $m);
		$m = $parser->create("slocp", "".$metrics['SLOC-P']);
		$parser->append($root, $m);
		$m = $parser->create("nof", "".$metrics['NOF']);
		$parser->append($root, $m);
		$m = $parser->create("locpf", "".$metrics['LOC-PF']);
		$parser->append($root, $m);
		
		// Save file
		return $parser->save($itemFolder."/metrics.xml");
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
		$sourceCode = fileManager::get(systemRoot.paths::getDevRsrcPath()."/modules/moduleViewDefault.inc");
		
		// Clear Delimiters
		$sourceCode = phpParser::unwrap($sourceCode);
		
		// Wrap to section
		return trim($sourceCode);
	}
	
	/**
	 * Gets the view's js code.
	 * 
	 * @return	string
	 * 		The js code.
	 */
	public function getJSCode()
	{
		// Get item
		$itemTrunkPath = $this->getItemPath(FALSE);
		
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
	 * Removes the view from the vcs repository.
	 * Use the module class to remove the view instead, to remove from the index.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove object from vcs
		$itemID = $this->getItemID();
		return $this->vcs->deleteItem($itemID);
	}
	
	/**
	 * Gets the view's item trunk path.
	 * 
	 * @param	boolean	$update
	 * 		Indicates whether the item should be updated in the vcs.
	 * 
	 * @return	string
	 * 		The item trunk path.
	 */
	private function getItemPath($update = FALSE)
	{
		$itemID = $this->getItemID();
		if ($update)
			$this->vcs->updateItem($itemID);
		
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