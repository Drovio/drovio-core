<?php
//#section#[header]
// Namespace
namespace DEV\Apps\components;

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
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Version", "vcs");

use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Apps\application;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Version\vcs;

/**
 * Application View
 * 
 * Represents an application view object manager.
 * 
 * @version	{empty}
 * @created	April 6, 2014, 0:23 (EEST)
 * @revised	April 7, 2014, 16:34 (EEST)
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
	 * Constructor. Initializes the object's variables.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$name
	 * 		The view name.
	 * 
	 * @return	void
	 */
	public function __construct($appID, $name = "")
	{
		// Init application
		$this->app = new application($appID);
		
		// Init vcs
		$repository = $this->app->getRepository();
		$this->vcs = new vcs($repository);
		
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
	 * 		The php code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updatePHPCode($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Clear Code
		$code = phpParser::clear($code);
		$code = phpCoder::safe($code);

		// Build header
		$headerCode = $this->buildHeader();
		
		// Build full source code
		$finalCode = $this->buildSourceCode($headerCode, $code);
		
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
	 * Builds the php code header.
	 * 
	 * @return	string
	 * 		The php code header.
	 */
	private function buildHeader()
	{
		$path = systemRoot.paths::getDevRsrcPath()."/headers/appView.inc";
		$header = fileManager::get($path);
		$header = phpParser::unwrap($header);
		
		// Add application id
		$header .= "\n\n".phpParser::variable("appID").' = '.$this->app->getID().";\n";
		
		// Initialize importer
		$header .= "importer::initApp(".$this->app->getID().");\n";
		
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
	 * 		The css code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
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
	 * 		The view's javascript code.
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
	 * 		The html content.
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
	 */
	public function run()
	{
		// Get item path
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->getItemTrunkPath($itemID);
		
		// Run - Require
		return importer::req($viewFolder."/view.php", FALSE);
	}
	
	/**
	 * Gets the vcs item's id.
	 * 
	 * @return	string
	 * 		The item's hash id.
	 */
	private function getItemID()
	{
		return "v".md5($this->name);
	}
	
	/**
	 * Creates the view inner file structure.
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
		
		// Create files
		fileManager::create($viewFolder."/view.html", "");
		fileManager::create($viewFolder."/view.php", "");
		fileManager::create($viewFolder."/style.css", "");
		fileManager::create($viewFolder."/script.js", "");
	}
}
//#section_end#
?>