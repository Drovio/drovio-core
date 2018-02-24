<?php
//#section#[header]
// Namespace
namespace BSS\WebDocs;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BSS
 * @package	WebDocs
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Tools", "codeParser");
importer::import("BSS", "WebDocs", "wDocLibrary");

use \API\Geoloc\locale;
use \API\Literals\literal;
use \API\Profile\team;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \DEV\Tools\codeParser;
use \BSS\WebDocs\wDocLibrary;

/**
 * Web Docs Document
 * 
 * Manages a Redback web document.
 * 
 * @version	1.1-1
 * @created	September 5, 2014, 18:24 (EEST)
 * @updated	January 12, 2015, 11:35 (EET)
 */
class wDoc
{
	/**
	 * The document directory.
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
	 * Initializes the document.
	 * 
	 * @param	string	$directory
	 * 		The document directory folder.
	 * 
	 * @param	string	$name
	 * 		The document name.
	 * 		Leave empty for new documents.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($directory, $name = "")
	{
		$this->directory = $directory;
		$this->docName = $name;
	}
	
	/**
	 * Create a new document in the library.
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
		
		// Create in the library
		$lib = new wDocLibrary();
		$status = $lib->createDoc($this->directory, $this->docName);
		if (!$status)
			return $status;
		
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
		
		// Check if document already exists and set context
		$docPath = $this->getDocPath($locale, "html");
		if (file_exists(systemRoot.$docPath) && empty($context))
			return TRUE;
		else if (file_exists(systemRoot.$docPath) && !empty($context))
			return $this->update($context, $locale);
		
		// Create html file
		$this->createDocStructure($docPath);
		
		// Update context
		if (!empty($context))
			return $this->update($context, $locale);
		
		return TRUE;
	}
	
	/**
	 * Create the document structure.
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
		fileManager::create(systemRoot.$docPath, $contextHTML, TRUE);
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
		fileManager::create(systemRoot.$docPathPlain, $context);
			
		// Update document html structured file	
		$docPath = $this->getDocPath($locale);
			
		// Create html file
		if (!file_exists(systemRoot.$docPath))
			$this->createDocStructure($docPath);
		
		$parser = new DOMParser();
		$contentHTML = fileManager::get(systemRoot.$docPath);
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
		return fileManager::create(systemRoot.$docPath, $contextHTML, TRUE);
	}
	
	/**
	 * Remove the document from the library.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Get document path
		$docPath = $this->getDocPath($locale);
		
		// Remove from the library
		$lib = new wDocLibrary();
		$status = $lib->removeDoc($this->directory, $this->docName);
		if ($status)
		{
			// Remove document
			$docPath = $this->getDocObjectPath();
			return folderManager::remove($docPath, "", TRUE);
		}
		
		return FALSE;
	}
	
	/**
	 * Get the document body.
	 * 
	 * @param	string	$locale
	 * 		The locale of the requested document context.
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
		if (!file_exists(systemRoot.$docPathPlain))
		{
			$locale = locale::getDefault();
			$docPathPlain = $this->getDocPath($locale, "plain.html");
		}
		
		// Check file exists
		if (file_exists(systemRoot.$docPathPlain))
			return fileManager::get(systemRoot.$docPathPlain);
		
		
		// COMPATIBILITY
		
		// Check if document exists in given locale and then get default
		$docPath = $this->getDocPath($locale, "html");
		if (!file_exists(systemRoot.$docPath))
		{
			$locale = locale::getDefault();
			$docPath = $this->getDocPath($locale, "html");
		}
		
		// Check file exists
		if (!file_exists(systemRoot.$docPath))
			return "";
		
		// Load document content
		$parser = new DOMParser();
		$contentHTML = fileManager::get(systemRoot.$docPath);
		$parser->loadContent($contentHTML, $contentType = DOMParser::TYPE_HTML, $preserve = TRUE);
		
		// Parse document and get body
		$body = $parser->evaluate("//div[@class='body']")->item(0);
		return $parser->innerHTML($body);
	}
	
	/**
	 * Load the entire document.
	 * The document will be loaded in the given locale.
	 * If no locale is given, the current system locale will be selected.
	 * If the document isn't translated in the current locale, it will return the system's default locale.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @param	boolean	$public
	 * 		Set this to TRUE in order to load a public document from the library's Public folder.
	 * 		
	 * 		If this attribute is set to TRUE, the document's directory must not include the public folder path.
	 * 
	 * @param	string	$teamID
	 * 		Only for public folders, use this attribute to specify the team to get the document from.
	 * 		
	 * 		NOTE: This doesn't work when in secure mode, including applications.
	 * 
	 * @return	string
	 * 		The entire document as html document.
	 */
	public function load($locale = NULL, $public = FALSE, $teamID = "")
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
			
		// Check if document exists in given locale and then get default
		$docPath = $this->getDocPath($locale, "html", $public, $teamID);
		if (!file_exists(systemRoot.$docPath))
		{
			$locale = locale::getDefault();
			$docPath = $this->getDocPath($locale, "html", $public, $teamID);
		}
		
		// Get the whole document
		return fileManager::get(systemRoot.$docPath);
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
	 * Get the document object path inside the library.
	 * 
	 * @param	boolean	$public
	 * 		Set this to TRUE in order to load a public document from the library's Public folder.
	 * 
	 * @param	integer	$teamID
	 * 		Only for public folders, use this attribute to specify the team to get the document from.
	 * 		
	 * 		NOTE: This doesn't work when in secure mode, including applications.
	 * 
	 * @return	string
	 * 		The document object path.
	 */
	private function getDocObjectPath($public = FALSE, $teamID = "")
	{
		// Get Web Docs Service folder
		$serviceFolder = team::getServicesFolder("/WebDocs/", $teamID);
		
		// Reuturn document path
		return $serviceFolder."/".($public ? wDocLibrary::PUBLIC_FOLDER : "").$this->directory."/".$this->docName.".wDoc/";
	}
	
	/**
	 * Get the document's path inside the library.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document.
	 * 
	 * @param	{type}	$extension
	 * 		{description}
	 * 
	 * @param	boolean	$public
	 * 		Set this to TRUE in order to load a public document from the library's Public folder.
	 * 
	 * @param	string	$teamID
	 * 		Only for public folders, use this attribute to specify the team to get the document from.
	 * 		
	 * 		NOTE: This doesn't work when in secure mode, including applications.
	 * 
	 * @return	string
	 * 		The full document path.
	 */
	private function getDocPath($locale, $extension = "html", $public = FALSE, $teamID = "")
	{
		// Get document path
		$docPath = $this->getDocObjectPath($public, $teamID);
		
		// Set filename with the given locale
		$extension = (empty($extension) ? "html" : $extension);
		$fileName = "doc.".$locale.".".$extension;
		
		// Return full path (no systemRoot)
		return $docPath.$fileName;
	}
}
//#section_end#
?>