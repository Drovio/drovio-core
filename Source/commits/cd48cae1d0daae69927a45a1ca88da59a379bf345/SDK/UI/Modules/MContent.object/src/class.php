<?php
//#section#[header]
// Namespace
namespace UI\Modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Modules
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Literals", "moduleLiteral");
importer::import("UI", "Content", "HTMLContent");
importer::import("UI", "Html", "DOM");

use \API\Literals\moduleLiteral;
use \UI\Content\HTMLContent;
use \UI\Html\DOM;

/**
 * Module Content builder
 * 
 * Builds a module content with a specified id and class.
 * It loads module's html and can parse module's literals.
 * 
 * @version	0.2-2
 * @created	June 23, 2014, 12:34 (EEST)
 * @revised	July 30, 2014, 12:55 (EEST)
 */
class MContent extends HTMLContent
{
	/**
	 * The module's id that loads this object.
	 * 
	 * @type	integer
	 */
	protected $moduleID;
	
	/**
	 * Initializes the Module Content object.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id for this content (if any).
	 * 		Empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($moduleID = "")
	{
		$this->moduleID = $moduleID;
	}
	
	/**
	 * Build the outer html content container.
	 * 
	 * @param	string	$id
	 * 		The element's id. Empty by default.
	 * 
	 * @param	string	$class
	 * 		The element's class. Empty by default.
	 * 
	 * @param	boolean	$loadHTML
	 * 		Indicator whether to load html from the designer file.
	 * 
	 * @return	MContent
	 * 		The MContent object.
	 */
	public function build($id = "", $class = "", $loadHTML = FALSE)
	{
		// Build HTMLContent
		parent::build($id, $class, $loadHTML);
		
		// Load module literals
		$this->loadModuleLiterals();
		
		// Return MContent object
		return $this;
	}
	
	/**
	 * Loads module's literals in the designer's html file.
	 * 
	 * @return	MContent
	 * 		The MContent object.
	 */
	private function loadModuleLiterals()
	{
		// Search for data-literal
		$containers = DOM::evaluate("//*[@data-literal]");
		foreach ($containers as $container)
		{
			// Get literal attributes
			$value = DOM::attr($container, "data-literal");
			$attributes = json_decode($value, true);
			
			// Get literal
			if (!isset($attributes['scope']))
				$literal = moduleLiteral::get($this->moduleID, $attributes['name']);
			
			// If literal is valid, append to container
			if (!empty($literal))
				DOM::append($container, $literal);
			
			// Remove literal attribute
			DOM::attr($container, "data-literal", null);
		}
		
		return $this;
	}
	
	/**
	 * Gets the parent's filename for loading the html from external file.
	 * 
	 * @return	string
	 * 		The parent script name.
	 */
	protected function getParentFilename()
	{
		$stack = debug_backtrace();
		return $stack[3]['file'];
	}
}
//#section_end#
?>