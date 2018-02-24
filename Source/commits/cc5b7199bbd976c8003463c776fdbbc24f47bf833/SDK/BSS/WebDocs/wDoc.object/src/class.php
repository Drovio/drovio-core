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

importer::import("API", "Profile", "team");
importer::import("BSS", "WebDocs", "wDocLibrary");
importer::import("GTL", "Docs", "webDoc");

use \API\Profile\team;
use \BSS\WebDocs\wDocLibrary;
use \GTL\Docs\webDoc;

/**
 * Web Docs Document
 * 
 * Manages a Redback web document.
 * 
 * @version	3.0-1
 * @created	September 5, 2014, 18:24 (EEST)
 * @updated	March 4, 2015, 10:54 (EET)
 */
class wDoc extends webDoc
{
	/**
	 * Whether the document will be loaded from the public folder of the team.
	 * 
	 * @type	boolean
	 */
	private $publicDoc = FALSE;
	
	/**
	 * The team id (for public folders).
	 * 
	 * @type	integer
	 */
	private $teamID;
	
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
	 * @param	boolean	$public
	 * 		Set this to TRUE in order to load a public document from the library's Public folder.
	 * 
	 * @param	integer	$teamID
	 * 		Only for public folders, use this attribute to specify the team to get the document from.
	 * 		
	 * 		NOTE: This doesn't work when in secure mode, including applications.
	 * 
	 * @return	void
	 */
	public function __construct($directory, $name = "", $public = FALSE, $teamID = "")
	{
		// Set variables
		$this->publicDoc = $public;
		$this->teamID = $teamID;
		
		// Construct web document
		parent::__construct($directory, $name, TRUE);
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
		// Create in the library
		$lib = new wDocLibrary();
		$status = $lib->createDoc($this->directory, $name);
		if (!$status)
			return $status;
		
		// Create doc
		return parent::create($name, $context, $locale);
	}
	
	/**
	 * Remove the document entry from the library and delete the document.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Remove from the library
		$lib = new wDocLibrary();
		$status = $lib->removeDoc($this->directory, $this->docName);
		if (!$status)
			return $status;
		
		// Remove document
		return parent::remove();
	}	
	/**
	 * Create a new document in the library.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function getRootDirectory()
	{
		// Get Web Docs Service folder
		$serviceFolder = team::getServicesFolder("/WebDocs/", $this->teamID);
		
		// Reuturn document path
		return $serviceFolder."/".($this->publicDoc ? wDocLibrary::PUBLIC_FOLDER : "")."/";
	}
}
//#section_end#
?>