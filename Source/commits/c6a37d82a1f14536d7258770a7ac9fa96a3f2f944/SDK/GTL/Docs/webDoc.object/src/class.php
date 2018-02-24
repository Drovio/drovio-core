<?php
//#section#[header]
// Namespace
namespace GTL\Docs;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	GTL
 * @package	Docs
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Tools", "codeParser");

use \API\Geoloc\locale;
use \API\Literals\literal;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \DEV\Tools\codeParser;

/**
 * Web Document
 * 
 * This is an abstract web document manager.
 * Manages web documents for this platform, providing a function to get the root directory to store the document.
 * 
 * @version	0.1-2
 * @created	March 3, 2015, 16:07 (EET)
 * @updated	March 3, 2015, 16:47 (EET)
 */
abstract class webDoc
{
	/**
	 * The document inner directory.
	 * 
	 * @type	string
	 */
	private $directory = "";
	
	/**
	 * The document name.
	 * 
	 * @type	string
	 */
	private $docName = "";
	
	/**
	 * Indicates whether the path must be normalized in order to be root relative.
	 * 
	 * @type	boolean
	 */
	private $rootRelative = TRUE;
	
	/**
	 * This function should return the root directory for the document.
	 * The inner directory path will be calculated from this root folder.
	 * 
	 * @return	string
	 * 		The root directory path.
	 */
	abstract public function getRootDirectory();
	
	/**
	 * Initializes the document.
	 * 
	 * @param	string	$directory
	 * 		The document inner directory folder.
	 * 
	 * @param	string	$name
	 * 		The document name.
	 * 		Leave empty for new documents.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$rootRelative
	 * 		Indicates whether the path must be normalized in order to be root relative.
	 * 
	 * @return	void
	 */
	public function __construct($directory, $name = "", $rootRelative = TRUE)
	{
		$this->directory = trim($directory);
		$this->directory = trim($this->directory, "/");
		$this->docName = trim($name);
		$this->docName = trim($this->docName, "/");
		$this->rootRelative = $rootRelative;
	}
	
	/**
	 * Create the document.
	 * 
	 * @param	string	$name
	 * 		The document name.
	 * 
	 * @param	string	$context
	 * 		The initial document context.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document context.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $context = "", $locale = NULL)
	{
		// Set doc name
		$this->docName = $name;
		
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
		
		// Check if document already exists and set context
		$docPath = $this->getDocPath($locale, "html");
		if (file_exists($docPath) && empty($context))
			return TRUE;
		else if (file_exists($docPath) && !empty($context))
			return $this->update($context, $locale);
		
		// Create html file
		$docPath = $this->getDocPath($locale, "html");
		$this->createDocStructure($docPath);
		
		// Update context
		if (!empty($context))
			return $this->update($context, $locale);
		
		return TRUE;
	}
	
	/**
	 * Create the document structure for the html file.
	 * 
	 * @param	string	$docPath
	 * 		The document's path to save the initial structure.
	 * 
	 * @return	void
	 */
	private function createDocStructure($docPath)
	{
		// Create html file
		$parser = new DOMParser();
		$wDoc = $parser->create("div", "", "", "wDoc");
		$parser->append($wDoc);
		
		// Create doc Header
		$header = $parser->create("div", "", "", "header");
		$parser->append($wDoc, $header);
		
		// Create doc body container
		$body = $parser->create("div", "", "", "body");
		$parser->append($wDoc, $body);
		
		// Create footer container
		$footer = $parser->create("div", "", "", "footer");
		$parser->append($wDoc, $footer);
		
		// Create file
		$contextHTML = $parser->getHTML(TRUE);
		fileManager::create($docPath, $contextHTML, TRUE);
	}
	
	/**
	 * Update the document's context.
	 * 
	 * @param	string	$context
	 * 		The document body.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document context.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($context, $locale = NULL)
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
		
		// Decode context
		$context = codeParser::decode($context);
		
		// Update plain html
		$docPathPlain = $this->getDocPath($locale, "plain.html");
		fileManager::create($docPathPlain, $context);
			
		// Update document html structured file	
		$docPath = $this->getDocPath($locale, "html");
			
		// Create html file
		if (!file_exists($docPath))
			$this->createDocStructure($docPath);
		
		$parser = new DOMParser();
		$contentHTML = fileManager::get($docPath);
		$parser->loadContent($contentHTML, $contentType = DOMParser::TYPE_HTML, $preserve = TRUE);
		
		// Parse document and update body
		$body = $parser->evaluate("//*[@class='body']")->item(0);
		$parser->innerHTML($body, $context);
		
		// Update footer time updated
		$footer = $parser->evaluate("//*[@class='footer']")->item(0);
		$parser->innerHTML($footer, "");
		
		$date = date('d F, Y \a\t H:i', time());
		$title = literal::get("sdk.BSS.wdocs", "lbl_dateLastUpdate", array(), FALSE, $locale);
		$footerTime = $parser->create("p", $title." ".$date, "", "updated");
		$parser->append($footer, $footerTime);
		
		// Update file and return status
		$contextHTML = $parser->getHTML(TRUE);
		return fileManager::create($docPath, $contextHTML, TRUE);
	}
	
	/**
	 * Delete the document object.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove document
		$docPath = $this->getDocObjectPath();
		return folderManager::remove($docPath, "", TRUE);
	}
	
	/**
	 * Get the document body.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document context.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @return	string
	 * 		The document body context.
	 */
	public function get($locale = NULL)
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
			
		// Check if document exists in given locale and then get default
		$docPathPlain = $this->getDocPath($locale, "plain.html");
		if (!file_exists($docPathPlain))
		{
			$locale = locale::getDefault();
			$docPathPlain = $this->getDocPath($locale, "plain.html");
		}
		
		// Check file exists
		if (file_exists($docPathPlain))
			return fileManager::get($docPathPlain);
	}
	
	/**
	 * Load the entire document.
	 * The document will be loaded in the given locale.
	 * If no locale is given, the current system locale will be selected.
	 * If the document isn't translated in the current locale, it will return the system's default locale.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document context.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @return	string
	 * 		The entire document as html document.
	 */
	public function load($locale = NULL)
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
			
		// Check if document exists in given locale and then get default
		$docPath = $this->getDocPath($locale, "html");
		if (!file_exists($docPath))
		{
			$locale = locale::getDefault();
			$docPath = $this->getDocPath($locale, "html");
		}
		
		// Get the whole document
		return fileManager::get($docPath);
	}
	
	/**
	 * Parse the contents of the document and save in separate file.
	 * 
	 * @return	void
	 */
	private function parseContents()
	{
		// Load Document and parse contents
	}
	
	/**
	 * Get the document's full path.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document to get the path for.
	 * 
	 * @param	string	$extension
	 * 		The file extension.
	 * 		This is used for all the files in the document object.
	 * 
	 * @return	string
	 * 		The full document path.
	 */
	private function getDocPath($locale, $extension = "html")
	{
		// Check document name
		if (empty($this->docName))
			return NULL;
			
		// Get document path from inherited class
		$prefix = ($this->rootRelative ? systemRoot : "");
		$docPath = $prefix.$this->getRootDirectory().$this->directory."/".$this->docName.".wDoc/";
		
		// Set filename with the given locale
		$extension = (empty($extension) ? "html" : $extension);
		$fileName = "doc.".$locale.".".$extension;
		
		// Return full path (no systemRoot)
		return $docPath.$fileName;
	}
}
//#section_end#
?>