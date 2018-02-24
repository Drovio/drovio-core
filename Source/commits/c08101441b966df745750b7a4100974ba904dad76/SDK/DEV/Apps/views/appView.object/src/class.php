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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Apps\application;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Version\vcs;
use \DEV\Resources\paths;

/**
 * Application View
 * 
 * Manages an application view object manager.
 * 
 * @version	1.1-2
 * @created	August 22, 2014, 13:40 (EEST)
 * @revised	September 27, 2014, 11:31 (EEST)
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
	 * @param	string	$name
	 * 		The view name.
	 * 		For creating new views, leave this empty.
	 * 
	 * @return	void
	 */
	public function __construct($appID, $name = "")
	{
		// Init application
		$this->app = new application($appID);
		
		// Init vcs
		$this->vcs = new vcs($appID);
		
		// Set name
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
		$status = $this->app->addObjectIndex("views", "view", $viewName);
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = application::VIEWS_FOLDER;
		$itemName = $viewName.".".self::FILE_TYPE;
		$viewFolder = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create view structure
		$this->createViewStructure();
		
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
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);

		// Remove temp file
		fileManager::remove($tempFile);
		
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;

		// Update File
		$this->vcs->updateItem($itemID);
		return fileManager::put($viewFolder."/view.php", $finalCode);
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
		$sourceCode = fileManager::get(systemRoot.paths::getDevRsrcPath()."/templates/appViewDefault.inc");
		
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
		$path = systemRoot.paths::getDevRsrcPath()."/headers/application/view.inc";
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
	 * Gets the view's style code.
	 * 
	 * @return	string
	 * 		The style css.
	 */
	public function getCSS()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load style
		return fileManager::get($viewFolder."/style.css");
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
		
		// Update File
		$code = phpParser::clear($code);
		return fileManager::put($viewFolder."/style.css", $code);
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
		// Create object index
		$status = $this->app->removeObjectIndex("views", "view", $this->name);
		
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
		return $this->app->getItemID("v_".$this->name);
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