<?php
//#section#[header]
// Namespace
namespace DEV\Projects;

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
 * @package	Projects
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Projects", "project");
importer::import("GTL", "Docs", "webDoc");

use \DEV\Projects\project;
use \GTL\Docs\webDoc;

/**
 * Project ReadMe document manager.
 * 
 * Manages a project's read me document in the form of a web document.
 * 
 * @version	0.1-1
 * @created	March 4, 2015, 16:41 (EET)
 * @updated	March 4, 2015, 16:41 (EET)
 */
class projectReadme extends webDoc
{
	/**
	 * The project's root directory.
	 * 
	 * @type	string
	 */
	private $projectRootDirectory;
	
	/**
	 * Initialize the read me web document.
	 * 
	 * @param	string	$projectRootDirectory
	 * 		The project's root directory.
	 * 		This can vary from repository to published library.
	 * 
	 * @param	boolean	$rootRelative
	 * 		Indicates whether the path is system root relative or absolute.
	 * 
	 * @return	void
	 */
	public function __construct($projectRootDirectory, $rootRelative = TRUE)
	{
		// Set variables
		$this->projectRootDirectory = $projectRootDirectory;
		
		// Construct sdk object manual document
		parent::__construct("", "ReadMe", $rootRelative);
	}

	/**
	 * Get the project's root directory to store the read me file.
	 * 
	 * @return	string
	 * 		The project's root directory.
	 */
	public function getRootDirectory()
	{
		return $this->projectRootDirectory;
	}
}
//#section_end#
?>