<?php
//#section#[header]
// Namespace
namespace UI\Navigation;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Navigation
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Navigation", "toolbar");

use \UI\Navigation\toolbar;

/**
 * Navigation Bar
 * 
 * Creates a specific navigation toolbar.
 * 
 * @version	{empty}
 * @created	June 8, 2013, 11:41 (EEST)
 * @revised	June 8, 2013, 11:41 (EEST)
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