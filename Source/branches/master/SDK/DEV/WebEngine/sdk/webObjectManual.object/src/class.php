<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine\sdk;

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
 * @package	WebEngine
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("GTL", "Docs", "webDoc");

use \GTL\Docs\webDoc;

/**
 * Web SDK Object Manual Document
 * 
 * Manages a Web Core SDK object's manual document in the object's smart folder.
 * 
 * @version	0.1-1
 * @created	May 29, 2015, 18:35 (EEST)
 * @updated	May 29, 2015, 18:35 (EEST)
 */
class webObjectManual extends webDoc
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
}
//#section_end#
?>