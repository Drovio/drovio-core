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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Geoloc", "locale");

use \API\Profile\team;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \API\Geoloc\locale;

/**
 * Web Documents Library Manager
 * 
 * This is the class that is responsible for managing the folders and documents of the Web Docs service.
 * 
 * @version	2.0-2
 * @created	September 5, 2014, 16:06 (EEST)
 * @revised	November 5, 2014, 16:55 (EET)
 */
class wDocLibrary
{
	/**
	 * The library's public folder.
	 * 
	 * @type	string
	 */
	const PUBLIC_FOLDER = "Public/";
	
	/**
	 * The Web Docs service root folder.
	 * 
	 * @type	string
	 */
	private $serviceFolder;
	/**
	 * The library index' DOMParser object.
	 * 
	 * @type	DOMParser
	 */
	private $dom_parser;
	
	/**
	 * Constructor method.
	 * It is responsible for creating the library index (if not any) and initializing the library.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Get Web Docs Service folder
		$this->serviceFolder = team::getServicesFolder("/WebDocs/");
		$this->dom_parser = new DOMParser();
		
		// Init Docs Library
		$this->init();
	}
	
	/**
	 * Get an array of all the folders under the given path.
	 * 
	 * @param	string	$parent
	 * 		The folder to look down from.
	 * 		The default value is empty, for the root.
	 * 		Separate each folder with "/".
	 * 
	 * @param	boolean	$compact
	 * 		Whether to return a single compact array with folders separated by "/".
	 * 
	 * @return	array
	 * 		A nested array of all the folders under the given path.
	 */
	public function getLibFolders($parent = "", $compact = FALSE)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/wdoclib";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/folder";
		
		$folders = array();
		$fes = $parser->evaluate($expression);
		foreach ($fes as $folderElement)
		{
			$folderName = $parser->attr($folderElement, "name");
			$newParent = (empty($parent) ? "" : $parent."/").$folderName;
			$libFolders = $this->getLibFolders($newParent, $compact);
			if ($compact)
			{
				$folders[] = $newParent;
				foreach ($libFolders as $lf)
					$folders[] = $lf;
			}
			else
				$folders[$folderName] = $libFolders;
		}
		
		if (empty($folders) && $compact)
			return "";
		return $folders;
	}
	
	/**
	 * Create a new folder in the doc library.
	 * 
	 * @param	string	$parent
	 * 		The parent folder to create the folder to.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$folder
	 * 		The name of the folder to create.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createFolder($parent, $folder)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/wdoclib";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentElement = $parser->evaluate($expression)->item(0);
		if (empty($parentElement))
			return FALSE;

		// Check if folder doesn't already exist
		$folderElement = $parser->evaluate($expression."/folder[@name='".$folder."']")->item(0);
		if (!is_null($folderElement))
			return FALSE;
		
		$folderElement = $parser->create("folder");
		$parser->attr($folderElement, "name", $folder);
		$parser->append($parentElement, $folderElement);
		return $parser->update();
	}
	
	/**
	 * Remove a folder from the Web Docs Library.
	 * The folder must be empty of pages and other folders.
	 * 
	 * @param	string	$folder
	 * 		The folder name to be removed.
	 * 		Separate each subfolder with "/".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeFolder($folder)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$folder = trim($folder);
		$folder = trim($folder, "/");
		
		// Get folder from index
		$expression = "/wdoclib";
		$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $folder)."']";
		$folderElement = $parser->evaluate($expression)->item(0);
		if (is_null($folderElement))
			return FALSE;
		
		// Check that folder is empty
		if ($folderElement->childNodes->length > 0)
			return FALSE;
		
		// Replace folder with null
		$parser->replace($folderElement, NULL);
		return $parser->update();
	}
	
	/**
	 * Create a document in the given folder.
	 * It updates the library index and creates a new wDoc.
	 * The initial context of the document will be saved in the user's active locale.
	 * 
	 * @param	string	$parent
	 * 		The folder to create the document to.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$docName
	 * 		The document name.
	 * 
	 * @return	mixed
	 * 		If success, it returns the wDoc object, otherwise it returns NULL.
	 */
	public function createDoc($parent, $docName)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		// Create doc library entry
		$expression = "/wdoclib";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentFolder = $parser->evaluate($expression)->item(0);
		if (empty($parentFolder))
			return FALSE;
		
		// Check if there isn't already a doc with the same name
		$docElement = $parser->evaluate($expression."/doc[@name='".$docName."']")->item(0);
		if (!is_null($docElement))
			return TRUE;
		
		// Create document entry in the library index
		$docElement = $parser->create("doc");
		$parser->attr($docElement, "name", $docName);
		$parser->append($parentFolder, $docElement);
		return $parser->update();
	}
	
	/**
	 * Remove a document from the library index.
	 * 
	 * @param	string	$parent
	 * 		The parent folder.
	 * 		Separate each folder with "/".
	 * 
	 * @param	string	$docName
	 * 		The document name to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeDoc($parent, $docName)
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		// Get view folder
		$expression = "/wdoclib";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$parentFolder = $parser->evaluate($expression)->item(0);
		if (empty($parentFolder))
			return FALSE;
		
		// Get the view from index
		$docElement = $parser->evaluate($expression."/doc[@name='".$docName."']")->item(0);
		if (is_null($docElement))
			return FALSE;
		
		// Replace view with null
		$parser->replace($docElement, NULL);
		return $parser->update();
	}
	
	/**
	 * Get all documents in a given folder.
	 * 
	 * @param	string	$parent
	 * 		The folder to look down from.
	 * 		The default value is empty, for the root.
	 * 		Separate each folder with "/".
	 * 
	 * @return	array
	 * 		An array of all documents.
	 */
	public function getFolderDocs($parent = "")
	{
		// Get library parser
		$parser = $this->dom_parser;
		$parent = trim($parent);
		$parent = trim($parent, "/");
		
		$expression = "/wdoclib";
		if (!empty($parent))
			$expression .= "/folder[@name='".str_replace("/", "']/folder[@name='", $parent)."']";
		$expression .= "/doc";
		
		$documents = array();
		// Get document parent
		$docs = $parser->evaluate($expression);
		foreach ($docs as $docElement)
			$documents[] = $parser->attr($docElement, "name");
		
		return $documents;
	}
	
	/**
	 * Initializes the DOMParser object and loads the library index file.
	 * If the index file doesn't exist, it creates it.
	 * 
	 * @return	void
	 */
	private function init()
	{
		// Get library parser
		$parser = $this->dom_parser;
		
		try
		{
			$parser->load($this->serviceFolder."/library.xml");
		}
		catch (Exception $ex)
		{
			// Create document's library root
			$root = $parser->create("wdoclib");
			$parser->append($root);
			
			// Create file
			fileManager::create(systemRoot.$this->serviceFolder."/library.xml", "", TRUE);
			$parser->save(systemRoot.$this->serviceFolder."/library.xml");
			
			// Create Public Folder
			$this->createFolder("", self::PUBLIC_FOLDER);
		}
	}
}
//#section_end#
?>