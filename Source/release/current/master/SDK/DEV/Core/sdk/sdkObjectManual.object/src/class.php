<?php
//#section#[header]
// Namespace
namespace DEV\Core\sdk;

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
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("GTL", "Docs", "webDoc");

use \GTL\Docs\webDoc;

/**
 * SDK Object Manual Document
 * 
 * Manages a Core SDK object's manual document in the object's smart folder.
 * 
 * @version	1.0-1
 * @created	March 4, 2015, 10:42 (GMT)
 * @updated	November 10, 2015, 11:15 (GMT)
 */
class sdkObjectManual extends webDoc
{
	/**
	 * The object's root folder.
	 * 
	 * @type	string
	 */
	private $objectFolder;
	
	/**
	 * Initialize the manual document.
	 * 
	 * @param	string	$objectFolder
	 * 		The object's root folder for the root directory function.
	 * 
	 * @param	string	$directory
	 * 		The manual document's directory.
	 * 
	 * @param	string	$name
	 * 		The document's name.
	 * 
	 * @return	void
	 */
	public function __construct($objectFolder, $directory, $name)
	{
		// Set variables
		$this->objectFolder = $objectFolder;
		
		// Construct sdk object manual document
		parent::__construct($directory, $name, FALSE);
	}

	/**
	 * Get the object's root directory as given by the sdkObject in the constructor.
	 * 
	 * @return	string
	 * 		Returns the root directory.
	 */
	public function getRootDirectory()
	{
		return $this->objectFolder;
	}
	
	/**
	 * Update the document's content.
	 * This class updates the document without table of contents.
	 * 
	 * @param	string	$content
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
	public function update($content, $locale = NULL)
	{
		return parent::update($content, $locale, $tableOfContents = FALSE);
	}
}
//#section_end#
?>