<?php
//#section#[header]
// Namespace
namespace UI\Navigation;

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
 * @package	Navigation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("UI", "Navigation", "toolbar");

use \UI\Navigation\toolbar;

/**
 * Side Bar
 * 
 * Creates a specific side toolbar.
 * 
 * @version	0.1-1
 * @created	June 8, 2013, 11:39 (EEST)
 * @updated	May 2, 2015, 2:45 (EEST)
 */
class sidebar extends toolbar
{
	/**
	 * Left sidebar indicator.
	 * 
	 * @type	string
	 */
	const LEFT = "L";
	/**
	 * Right sidebar indicator.
	 * 
	 * @type	string
	 */
	const RIGHT = "R";
	
	/**
	 * Builds the sidebar.
	 * 
	 * @param	string	$dock
	 * 		The sidebar dock position.
	 * 
	 * @param	DOMElement	$parent
	 * 		The toolbar parent to set extra attributes for css.
	 * 
	 * @param	string	$id
	 * 		The toolbar unique id.
	 * 		If empty, it will get a random unique id.
	 * 		It is empty by default.
	 * 
	 * @param	string	$class
	 * 		The toolbar extra class for custom styling.
	 * 		It is empty by default.
	 * 
	 * @return	sidebar`
	 * 		The sidebar element.
	 */
	public function build($dock = self::LEFT, $parent = NULL, $id = "", $class = "")
	{
		// Build toolbar
		parent::build($dock, $id, $class);
		
		// Set parent (if not null)
		if (!is_null($parent))
			$this->setParent($parent, $dock);
		
		// Return sideBar object
		return $this;
	}
}
//#section_end#
?>