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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "coders::phpCoder");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Websites\website;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;
use \DEV\Version\vcs;
use \DEV\Resources\paths;



/**
 * {title}
 * 
 * {description}
 * 
 * @version	1.0-1
 * @created	September 10, 2014, 20:08 (EEST)
 * @revised	September 12, 2014, 22:18 (EEST)
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
	 * @param	string	$name
	 * 		The page name.
	 * 		For creating new page, leave this empty.
	 * 
	 * @return	void
	 */
	public function __construct($id, $name)
	{
		// Init website
		$this->website = new website($id);
		
		// Init vcs
		$this->vcs = new vcs($id);
		
		// Set name
		$this->name = $name;
	}
	
	/**
	 * Creates a new application view.
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
		$status = $this->website->addObjectIndex("pages", "page", $name);
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemID = $this->getItemID();
		$itemPath = website::PAGES_FOLDER;
		$itemName = $name.".".self::FILE_TYPE;
		$folder = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create page object inner structure
		$this->createStructure();
		
		return TRUE;
	}
	
	/**
	 * Get the object's php code.
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
	 * Updates the object's php code.
	 * 
	 * @param	string	$code
	 * 		The object's php code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updatePHPCode($code = "")
	{
		// Update and Load view folder
		$itemID = $this->getItemID();
		$folder = $this->vcs->getItemTrunkPath($itemID);
		
		// Clear Code
		$code = phpParser::clear($code);
		$code = phpCoder::safe($code);

		// Build header
		$headerCode = "";//$this->buildHeader();
		
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
	 * Gets the object's style code.
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
	 * Updates the object's css code.
	 * 
	 * @param	string	$code
	 * 		The object's new css code.
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
	 * Gets the object's javascript code.
	 * 
	 * @return	string
	 * 		The object's javascript code.
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
	 * Updates the object's javascript code.
	 * 
	 * @param	string	$code
	 * 		The object's new javascript code
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
	 * Gets the object's html content.
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
	 * Updates the object's html content.
	 * 
	 * @param	string	$html
	 * 		The object's new html content.
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
		// Create object index
		$status = $this->website->removeObjectIndex("pages", "page", $this->name);
		
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
	 * Gets the vcs item's id.
	 * 
	 * @return	string
	 * 		The item's hash id.
	 */
	private function getItemID()
	{
		return "p".md5($this->name);
	}
	
	/**
	 * Creates the object's inner file structure.
	 * 
	 * @return	boolean
	 * 		{description}
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
		
		//$sdk_dependencies = $parser->create("sdk_dependencies");
		//$parser->append($root, $sdk_dependencies);
		//$app_dependencies = $parser->create("app_dependencies");
		//$parser->append($root, $app_dependencies);
		//$parser->update();
		
		// Create files
		fileManager::create($folder."/page.html", "");
		fileManager::create($folder."/page.php", "");
		fileManager::create($folder."/style.css", "");
		fileManager::create($folder."/script.js", "");
	}
}
//#section_end#
?>