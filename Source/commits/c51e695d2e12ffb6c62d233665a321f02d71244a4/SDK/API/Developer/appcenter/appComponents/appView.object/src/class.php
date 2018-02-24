<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter\appComponents;

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
 * @namespace	\appcenter\appComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\appcenter\application;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * Application View
 * 
 * Represents an application view object manager.
 * 
 * @version	{empty}
 * @created	June 19, 2013, 14:04 (EEST)
 * @revised	November 3, 2013, 12:23 (EET)
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
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $appID;
	
	/**
	 * The view name.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * The application's vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * The developer's application path.
	 * 
	 * @type	string
	 */
	private $devPath;
	
	/**
	 * Constructor. Initializes the object's variables.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	vcs	$vcs
	 * 		The application's vcs object manager.
	 * 
	 * @param	string	$devPath
	 * 		The application path.
	 * 
	 * @param	string	$name
	 * 		The view name. It can be empty in case of new.
	 * 
	 * @return	void
	 */
	public function __construct($appID, $vcs, $devPath, $name = "")
	{
		// Put your constructor method code here.
		$this->appID = $appID;
		$this->vcs = $vcs;
		$this->devPath = $devPath;
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
		// Set view name
		$this->name = $viewName;
		
		// Create object index
		application::addObjectIndex($this->devPath, "views", "view", $viewName);
		
		// Create vcs object
		$itemID = $this->getItemID();
		$viewFolder = $this->vcs->createItem($itemID, application::VIEWS_FOLDER, $viewName.".".self::FILE_TYPE, $isFolder = TRUE);
		
		// Create view structure
		folderManager::create($viewFolder);
		$this->createViewStructure();
		
		return TRUE;
	}
	
	/**
	 * Get the view's php code.
	 * 
	 * @param	boolean	$full
	 * 		If true, returns the entire php code as is form the file.
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
		$code = phpParser::clearCode($code);
		$code = phpParser::safe($code);

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
		$path = systemRoot.paths::getDevRsrcPath()."/Content/appCenter/Headers/viewPrivate.inc";
		$header = fileManager::get($path);
		$header = phpParser::unwrap($header);
		
		// Add application id
		$header .= "\n".phpParser::variable("appID").' = '.$this->appID.";\n";
		
		// Initialize importer
		$header .= "importer::initApp(".$this->appID.");\n";
		
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
	 * 		The style code.
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
		$code = phpParser::clearCode($code);
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
		$code = phpParser::clearCode($code);
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
		return fileManager::put($viewFolder."/view.html", $html);
	}
	
	/**
	 * Gets the item's hash id.
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
		
		// Create files
		fileManager::create($viewFolder."/view.html", "");
		fileManager::create($viewFolder."/view.php", "");
		fileManager::create($viewFolder."/style.css", "");
		fileManager::create($viewFolder."/script.js", "");
	}
}
//#section_end#
?>