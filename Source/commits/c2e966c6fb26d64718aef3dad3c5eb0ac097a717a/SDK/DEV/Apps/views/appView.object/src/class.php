<?php
//#section#[header]
// Namespace
namespace DEV\Apps\views;

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
 * @package	Apps
 * @namespace	\views
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "views/appViewManager");
importer::import("DEV", "Apps", "source/srcPackage");
importer::import("DEV", "Tools", "parsers/phpParser");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/scssParser");
importer::import("DEV", "Tools", "parsers/jsParser");
importer::import("DEV", "Tools", "coders/phpCoder");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Apps\application;
use \DEV\Apps\views\appViewManager;
use \DEV\Apps\source\srcPackage;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\scssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Version\vcs;
use \DEV\Resources\paths;

/**
 * Application View
 * 
 * Manages an application view object manager.
 * 
 * @version	3.1-5
 * @created	August 22, 2014, 13:40 (EEST)
 * @updated	September 3, 2015, 11:49 (EEST)
 */
class appView
{
	/**
	 * The view object type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "view";
	
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $app;
	
	/**
	 * The view name.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * The view folder name.
	 * 
	 * @type	string
	 */
	private $folder;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The array of sdk dependencies.
	 * 
	 * @type	array
	 */
	private $sdk_dependencies = array();
	
	/**
	 * The array of application dependencies.
	 * 
	 * @type	array
	 */
	private $app_dependencies = array();
	
	/**
	 * Constructor. Initializes the object's variables.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$folder
	 * 		The view folder.
	 * 		For root views, leave this field empty.
	 * 		It is empty by default.
	 * 
	 * @param	string	$name
	 * 		The view name.
	 * 		For creating new views, leave this empty.
	 * 
	 * @return	void
	 */
	public function __construct($appID, $folder = "", $name = "")
	{
		// Init application
		$this->app = new application($appID);
		
		// Init vcs
		$this->vcs = new vcs($appID);
		
		// Set name
		$this->folder = $folder;
		$this->name = $name;
	}
	
	/**
	 * Creates a new application view.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($viewName)
	{
		// Check that name is not empty and valid
		if (empty($viewName))
			return FALSE;
			
		// Set view name
		$this->name = $viewName;
		
		// Create object index
		$vMan = new appViewManager($this->app->getID());
		$status = $vMan->createView($this->folder, $this->name);
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = application::VIEWS_FOLDER."/".$this->folder;
		$itemName = $viewName.".".self::FILE_TYPE;
		$viewFolder = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create view structure
		$this->createViewStructure();
		
		// Update dependencies
		$dependencies = array();
		$dependencies["UI,Apps"] = 1;
		$this->updateInfo($dependencies);
		
		// Set initial default code
		$this->updatePHPCode();
		
		return TRUE;
	}
	
	/**
	 * Get the view's php code.
	 * 
	 * @param	boolean	$full
	 * 		If true, returns the entire php code as is form the file, otherwise it returns only the view code section.
	 * 
	 * @return	string
	 * 		The php source code.
	 */
	public function getPHPCode($full = FALSE)
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load php code
		$code = fileManager::get($viewFolder."/view.php");
		
		if (!$full)
		{
			// Unwrap php code
			$code = phpParser::unwrap($code);
			$sections = phpCoder::sections($code);
			return $sections['view'];
		}
		else
			return $code;
	}
	
	/**
	 * Updates the view's php code.
	 * 
	 * @param	string	$code
	 * 		The view's new php code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updatePHPCode($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Set source code (default if not given)
		$sourceCode = (trim($code) == "" ? $this->getDefaultSourceCode() : $code);
		
		// Clear code from unwanted characters
		$sourceCode = phpParser::clear($sourceCode);
		
		// Comment out dangerous commands
		$sourceCode = phpCoder::safe($sourceCode);

		// Build header
		$headerCode = $this->buildHeader();
		
		// Build full source code
		$finalCode = $this->buildSourceCode($headerCode, $sourceCode);

		// Create temp file to check syntax
		$tempFile = $viewFolder."/view.temp.php";
		fileManager::create($tempFile, $finalCode, TRUE);
		$syntaxCheck = phpParser::syntax($tempFile);
		fileManager::remove($tempFile);
		
		// Update single dependencies and metrics
		$this->updateSingleDependencies($finalCode);
		$this->updateMetrics($finalCode);

		// Update File
		$this->vcs->updateItem($itemID);
		$file_status = fileManager::put($viewFolder."/view.php", $finalCode);
		
		// If there was a syntax error, show the error
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;
		
		return $file_status;
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
		$sourceCode = fileManager::get(systemRoot.paths::getDevRsrcPath()."/applications/appViewDefault.inc");
		
		// Clear Delimiters
		$sourceCode = phpParser::unwrap($sourceCode);
		
		// Wrap to section
		return trim($sourceCode);
	}
	
	/**
	 * Update the view's information, source dependencies.
	 * 
	 * @param	array	$sdk_dependencies
	 * 		The view's dependencies to the Redback SDK.
	 * 
	 * @param	array	$app_dependencies
	 * 		The view's dependencies to the Application source.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($sdk_dependencies = array(), $app_dependencies = array())
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load index
		$parser = new DOMParser();
		try
		{
			$parser->load($viewFolder."/index.xml", FALSE);
			$sdk_dep = $parser->evaluate("//sdk_dependencies")->item(0);
			$app_dep = $parser->evaluate("//app_dependencies")->item(0);
		}
		catch (Exception $ex)
		{
			// Create index
			fileManager::create($viewFolder."/index.xml", "");
			
			// Add nodes
			$root = $parser->create("view", "", $this->name);
			$parser->append($root);
			$parser->save($viewFolder."/index.xml");
			
			$sdk_dep = $parser->create("sdk_dependencies");
			$parser->append($root, $sdk_dep);
			$app_dep = $parser->create("app_dependencies");
			$parser->append($root, $app_dep);
			$parser->update();
		}
		
		// Update Dependencies
		$parser->innerHTML($sdk_dep, "");
		$parser->innerHTML($app_dep, "");
		$this->setDependencies($sdk_dependencies, $app_dependencies);
		foreach ($this->sdk_dependencies as $library => $objects)
			foreach ($objects as $object)
			{
				$entry = $parser->create("dependency");
				$parser->attr($entry, "library", $library);
				$parser->attr($entry, "object", $object);
				$parser->append($sdk_dep, $entry);
			}
		foreach ($this->app_dependencies as $object)
		{
			$entry = $parser->create("dependency");
			$parser->attr($entry, "library", srcPackage::LIB_NAME);
			$parser->attr($entry, "object", $object);
			$parser->append($app_dep, $entry);
		}
		
		// Update Index file
		$status = $parser->update();
		
		// Update vcs item
		if ($status)
			$this->vcs->updateItem($itemID);
		
		return $status;
	}
	
	/**
	 * Sets the dependencies as a normalized dependency array.
	 * 
	 * @param	array	$sdk_dependencies
	 * 		The view's dependencies to the Redback SDK.
	 * 
	 * @param	array	$app_dependencies
	 * 		The view's dependencies to the Application source.
	 * 
	 * @return	void
	 */
	private function setDependencies($sdk_dependencies, $app_dependencies)
	{
		// Init SDK dependencies
		$this->sdk_dependencies = array();
		
		// Gather up
		foreach ($sdk_dependencies as $key => $val)
		{
			// Get Parts
			$parts = explode(",", $key);
			$library = $parts[0];
			$object = $parts[1];
			
			// Build array
			$this->sdk_dependencies[$library][] = $object;
		}
		
		// Init APP dependencies
		$this->app_dependencies = array();
		
		// Gather up
		foreach ($app_dependencies as $key => $val)
			$this->app_dependencies[] = $key;
	}
	
	/**
	 * Builds the php code header.
	 * 
	 * @return	string
	 * 		The php code header.
	 */
	private function buildHeader()
	{
		$path = systemRoot.paths::getDevRsrcPath()."/applications/headers/view.inc";
		$header = fileManager::get($path);
		$header = phpParser::unwrap($header);
		
		// Add application id
		$header .= "\n\n".phpParser::comment("Set Application ID", $multi = FALSE)."\n";
		$header .= phpParser::variable("appID").' = '.$this->app->getID().";\n\n";
		
		// Init application
		$header .= phpParser::comment("Init Application and Application literal", $multi = FALSE)."\n";
		$header .= "application::init(".$this->app->getID().");\n";
		
		// Secure importer
		$header .= phpParser::comment("Secure Importer", $multi = FALSE)."\n";
		$header .= "importer::secure(TRUE);\n\n";
		
		// Add SDK dependencies
		$header .= phpParser::comment("Import SDK Packages", $multi = FALSE)."\n";
		foreach ($this->sdk_dependencies as $library => $packages)
			foreach ($packages as $package)
				$header .= 'importer::import("'.$library.'", "'.$package.'");'."\n";
		
		// Add Application dependencies
		$header .= "\n".phpParser::comment("Import APP Packages", $multi = FALSE)."\n";
		foreach ($this->app_dependencies as $package)
			$header .= 'application::import("'.$package.'");'."\n";
		
		// Remove last line feed
		$header = preg_replace('/\n$/', '', $header);
		
		return $header;
	}
	
	/**
	 * Builds the source code with the given header and body.
	 * 
	 * @param	string	$header
	 * 		The header code.
	 * 
	 * @param	string	$viewCode
	 * 		The body code.
	 * 
	 * @return	string
	 * 		The final source code.
	 */
	private function buildSourceCode($header, $viewCode)
	{
		// Build Sections
		$headerCodeSection = phpCoder::section($header, "header");
		$viewCodeSection = phpCoder::section($viewCode, "view");
		
		// Merge all pieces
		$completeCode = trim($headerCodeSection.$viewCodeSection);

		// Complete php code
		return phpParser::wrap($completeCode);
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
	 * @return	array
	 * 		An array of all dependencies including the use 'path' and use 'alias'.
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
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
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
		$this->vcs->updateItem($itemID);
		return $parser->save($viewFolder."/dependencies.xml");
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
		$predefinedDependencies["AEL"]["Platform"] = 1;
		$predefinedDependencies["UI"]["Html"] = 1;
		
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		// Get single dependencies
		$singleDependencies = $this->getSingleDependencies($viewFolder."/dependencies.xml", FALSE);
		
		// Get package dependencies
		$dependenciesRaw = $this->getDependencies();
		$appDependencies = array();
		$appDependencies[srcPackage::LIB_NAME] = $dependenciesRaw['app'];
		$packageDependencies = array_merge($dependenciesRaw['sdk'], $appDependencies);
		
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
	 * @return	array
	 * 		An array of all metrics data. Including:
	 * 		LOC
	 * 		CLOC
	 * 		SLOC-P
	 * 		NOF
	 * 		LOC-PF.
	 * 		For more information on explanation, see the DEV\Tools\phpParser.
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
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
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
		$this->vcs->updateItem($itemID);
		return $parser->save($viewFolder."/metrics.xml");
	}
	
	/**
	 * Gets the view's style code.
	 * 
	 * @param	boolean	$normalCss
	 * 		If true, return the parsed css code, else it returns the initial scss.
	 * 		It is FALSE by default.
	 * 
	 * @return	string
	 * 		The style css.
	 */
	public function getCSS($normalCss = FALSE)
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);

		// Get scss
		$scss = fileManager::get($viewFolder."/style.scss");
		if (empty($scss) || $normalCss)
		{
			// If the scss is empty or the user requested the css specificly
			return fileManager::get($viewFolder."/style.css");
		}
		
		// Return scss
		return $scss;
	}
	
	/**
	 * Updates the view's css code.
	 * 
	 * @param	string	$code
	 * 		The view's new css code.
	 * 
	 * @return	void
	 */
	public function updateCSS($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->updateItem($itemID);
		
		// Update scss and css
		$scss = cssParser::clear($code);
		$status1 = fileManager::put($viewFolder."/style.scss", $scss);
		
		// Compile scss to css
		$css = scssParser::toCSS($scss);
		$status2 = fileManager::put($viewFolder."/style.css", $css);
		
		return $status1 && $status2;
	}
	
	/**
	 * Gets the view's javascript code.
	 * 
	 * @return	string
	 * 		The view's javascript code.
	 */
	public function getJS()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load script
		return fileManager::get($viewFolder."/script.js");
	}
	
	/**
	 * Updates the view's javascript code.
	 * 
	 * @param	string	$code
	 * 		The view's new javascript code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateJS($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->updateItem($itemID);
		
		// Update File
		$code = phpParser::clear($code);
		return fileManager::put($viewFolder."/script.js", $code);
	}
	
	/**
	 * Gets the view's html content.
	 * 
	 * @return	string
	 * 		The html content.
	 */
	public function getHTML()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load script
		return fileManager::get($viewFolder."/view.html");
	}
	
	/**
	 * Updates the view's html content.
	 * 
	 * @param	string	$html
	 * 		The view's new html content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateHTML($html = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->updateItem($itemID);

		// Update File
		$html = phpParser::clear($html);
		return fileManager::put($viewFolder."/view.html", $html);
	}
	
	/**
	 * Runs the view from the trunk.
	 * 
	 * @return	mixed
	 * 		The view result.
	 * 
	 * @throws	Exception
	 * 
	 * @deprecated	Use loadFromTrunk() instead.
	 */
	public function run()
	{
		return $this->loadFromTrunk();
	}
	
	/**
	 * Runs the view from the trunk.
	 * 
	 * @return	mixed
	 * 		The view result.
	 * 
	 * @throws	Exception
	 */
	public function loadFromTrunk()
	{
		// Get item path
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		//echo "load from ".$viewFolder."\n";return FALSE;
		// Run - Require
		return importer::req($viewFolder."/view.php", FALSE);
	}
	
	/**
	 * Runs the view from the working branch.
	 * 
	 * @return	mixed
	 * 		The view result.
	 * 
	 * @throws	Exception
	 */
	public function loadFromWBranch()
	{
		// Get item path
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemBranchPath($itemID);

		// Run - Require
		return importer::req($viewFolder."/view.php", FALSE);
	}
	
	/**
	 * Remove the view from the application.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove view from index
		$vMan = new appViewManager($this->app->getID());
		$status = $vMan->removeView($this->folder, $this->name);
		
		// If delete is successful, delete from vcs
		if ($status === TRUE)
		{
			// Remove object from vcs
			$itemID = $this->getItemID();
			$this->vcs->deleteItem($itemID);
		}
		
		return $status;
	}
	
	/**
	 * Gets the view dependencies.
	 * 
	 * @return	void
	 */
	public function getDependencies()
	{
		$this->loadInfo();
		$dependencies = array();
		$dependencies['sdk'] = $this->sdk_dependencies;
		$dependencies['app'] = $this->app_dependencies;
		
		return $dependencies;
	}
	
	/**
	 * Loads the view's info from the index file.
	 * 
	 * @return	void
	 */
	private function loadInfo()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load index info
		$parser = new DOMParser();
		try
		{
			$parser->load($viewFolder."/index.xml", FALSE);
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		// Load SDK Dependencies
		$this->sdk_dependencies = array();
		$dependencyEntries = $parser->evaluate("//sdk_dependencies/dependency");
		foreach ($dependencyEntries as $entry)
		{
			$library = $parser->attr($entry, "library");
			$object = $parser->attr($entry, "object");
			$this->sdk_dependencies[$library][] = $object;
		}
		
		// Load APP Dependencies
		$this->app_dependencies = array();
		$dependencyEntries = $parser->evaluate("//app_dependencies/dependency");
		foreach ($dependencyEntries as $entry)
		{
			$object = $parser->attr($entry, "object");
			$this->app_dependencies[] = $object;
		}
	}
	
	/**
	 * Gets the vcs item's id.
	 * 
	 * @return	string
	 * 		The item's hash id.
	 */
	private function getItemID()
	{
		$oID = "v_".(empty($this->folder) ? "" : $this->folder."_").$this->name;
		return $this->app->getItemID($oID);
	}
	
	/**
	 * Creates the view's inner file structure.
	 * 
	 * @return	void
	 */
	private function createViewStructure()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Create view folder
		folderManager::create($viewFolder);
		
		// Create view information index
		fileManager::create($viewFolder."/index.xml", "");
		
		$parser = new DOMParser();
		$root = $parser->create("view", "", $this->name);
		$parser->append($root);
		$parser->save($viewFolder."/index.xml");
		
		$sdk_dependencies = $parser->create("sdk_dependencies");
		$parser->append($root, $sdk_dependencies);
		$app_dependencies = $parser->create("app_dependencies");
		$parser->append($root, $app_dependencies);
		$parser->update();
		
		// Create files
		fileManager::create($viewFolder."/view.html", "");
		fileManager::create($viewFolder."/view.php", "");
		fileManager::create($viewFolder."/style.css", "");
		fileManager::create($viewFolder."/script.js", "");
	}
}
//#section_end#
?>