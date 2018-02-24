<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ajax;

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
 * @namespace	\components\ajax
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::ajaxManager");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::coders::phpCoder");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\components\ajaxManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\coders\phpCoder;
use \API\Developer\misc\vcs;
use \API\Developer\projects\project;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * AJAX Page Manager
 * 
 * Manages all ajax pages in repositories.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 16:08 (EEST)
 * @revised	January 14, 2014, 12:27 (EET)
 */
class ajaxPage
{
	/**
	 * The page's full parent directory
	 * 
	 * @type	string
	 */
	private $ajaxDirectory = "";
	
	/**
	 * The page name.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * The vcs object manager.
	 * 
	 * @type	vcs
	 */
	private $vcs;

	/**
	 * Constructs the page object.
	 * 
	 * @param	string	$pageName
	 * 		The page's name.
	 * 
	 * @param	string	$directory
	 * 		The page's full directory name (separated by "/").
	 * 
	 * @return	void
	 */
	public function __construct($pageName = "", $directory = "")
	{
		// Set Object variables
		$this->name = $pageName;
		$this->ajaxDirectory = $directory;
		
		// Initialize new vcs
		$repository = project::getRepository(1);
		$this->vcs = new vcs($repository, $includeRelease = FALSE);
		
		// Create Structure (if not exists)
		$this->vcs->createStructure();
	}
	
	/**
	 * Creates a new page.
	 * 
	 * @param	string	$name
	 * 		The new page's name.
	 * 
	 * @param	string	$directory
	 * 		The full directory name.
	 * 
	 * @return	ajaxPage
	 * 		{description}
	 */
	public function create($name, $directory)
	{
		$this->name = $name;
		$this->ajaxDirectory = $directory;

		// Create Index 
		$proceed = $this->createIndex();
		if (!$proceed)
			return FALSE;
			
		// Create new vcs object
		$itemID = $this->getItemID();
		$itemPath = "/Ajax/".$this->ajaxDirectory."/";
		$itemName = $this->name.".php";
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Update item
		$this->update();
		
		return $this;
	}
	
	/**
	 * Updates the page's source code.
	 * 
	 * @param	string	$code
	 * 		The new source code.
	 * 
	 * @return	ajaxPage
	 * 		{description}
	 */
	public function update($code = "")
	{
		// Update new vcs item info
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		$finalCode = $this->buildSourceCode($code);
		
		// Create temp file to check syntax
		$tempFile = $itemTrunkPath.".temp";
		fileManager::create($tempFile, $finalCode, TRUE);
		
		// Check Syntax
		$syntaxCheck = phpParser::syntax($tempFile);

		// Remove temp file
		fileManager::remove($tempFile);
		
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;
		
		// Update php code
		return (fileManager::create($itemTrunkPath, $finalCode, TRUE) !== FALSE);
	}
	
	/**
	 * Export this page to ajax for immediate use.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function export()
	{
		// Update new vcs item info
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		$destinationFile = systemRoot."/ajax/".$this->ajaxDirectory."/".$this->name.".php";
		fileManager::create($destinationFile, "", TRUE);
		return fileManager::copy($itemTrunkPath, $destinationFile);
	}
	
	/**
	 * Returns the page's source code.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getSourceCode()
	{
		// Update new vcs item info
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		$source = fileManager::get($itemTrunkPath);
		$sections = phpCoder::sections($source);

		// Return source code
		return trim($sections["code"]);
	}
	
	/**
	 * Runs the ajax page from the trunk.
	 * 
	 * @return	void
	 */
	public function run()
	{
		// Get item trunk path
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		// Run
		importer::req($itemTrunkPath, FALSE, TRUE);
	}
	
	/**
	 * Builds the source code with its headers.
	 * 
	 * @param	string	$code
	 * 		The source code alone.
	 * 
	 * @return	string
	 * 		The full source code.
	 */
	protected function buildSourceCode($code)
	{
		// Build Class Header
		$headerCode = $this->buildHeader();
		
		// Build Sections
		$headerCodeSection = phpCoder::section($headerCode, "header");
		$classCodeSection = phpCoder::section($code, "code");
		
		// Merge all pieces
		$completeCode = trim($headerCodeSection.$classCodeSection);
			
		// Complete php code
		return phpParser::wrap($completeCode);
	}
	
	/**
	 * Builds the source code's header.
	 * 
	 * @return	string
	 * 		The page's header code.
	 */
	private function buildHeader()
	{  
		$path = systemRoot.paths::getDevRsrcPath()."/Content/Ajax/Headers/private.inc";
		$header = fileManager::get($path);
		$header = phpParser::unwrap($header);
		
		return $header;
	}
	
	/**
	 * Creates the map index for the page.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	private function createIndex()
	{
		// Library Path
		$libPath = ajaxManager::updateMapFilepath();
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($libPath, TRUE);
		
		$base = $parser->evaluate("//map")->item(0);
		
		if (empty($this->ajaxDirectory))
			$baseDir = $base;
		else
		{
			// If parent directory given, search for it
			$pdir = explode("/", $this->ajaxDirectory);
			$q_dir = "dir[@name='".implode("']/dir[@name='", $pdir)."']";
			$baseDir = $parser->evaluate($q_dir, $base)->item(0);
			if (is_null($baseDir))
				throw new Exception("Parent directory '$this->ajaxDirectory' doesn't exist.");
		}
		
		// Create root directory (if not already exists)
		$page = $parser->evaluate("page[@name='$this->name']", $baseDir)->item(0);
		if (is_null($page))
		{
			$page = $parser->create("page");
			$parser->attr($page, "name", $this->name);
			$parser->append($baseDir, $page);
			return $parser->save(systemRoot.$libPath, "", TRUE);
		}
		
		return FALSE;
	}
	
	/**
	 * Get the item id for the vcs.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	private function getItemID()
	{
		return "ajx".hash("md5", $this->ajaxDirectory."_".$this->name, FALSE);
	}
	
	/**
	 * Get all pages in a given directory.
	 * 
	 * @param	string	$directory
	 * 		The full directory name.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\ajaxManager::getPages()
	 */
	public static function getPages($directory)
	{
		return ajaxManager::getPages($directory);
	}
}
//#section_end#
?>