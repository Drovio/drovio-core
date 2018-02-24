<?php
//#section#[header]
// Namespace
namespace DEV\WebTemplates\components;

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
 * @package	WebTemplates
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */
/*
importer::import("DEV", "WebTemplates", "template");


use \DEV\WebTemplates\template;
*/
/**
 * Web Template Page
 * 
 * Object class to manage web template page
 * 
 * @version	0.1-1
 * @created	July 7, 2014, 22:08 (EEST)
 * @revised	July 21, 2014, 21:40 (EEST)
 */
class templPage
{
	/**
	 * Object file extension
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "page";
	
	/**
	 * The parent template object
	 * 
	 * @type	template
	 */
	private $templ;
	
	/**
	 * The page name
	 * 
	 * @type	string
	 */
	private $name;

	/**
	 * The constructor class
	 * 
	 * @param	string	$templID
	 * 		The id of the parent template, the one that the page belongs to
	 * 
	 * @param	string	$name
	 * 		The name of the page
	 * 
	 * @return	void
	 */
	public function __construct($templID, $name = "")
	{
		// Init
		$this->templ= new template($templID);
		
		// Set name
		$this->name = $name;
	}
	
	/**
	 * Creates a new template page.
	 * 
	 * @param	string	$name
	 * 		The items' name
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name)
	{
		// Check that name is not empty and valid
		if (empty($name))
			return FALSE;
			
		// Set view name
		$this->name = $name;
		
		// Create object index
		$status = $this->templ->addObjectIndex(template::PAGES_FOLDER, "page", $name);
		if (!$status)
			return FALSE;
		
		// Create vcs object
		$itemTrunkPath = systemRoot.$this->templ->getRepository()."/".template::PAGES_FOLDER;
		$itemName = $viewName.".".self::FILE_TYPE;		
		folderManager::create($itemTrunkPath."/".$itemName);
		
		// Create view structure
		$this->createStructure();
		
		return TRUE;
	}
	
	/**
	 * Gets the view's html content
	 * 
	 * @return	string
	 * 		The html content
	 */
	public function getHTML()
	{
		$itemTrunkPath = systemRoot.$this->templ->getRepository()."/".template::PAGES_FOLDER;
		$itemName = $viewName.".".self::FILE_TYPE;
	
		// Load view folder
		$viewFolder = $itemTrunkPath."/".$itemName;
		
		// Load script
		return fileManager::get($viewFolder."/page.html");
	}
	
	/**
	 * pdates the items' html content.
	 * 
	 * @param	string	$html
	 * 		The html content
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function updateHTML($html = "")
	{
		$itemTrunkPath = systemRoot.$this->templ->getRepository()."/".template::PAGES_FOLDER;
		$itemName = $viewName.".".self::FILE_TYPE;
	
		// Update and Load view folder
		$viewFolder = $itemTrunkPath."/".$itemName;
		

		// Update File
		$html = phpParser::clear($html);
		return fileManager::put($viewFolder."/page.html", $html);
	}
	
	/**
	 * Updates the view's css code.
	 * 
	 * @return	string
	 * 		The style css.
	 */
	public function getCSS()
	{
		$itemTrunkPath = systemRoot.$this->templ->getRepository()."/".template::PAGES_FOLDER;
		$itemName = $viewName.".".self::FILE_TYPE;
	
		// Load view folder
		$viewFolder = $itemTrunkPath."/".$itemName;
		
		// Load style
		return fileManager::get($viewFolder."/style.css");
	}
	
	/**
	 * Updates the items' css code.
	 * 
	 * @param	string	$code
	 * 		The css code.
	 * 
	 * @return	booleam
	 * 		True on success, false on failure.
	 */
	public function updateCSS($code = "")
	{
		$itemTrunkPath = systemRoot.$this->templ->getRepository()."/".template::PAGES_FOLDER;
		$itemName = $viewName.".".self::FILE_TYPE;
	
		// Update and Load view folder
		$viewFolder = $itemTrunkPath."/".$itemName;
		
		// Update File
		$code = phpParser::clear($code);
		return fileManager::put($viewFolder."/style.css", $code);
	}
	
	/**
	 * Creates the objects' inner file structure.
	 * 
	 * @return	void
	 */
	private function createStructure()
	{
		$itemTrunkPath = systemRoot.$this->templ->getRepository()."/".template::PAGES_FOLDER;
		$itemName = $viewName.".".self::FILE_TYPE;
	
		// Load view folder
		$viewFolder = $itemTrunkPath."/".$itemName;
		
		// Create view folder
		folderManager::create($viewFolder);
		
		// Create files
		fileManager::create($viewFolder."/view.html", "");
		fileManager::create($viewFolder."/style.css", "");
	}
}
//#section_end#
?>