<?php
//#section#[header]
// Namespace
namespace DEV\Core\ajax;

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
 * @package	Core
 * @namespace	\ajax
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Core", "ajax/ajaxDirectory");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Resources", "paths");
importer::import("DEV", "Tools", "parsers/phpParser");
importer::import("DEV", "Tools", "coders/phpCoder");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Core\ajax\ajaxDirectory;
use \DEV\Core\coreProject;
use \DEV\Version\vcs;
use \DEV\Projects\project;
use \DEV\Resources\paths;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\coders\phpCoder;

/**
 * AJAX Page Manager
 * 
 * Manages all ajax pages in repositories.
 * 
 * @version	0.1-4
 * @created	March 31, 2014, 16:51 (EEST)
 * @updated	September 3, 2015, 12:52 (EEST)
 */
class ajaxPage
{
	/**
	 * The page's full parent directory
	 * 
	 * @type	string
	 */
	private $directory = "";
	
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
	 * Initializes the page object.
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
		$this->directory = $directory;
		
		// Initialize new vcs
		$this->vcs = new vcs(coreProject::PROJECT_ID);
	}
	
	/**
	 * Creates a new page.
	 * 
	 * @param	string	$name
	 * 		The new page's name.
	 * 
	 * @param	string	$directory
	 * 		The page's full directory name (separated by "/").
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $directory)
	{
		$this->name = $name;
		$this->directory = $directory;

		// Create Index 
		$proceed = $this->createIndex();
		if (!$proceed)
			return FALSE;
			
		// Create new vcs object
		$itemID = $this->getItemID();
		$itemPath = "/Ajax/".$this->directory."/";
		$itemName = $this->name.".php";
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Update item
		return $this->update();
	}
	
	/**
	 * Updates the page's source code.
	 * 
	 * @param	string	$code
	 * 		The page's new source code.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($code = "")
	{
		// Update new vcs item info
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Clear Code
		$code = phpParser::clear($code);
		$finalCode = $this->buildSourceCode($code);
		
		// Create temp file to check syntax
		$tempFile = $itemTrunkPath.".temp";
		fileManager::create($tempFile, $finalCode, TRUE);
		$syntaxCheck = phpParser::syntax($tempFile);
		fileManager::remove($tempFile);
		
		// Update php code
		$file_status = (fileManager::create($itemTrunkPath, $finalCode, TRUE) !== FALSE);
		
		// If there was a syntax error, show the error
		if ($syntaxCheck !== TRUE)
			return $syntaxCheck;
		
		return $file_status;
	}
	
	/**
	 * Returns the page's source code.
	 * 
	 * @return	string
	 * 		The page's source code.
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
	 * Remove the page from the repository.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Create Index 
		$proceed = $this->removeIndex();
		if (!$proceed)
			return FALSE;
			
		// Remove vcs object
		$itemID = $this->getItemID();
		return $this->vcs->deleteItem($itemID);
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
		$path = systemRoot.paths::getDevRsrcPath()."/core/headers/ajax.inc";
		$header = fileManager::get($path);
		return phpParser::unwrap($header);
	}
	
	/**
	 * Creates the map index for the page.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function createIndex()
	{
		// Library Path
		$libPath = ajaxDirectory::updateMapFilepath();
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($libPath, FALSE);
		
		$base = $parser->evaluate("//map")->item(0);
		
		if (empty($this->directory))
			$baseDir = $base;
		else
		{
			// If parent directory given, search for it
			$pdir = explode("/", $this->directory);
			$q_dir = "dir[@name='".implode("']/dir[@name='", $pdir)."']";
			$baseDir = $parser->evaluate($q_dir, $base)->item(0);
			if (is_null($baseDir))
				throw new Exception("Parent directory '$this->directory' doesn't exist.");
		}
		
		// Create root directory (if not already exists)
		$page = $parser->evaluate("page[@name='$this->name']", $baseDir)->item(0);
		if (is_null($page))
		{
			$page = $parser->create("page");
			$parser->attr($page, "name", $this->name);
			$parser->append($baseDir, $page);
			return $parser->update();
		}
		
		return FALSE;
	}
	
	/**
	 * Remove the page from the source index.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function removeIndex()
	{
		// Library Path
		$libPath = ajaxDirectory::updateMapFilepath();
		
		// Open Index File and remove entry
		$parser = new DOMParser();
		$parser->load($libPath, FALSE);
		
		$base = $parser->evaluate("//map")->item(0);
		if (empty($this->directory))
			$baseDir = $base;
		else
		{
			// If parent directory given, search for it
			$pdir = explode("/", $this->directory);
			$q_dir = "dir[@name='".implode("']/dir[@name='", $pdir)."']";
			$baseDir = $parser->evaluate($q_dir, $base)->item(0);
			if (is_null($baseDir))
				return FALSE;
		}
		
		// Create root directory (if not already exists)
		$page = $parser->evaluate("page[@name='$this->name']", $baseDir)->item(0);
		$parser->replace($page, null);
		
		return $parser->update();
	}
	
	/**
	 * Get the item id for the vcs.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	private function getItemID()
	{
		return "ajx".hash("md5", $this->directory."_".$this->name, FALSE);
	}
}
//#section_end#
?>