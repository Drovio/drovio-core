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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("API", "Model", "core/resource");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("AEL", "Resources", "appManager");
importer::import("DEV", "Tools", "codeParser");

use \API\Geoloc\locale;
use \API\Literals\literal;
use \API\Model\core\resource;
use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \AEL\Resources\appManager;
use \DEV\Tools\codeParser;

/**
 * Web Document
 * 
 * This is an abstract web document manager.
 * Manages web documents for this platform, providing a function to get the root directory to store the document.
 * 
 * @version	3.1-6
 * @created	March 3, 2015, 14:07 (GMT)
 * @updated	November 1, 2015, 16:09 (GMT)
 */
abstract class webDoc
{
	/**
	 * The main web doc content class.
	 * 
	 * @type	string
	 */
	const DOC_CLASS = "wDoc";
	
	/**
	 * The document inner directory.
	 * 
	 * @type	string
	 */
	protected $directory = "";
	
	/**
	 * The document name.
	 * 
	 * @type	string
	 */
	protected $docName = "";
	
	/**
	 * Indicates whether the path must be normalized in order to be root relative.
	 * 
	 * @type	boolean
	 */
	protected $rootRelative = TRUE;
	
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
	 * 		Indicates whether the path is system root relative or absolute.
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
	 * @param	string	$content
	 * 		The initial document content.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document context.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @param	boolean	$tableOfContents
	 * 		Set true to add a table of contents to the document.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $content = "", $locale = NULL, $tableOfContents = TRUE)
	{
		// Set doc name
		$this->docName = $name;
		
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
		
		// Check if document already exists and set context
		$docPath = $this->getDocPath($locale, "html");
		if (file_exists($docPath) && empty($content))
			return TRUE;
		else if (file_exists($docPath) && !empty($content))
			return $this->update($content, $locale);
		
		// Create html file
		$docPath = $this->getDocPath($locale, "html");
		$this->createDocStructure($docPath);
		
		// Update context
		if (!empty($content))
			return $this->update($content, $locale, $tableOfContents);
		
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
		$wDoc = $parser->create("div", "", "", self::DOC_CLASS);
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
		$contentHTML = $parser->getHTML(TRUE);
		fileManager::create($docPath, $contentHTML, TRUE);
	}
	
	/**
	 * Update the document's content.
	 * 
	 * @param	string	$content
	 * 		The document body.
	 * 
	 * @param	string	$locale
	 * 		The locale of the document context.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @param	boolean	$tableOfContents
	 * 		Set true to add a table of contents to the document.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($content, $locale = NULL, $tableOfContents = TRUE)
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
		
		// Clear and decode content
		$content = codeParser::clear($content);
		
		// Update plain html
		$docPathPlain = $this->getDocPath($locale, "plain.html");
		fileManager::create($docPathPlain, $content);
			
		// Update document html structured file	
		$docPath = $this->getDocPath($locale, "html");
			
		// Create html file
		if (!file_exists($docPath))
			$this->createDocStructure($docPath);
		
		$parser = new DOMParser();
		$contentHTML = fileManager::get($docPath);
		
		// Load content as xml/html
		$parser->loadContent($contentHTML, $contentType = DOMParser::TYPE_HTML, $preserve = TRUE);
		
		// Resolve any urls
		$content = $this->resolveUrls($content);
		
		// Parse document and update body
		$body = $parser->select(".body")->item(0);
		$parser->innerHTML($body, $content);
		
		// Update footer locale and time updated
		$footer = $parser->select(".footer")->item(0);
		$parser->innerHTML($footer, "");
		
		$date = date('d F, Y', time());
		$title = literal::get("sdk.BSS.wdocs", "lbl_dateLastUpdate", array(), FALSE, $locale);
		$footerTime = $parser->create("p", $title." ".$date." [".$locale."]", "", "updated");
		$parser->append($footer, $footerTime);
		
		// Parser document info (title, contents etc)
		$this->updateDocumentInfo($parser);
		
		// Insert document contents
		if ($tableOfContents)
			$this->updateDocumentContents($parser, $locale);
		
		// Update file and return status
		$contentHTML = $parser->getHTML(TRUE);
		return fileManager::create($docPath, $contentHTML, TRUE);
	}
	
	/**
	 * Resolve document urls.
	 * It currently resolves team profile urls using %{team} and %{team_shared}.
	 * 
	 * @param	string	$document
	 * 		The document content.
	 * 
	 * @return	void
	 */
	private function resolveUrls($document)
	{
		// Resolve urls
		$teamSharedProfileURL = team::getProfileUrl($innerPath = appManager::SHARED_FOLDER, $teamID = "");
		$document = str_replace("%{team_shared}", $teamSharedProfileURL, $document);
		$document = str_replace("{team_shared}", $teamSharedProfileURL, $document);
		
		$teamProfileURL = team::getProfileUrl($innerPath = "", $teamID = "");
		$document = str_replace("%{team}", $teamProfileURL, $document);
		$document = str_replace("{team}", $teamProfileURL, $document);
		
		// Return new document
		return $document;
	}
	
	/**
	 * Updates the document info into the document xml file.
	 * 
	 * @param	DOMParser	$parser
	 * 		The document parser.
	 * 
	 * @return	void
	 */
	private function updateDocumentInfo($parser)
	{
		// Load/create the document info file
		$docPath = $this->getRootDirectory().$this->getDocumentFolderName()."/document.xml";
		$xmlParser = new DOMParser();
		try
		{
			$xmlParser->load($docPath, $this->rootRelative);
			$root = $xmlParser->evaluate("/document")->item(0);
		}
		catch (Exception $ex)
		{
			// Create root
			$root = $xmlParser->create("document");
			$xmlParser->attr($root, "created", time());
			$xmlParser->append($root);
			
			// Save
			$prefix = ($this->rootRelative ? systemRoot : "");
			$xmlParser->save($prefix.$docPath);
		}
		
		// Get document title element
		$titleElement = $xmlParser->evaluate("/document/title")->item(0);
		if (empty($titleElement))
		{
			$titleElement = $xmlParser->create("title");
			$xmlParser->prepend($root, $titleElement);
		}
		
		// Set document title value
		$docTitle = $parser->select("h1")->item(0);
		if (!empty($docTitle))
			$xmlParser->innerHTML($titleElement, $docTitle->nodeValue);
		
		// Get document contents element
		$contentsElement = $xmlParser->evaluate("/document/contents")->item(0);
		if (empty($contentsElement))
		{
			$contentsElement = $xmlParser->create("contents");
			$xmlParser->append($root, $contentsElement);
		}
		// Empty the contents element
		$xmlParser->innerHTML($contentsElement, "");
		
		// Parse document contents
		$headers = $parser->select("h2,h3,h4,h5,h6,h7");
		$contents = array();
		foreach ($headers as $headerElement)
		{
			// Get tag value
			$contentType = $headerElement->tagName;
			// Get content value
			$contentValue = trim($headerElement->nodeValue);
			if (empty($contentValue))
				continue;
			
			// Create header reference id
			$contentID = strtolower($contentValue);
			$contentID = str_replace(" ", "_", $contentID);
			$contentID = str_replace(".", "_", $contentID);
			
			// Append contents to the document info
			$cntEntry = $xmlParser->create("cnt", $contentValue, $contentID);
			$xmlParser->attr($cntEntry, "type", $contentType);
			$xmlParser->append($contentsElement, $cntEntry);
			
			// Add meta attributes for document navigation
			$parser->attr($headerElement, "id", $contentID);
			
			// Add item to list
			$cnt = array();
			$cnt['type'] = $contentType;
			$cnt['header'] = $contentValue;
			$contents[$contentID] = $cnt;
		}
		
		// Update file
		$xmlParser->attr($root, "updated", time());
		return $xmlParser->update();
	}
	
	/**
	 * Insert the document contents in the document.
	 * 
	 * @param	DOMParser	$parser
	 * 		The document parser.
	 * 
	 * @param	string	$locale
	 * 		The document locale.
	 * 
	 * @return	void
	 */
	private function updateDocumentContents($parser, $locale)
	{
		// Create content container
		$cntContainer = $parser->create("div", "", "", "toc");
		
		// Hide/Show button
		$title = literal::get("sdk.BSS.wdocs", "hd_document_contents_hide", array(), FALSE, $locale);
		$toggleButton_hide = $parser->create("div", $title, "", "toggleButton hide");
		$parser->append($cntContainer, $toggleButton_hide);
		
		$title = literal::get("sdk.BSS.wdocs", "hd_document_contents_show", array(), FALSE, $locale);
		$toggleButton_show = $parser->create("div", $title, "", "toggleButton show");
		$parser->append($cntContainer, $toggleButton_show);
		
		// Add title
		$title = literal::get("sdk.BSS.wdocs", "hd_document_contents", array(), FALSE, $locale);
		$hd = $parser->create("div", $title, "", "ctitle");
		$parser->append($cntContainer, $hd);
		
		// Create tocList
		$tocList = $parser->create("div", "", "", "toc-list");
		$parser->append($cntContainer, $tocList);
		
		// Go through the contents
		$contentsList = $this->getDocumentContents();
		$baseLevel = $currentLevel = 2;
		$levelIndex = array();
		$levelIndex[2] = 1;
		foreach ($contentsList as $contentID => $contentEntry)
		{
			// Get level
			$level = intval(str_replace("h", "", $contentEntry['type']));
			
			// Set level index
			$currentLevelIndex = "";
			if ($level > $currentLevel)
				$levelIndex[$level] = 1;
			else
				$levelIndex[$level] = (empty($levelIndex[$level]) ? 1 : $levelIndex[$level]);
			$currentLevel = $level;
			
			
			// Set current level index
			$currentLevelIndex = $levelIndex[$level];
			if ($currentLevel > $baseLevel)
				for ($i = $currentLevel - 1; $i >= $baseLevel; $i--)
					$currentLevelIndex = ($levelIndex[$i] - 1 <= 0 ? $levelIndex[$i] : $levelIndex[$i] - 1).".".$currentLevelIndex;
			else
				$currentLevelIndex = $levelIndex[$currentLevel];
			
			$entryValue = str_repeat("\t", $level - $baseLevel).$currentLevelIndex." ".$contentEntry['header'];
			$entry = $parser->create("a", $entryValue, "", "toc-entry");
			$parser->attr($entry, "href", "#".$contentID);
			$parser->append($tocList, $entry);
			
			// Increase level
			$levelIndex[$level]++;
		}
		
		// Find document title and append contents after that
		$docBody = $parser->select(".body")->item(0);
		$nextTitle = $parser->select(".body h2")->item(0);
		if (!empty($nextTitle))
			$parser->appendBefore($docBody, $nextTitle, $cntContainer);
		else
			$parser->prepend($docBody, $cntContainer);
	}
	
	/**
	 * Parse the contents of the document and save in separate file.
	 * 
	 * @return	DOMParser
	 * 		The updated parser.
	 */
	public function getDocumentTitle()
	{
		// Get document info
		$documentInfo = $this->getDocumentInfo();
		
		// Return the title
		return $documentInfo['title'];
	}
	
	/**
	 * Get the document's contents.
	 * 
	 * @return	array
	 * 		It will return an array of all the document contents as they appear in the following format:
	 * 		$content[content_id]['type'] = "h2";
	 * 		$content[content_id]['header'] = "This is the content header";
	 */
	public function getDocumentContents()
	{
		// Get document info
		$documentInfo = $this->getDocumentInfo();
		
		// Return the contents array
		return $documentInfo['contents'];
	}
	
	/**
	 * Get all document info including title and contents.
	 * 
	 * @return	array
	 * 		An array of the document info:
	 * 		'title' and 'contents'.
	 */
	public function getDocumentInfo()
	{
		// Initialize document info
		$documentInfo = array();
		
		// Load the document info file
		$docPath = $this->getRootDirectory().$this->getDocumentFolderName()."/document.xml";
		$xmlParser = new DOMParser();
		try
		{
			$xmlParser->load($docPath, $rootRelative = TRUE);
			$root = $xmlParser->evaluate("/document")->item(0);
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		// Get document title
		$titleElement = $xmlParser->evaluate("/document/title")->item(0);
		$documentInfo['title'] = $titleElement->nodeValue;
		
		// Get document contents
		$contents = $xmlParser->evaluate("/document/contents/cnt");
		foreach ($contents as $cElement)
		{
			$cnt = array();
			$cnt['type'] = $xmlParser->attr($cElement, "type");
			$cnt['header'] = $cElement->nodeValue;
			
			$contentID = $xmlParser->attr($cElement, "id");
			$documentInfo['contents'][$contentID] = $cnt;
		}
		
		// Return document info
		return $documentInfo;
	}
	
	/**
	 * Delete the document object.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Get document path from inherited class
		$prefix = ($this->rootRelative ? systemRoot : "");
		$docPath = $prefix.$this->getRootDirectory().$this->getDocumentFolderName();
		
		// Remove document
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
	 * Export the document to an independent html file.
	 * 
	 * @param	string	$locale
	 * 		The locale to export to.
	 * 		If empty, it will be the current locale of the user.
	 * 		It is NULL by default.
	 * 
	 * @return	string
	 * 		The html document.
	 */
	public function export($locale = NULL)
	{
		// Load document to the given locale
		$documentHTML = $this->load($locale);
		
		// Create full html document
		$parser = new DOMParser();
		$html = $parser->create("html");
		$parser->append($html);
		
		// Set head and add styles
		$head = $parser->create("head");
		$parser->append($html, $head);
		
		// Add meta attributes
		$meta = $parser->create("meta");
		$parser->attr($meta, "http-equiv", "Content-Type");
		$parser->attr($meta, "content", "text/html; charset=utf-8");
		$parser->append($head, $meta);
		
		$meta = $parser->create("meta");
		$parser->attr($meta, "name", "viewport");
		$parser->attr($meta, "content", "width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0");
		$parser->append($head, $meta);
		
		$meta = $parser->create("meta");
		$parser->attr($meta, "name", "apple-mobile-web-app-capable");
		$parser->attr($meta, "content", "yes");
		$parser->append($head, $meta);
		
		$meta = $parser->create("meta");
		$parser->attr($meta, "name", "generator");
		$parser->attr($meta, "content", "Drovio Web Document");
		$parser->append($head, $meta);
		
		// Reset.css
		$link = $parser->create("link");
		$parser->attr($link, "href", "http://cdn.drov.io/css/reset.css");
		$parser->attr($link, "rel", "stylesheet");
		$parser->append($head, $link);
		
		// Add styles
		$docStyle = resource::get("/resources/GTL/wdoc.css");
		$style = $parser->create("style", $docStyle);
		$parser->append($head, $style);
		
		// Add body with html content
		$body = $parser->create("body");
		$parser->innerHTML($body, $documentHTML);
		$parser->append($html, $body);
		
		// Export html
		return $parser->getHTML(TRUE);
	}
	
	/**
	 * Get the document's entire path below the root, including the document name.
	 * 
	 * @return	string
	 * 		The document full inner path.
	 */
	public function getDocumentFolderName()
	{
		return $this->directory."/".$this->docName.".wDoc/";
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
	final protected function getDocPath($locale, $extension = "html")
	{
		// Check document name
		if (empty($this->docName))
			return NULL;
		
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
		
		// Get document path from inherited class
		$prefix = ($this->rootRelative ? systemRoot : "");
		$docPath = $prefix.$this->getRootDirectory().$this->getDocumentFolderName();

		// Set filename with the given locale
		$extension = (empty($extension) ? "html" : $extension);
		$fileName = "doc.".$locale.".".$extension;

		// Return full path (no systemRoot)
		return $docPath.$fileName;
	}
}
//#section_end#
?>