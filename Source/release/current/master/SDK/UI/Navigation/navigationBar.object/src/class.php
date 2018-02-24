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
 * Horizontal Navigation Bar
 * 
 * Creates an horizontal navigation toolbar.
 * It can be placed on the top or the bottom of a pane.
 * 
 * @version	0.2-2
 * @created	June 8, 2013, 11:41 (EEST)
 * @updated	May 21, 2015, 17:04 (EEST)
 */
class navigationBar extends toolbar
{
	/**
	 * Top navigation toolbar indicator.
	 * 
	 * @type	string
	 */
	const TOP = "T";

	/**
	 * Bottom navigation toolbar indicator.
	 * 
	 * @type	string
	 */
	const BOTTOM = "B";
	
	/**
	 * Builds the navigation toolbar.
	 * 
	 * @param	string	$dock
	 * 		The navigation toolbar dock position.
	 * 		Use class constants to set.
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
	 * @return	navigationBar
	 * 		Returns the navigationBar object.
	 */
	public function build($dock = self::TOP, $parent = NULL, $id = "", $class = "")
	{
		// Build toolbar
		parent::build($dock, $id, $class);
		
		// Set parent (if not null)
		if (!is_null($parent))
			$this->setParent($parent, $dock);
		
		// Return navigationBar object
		return $this;
	}
}
//#section_end#
?>