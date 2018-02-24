<?php
//#section#[header]
// Namespace
namespace DEV\Websites\pages;

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
 * @package	Websites
 * @namespace	\pages
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "settingsManager");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Websites", "pages/wsPageManager");
importer::import("DEV", "Tools", "parsers/phpParser");
importer::import("DEV", "Tools", "coders/phpCoder");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\settingsManager;
use \DEV\Websites\website;
use \DEV\Websites\pages\wsPageManager;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Version\vcs;
use \DEV\Resources\paths;

/**
 * Website Page
 * 
 * It handles the entire page object, including source, view and javascript.
 * 
 * @version	4.0-2
 * @created	September 10, 2014, 20:08 (EEST)
 * @updated	January 4, 2015, 0:48 (EET)
 */
class wsPage
{
	/**
	 * The object type / extension
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "page";
	
	/**
	 * The website object
	 * 
	 * @type	website
	 */
	private $website;
	
	/**
	 * The object name.
	 * 
	 * @type	string
	 */
	private $name;
	/**
	 * The page folder. Empty for root pages.
	 * 
	 * @type	string
	 */
	private $folder;
	
	/**
	 * The array of Web SDK dependencies.
	 * 
	 * @type	array
	 */
	private $wsdk_dependencies;
	
	/**
	 * The array of Website Source dependencies.
	 * 
	 * @type	array
	 */
	private $ws_dependencies;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;

	/**
	 * Constructor. Initializes the object's variables.
	 * 
	 * @param	integer	$id
	 * 		The website id.
	 * 
	 * @param	string	$folder
	 * 		The page folder.
	 * 
	 * @param	string	$name
	 * 		The page name.
	 * 		For creating new page, leave this empty.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($id, $folder = "", $name = "")
	{
		// Init website
		$this->website = new website($id);
		
		// Init vcs
		$this->vcs = new vcs($id);
		
		// Set name
		$this->folder = $folder;
		$this->name = $name;
	}
	
	/**
	 * Creates a new website page.
	 * 
	 * @param	string	$name
	 * 		The page name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
			
		// Set pagename
		$this->name = $name;
		
		// Create object index
		$pMan = new wsPageManager($this->website->getID());
		$status = $pMan->createPage($this->folder, $this->name);
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = website::PAGES_FOLDER."/".$this->folder."/";
		$itemName = $this->name.".".self::FILE_TYPE;
		$folder = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create page object inner structure
		$this->createStructure();
		
		// Update default php code and set dependencies
		$this->updatePHPCode();
		$sdk_dependencies = array();
		$sdk_dependencies[] = "WUI,Website";
		$this->updateDependencies($sdk_dependencies);
		
		return TRUE;
	}
	
	/**
	 * Get the page's php code.
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
		$folder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load php code
		$code = fileManager::get($folder."/page.php");
		
		if (!$full)
		{
			// Unwrap php code
			$code = phpParser::unwrap($code);
			$sections = phpCoder::sections($code);
			return $sections['page'];
		}
		else
			return $code;
	}
	
	/**
	 * Updates the page's php code.
	 * 
	 * @param	string	$code
	 * 		The page's php code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updatePHPCode($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->getItemTrunkPath($itemID);
		
		// Set source code (default if not given)
		$code = (trim($code) == "" ? $this->getDefaultSourceCode() : $code);
		
		// Clear code from unwanted characters
		$code = phpParser::clear($code);
		
		// Comment out dangerous commands
		$code = phpCoder::safe($code);

		// Build header
		$headerCode = $this->buildHeader();
		
		// Build full source code
		$finalCode = $this->buildSourceCode($headerCode, $code);
		
		// Create temp file to check syntax
		$tempFile = $viewFolder."/page.temp.php";
		fileManager::create($tempFile, $finalCode, TRUE);
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);

		// Remove temp file
		fileManager::remove($tempFile);
		
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;

		// Update File
		$this->vcs->updateItem($itemID);
		return fileManager::put($folder."/page.php", $finalCode);
	}
	
	/**
	 * Builds the php code header.
	 * 
	 * @return	string
	 * 		The php code header.
	 */
	private function buildHeader()
	{
		$path = systemRoot.paths::getDevRsrcPath()."/websites/headers/pageView.inc";
		$header = fileManager::get($path);
		$header = phpParser::unwrap($header)."\n\n";
		
		// Add SDK dependencies
		$header .= phpParser::comment("Import Web SDK Packages", $multi = FALSE)."\n";
		foreach ($this->wsdk_dependencies as $library => $packages)
			foreach ($packages as $package)
				$header .= 'importer::import("'.$library.'", "'.$package.'");'."\n";
		
		// Add Website dependencies
		$header .= "\n".phpParser::comment("Import Website Packages", $multi = FALSE)."\n";
		foreach ($this->ws_dependencies as $library => $packages)
			$header .= 'website::import("'.$library.'", "'.$package.'");'."\n";
		
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
	 * @param	string	$code
	 * 		The body code.
	 * 
	 * @return	string
	 * 		The final source code.
	 */
	private function buildSourceCode($header, $code)
	{
		// Build Sections
		$headerCodeSection = phpCoder::section($header, "header");
		$mainCodeSection = phpCoder::section($code, "page");
		
		// Merge all pieces
		$completeCode = trim($headerCodeSection.$mainCodeSection);
			
		// Complete php code
		return phpParser::wrap($completeCode);
	}
	
	/**
	 * Gets the page's default source code.
	 * 
	 * @return	string
	 * 		The default template source code.
	 */
	private function getDefaultSourceCode()
	{
		// Get Default Module Code
		$sourceCode = fileManager::get(systemRoot.paths::getDevRsrcPath()."/websites/pageViewDefault.inc");
		
		// Clear Delimiters
		$sourceCode = phpParser::unwrap($sourceCode);
		
		// Wrap to section
		return trim($sourceCode);
	}
	
	/**
	 * Gets the page's style code.
	 * 
	 * @return	string
	 * 		The style css.
	 */
	public function getCSS()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load style
		return fileManager::get($folder."/style.css");
	}
	
	/**
	 * Updates the page's css code.
	 * 
	 * @param	string	$code
	 * 		The page's new css code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateCSS($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->updateItem($itemID);
		
		// Update File
		$code = phpParser::clear($code);
		return fileManager::put($folder."/style.css", $code);
	}
	
	/**
	 * Gets the page's javascript code.
	 * 
	 * @return	string
	 * 		The page's javascript code.
	 */
	public function getJS()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load script
		return fileManager::get($folder."/script.js");
	}
	
	/**
	 * Updates the page's javascript code.
	 * 
	 * @param	string	$code
	 * 		The page's new javascript code
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateJS($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->updateItem($itemID);
		
		// Update File
		$code = phpParser::clear($code);
		return fileManager::put($folder."/script.js", $code);
	}
	
	/**
	 * Gets the page's html content.
	 * 
	 * @return	string
	 * 		The html content.
	 */
	public function getHTML()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load script
		return fileManager::get($folder."/page.html");
	}
	
	/**
	 * Updates the page's html content.
	 * 
	 * @param	string	$html
	 * 		The page's new html content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateHTML($html = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->updateItem($itemID);

		// Update File
		$html = phpParser::clear($html);
		return fileManager::put($folder."/page.html", $html);
	}
	
	/**
	 * Remove the page from the website.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove page from map
		$wpm = new wsPageManager($this->website->getID());
		$status = $wpm->removePage($this->folder, $this->name);
		
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
	 * Update the page's information, source dependencies.
	 * 
	 * @param	array	$sdk_dependencies
	 * 		The page's dependencies to the Web SDK.
	 * 		The array's format is in the key. Each key is like [library,package].
	 * 
	 * @param	array	$ws_dependencies
	 * 		The page's dependencies to the website source.
	 * 		The array's format is in the key. Each key is like [library,package].
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateDependencies($sdk_dependencies = array(), $ws_dependencies = array())
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Load index
		$parser = new DOMParser();
		try
		{
			$parser->load($viewFolder."/index.xml", FALSE);
			$sdk_dep = $parser->evaluate("//wsdk_dependencies")->item(0);
			$ws_dep = $parser->evaluate("//ws_dependencies")->item(0);
		}
		catch (Exception $ex)
		{
			// Create index
			fileManager::create($viewFolder."/index.xml", "");
			
			// Add nodes
			$root = $parser->create("view", "", $this->name);
			$parser->append($root);
			$parser->save($viewFolder."/index.xml");
			
			$sdk_dep = $parser->create("wsdk_dependencies");
			$parser->append($root, $sdk_dep);
			$ws_dep = $parser->create("ws_dependencies");
			$parser->append($root, $ws_dep);
			$parser->update();
		}
		
		// Update Dependencies
		$parser->innerHTML($sdk_dep, "");
		$parser->innerHTML($ws_dep, "");
		$this->setDependencies($sdk_dependencies, $ws_dependencies);
		foreach ($this->wsdk_dependencies as $library => $objects)
			foreach ($objects as $object)
			{
				$entry = $parser->create("dependency");
				$parser->attr($entry, "library", $library);
				$parser->attr($entry, "object", $object);
				$parser->append($sdk_dep, $entry);
			}
			
		foreach ($this->ws_dependencies as $library => $objects)
			foreach ($objects as $object)
			{
				$entry = $parser->create("dependency");
				$parser->attr($entry, "library", $library);
				$parser->attr($entry, "object", $object);
				$parser->append($ws_dep, $entry);
			}
		
		// Update Index file
		$status = $parser->update();
		
		// Update vcs item
		if ($status)
			$this->vcs->updateItem($itemID);
		
		return $status;
	}
	
	/**
	 * Gets the page's dependencies.
	 * 
	 * @return	array
	 * 		An array of web sdk and website source dependencies:
	 * 		dependencies['sdk']
	 * 		dependencies['ws']
	 */
	public function getDependencies()
	{
		$this->loadInfo();
		$dependencies = array();
		$dependencies['sdk'] = $this->wsdk_dependencies;
		$dependencies['ws'] = $this->ws_dependencies;
		
		return $dependencies;
	}
	
	/**
	 * Update page settings.
	 * 
	 * @param	array	$settings
	 * 		An array of all page settings by name and value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateSettings($settings = array())
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Initialize settings manager
		$pageSettings = new settingsManager($viewFolder, "settings", FALSE);
		$pageSettings->create();
		
		// Update settings
		foreach ($settings as $name => $value)
			if (!empty($name) && !is_numeric($name))
				$pageSettings->set($name, (empty($value) ? NULL : $value));
		
		return TRUE;
	}
	
	/**
	 * Get all page settings.
	 * 
	 * @return	array
	 * 		All page settings in an array by name and value.
	 * 		All settings names are upper case.
	 */
	public function getSettings()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Initialize settings manager
		$pageSettings = new settingsManager($viewFolder, "settings", FALSE);
		$pageSettings->create();
		
		// Return all settings
		return $pageSettings->get();
	}
	
	/**
	 * Loads the page's info from the index file.
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
		$this->wsdk_dependencies = array();
		$dependencyEntries = $parser->evaluate("//wsdk_dependencies/dependency");
		foreach ($dependencyEntries as $entry)
		{
			$library = $parser->attr($entry, "library");
			$object = $parser->attr($entry, "object");
			$this->wsdk_dependencies[$library][] = $object;
		}
		
		// Load APP Dependencies
		$this->ws_dependencies = array();
		$dependencyEntries = $parser->evaluate("//ws_dependencies/dependency");
		foreach ($dependencyEntries as $entry)
		{
			$library = $parser->attr($entry, "library");
			$object = $parser->attr($entry, "object");
			$this->ws_dependencies[$library][] = $object;
		}
	}
	
	/**
	 * Sets the dependencies as a normalized dependency array.
	 * 
	 * @param	array	$wsdk_dependencies
	 * 		The page's dependencies to the Web SDK.
	 * 		The array's format is in the key. Each key is like [library,package].
	 * 
	 * @param	array	$ws_dependencies
	 * 		The page's dependencies to the website source.
	 * 		The array's format is in the key. Each key is like [library,package].
	 * 
	 * @return	void
	 */
	private function setDependencies($wsdk_dependencies, $ws_dependencies)
	{
		// Init SDK dependencies
		$this->wsdk_dependencies = array();
		
		// Gather up
		foreach ($wsdk_dependencies as $key => $val)
		{
			// Get Parts
			$parts = explode(",", $key);
			$library = $parts[0];
			$object = $parts[1];
			
			// Build array
			$this->wsdk_dependencies[$library][] = $object;
		}
		
		// Gather up
		foreach ($ws_dependencies as $key => $val)
		{
			// Get Parts
			$parts = explode(",", $key);
			$library = $parts[0];
			$object = $parts[1];
			
			// Build array
			$this->ws_dependencies[$library][] = $object;
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
		return $this->website->getItemID("p_".$this->name);
	}
	
	/**
	 * Creates the page's inner file structure.
	 * 
	 * @return	void
	 */
	private function createStructure()
	{
		// Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->getItemTrunkPath($itemID);
		
		// Create view folder
		folderManager::create($folder);
		
		// Create view information index
		fileManager::create($folder."/index.xml", "");
		
		$parser = new DOMParser();
		$root = $parser->create("page", "", $this->name);
		$parser->append($root);
		$parser->save($folder."/index.xml");
		
		$wsdk_dependencies = $parser->create("wsdk_dependencies");
		$parser->append($root, $wsdk_dependencies);
		$ws_dependencies = $parser->create("ws_dependencies");
		$parser->append($root, $ws_dependencies);
		$parser->update();
		
		// Create files
		fileManager::create($folder."/page.html", "");
		fileManager::create($folder."/page.php", "");
		fileManager::create($folder."/style.css", "");
		fileManager::create($folder."/script.js", "");
	}
}
//#section_end#
?>