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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Navigation", "toolbar");

use \UI\Navigation\toolbar;

/**
 * Horizontal Navigation Bar
 * 
 * Creates an horizontal navigation toolbar.
 * It can be placed on the top or the bottom of a pane.
 * 
 * @version	0.1-1
 * @created	June 8, 2013, 11:41 (EEST)
 * @revised	December 22, 2014, 11:31 (EET)
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
	 * @return	navigationBar
	 * 		Returns the navigationBar object.
	 */
	public function build($dock = self::TOP, $parent = NULL)
	{
		parent::build($dock);
		
		if (!is_null($parent))
			$this->setParent($parent, $dock);
		
		return $this;
	}
}
//#section_end#
?>