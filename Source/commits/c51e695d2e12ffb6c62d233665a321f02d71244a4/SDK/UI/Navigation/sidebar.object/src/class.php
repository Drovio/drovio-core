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
 * Side Bar
 * 
 * Creates a specific side toolbar.
 * 
 * @version	{empty}
 * @created	June 8, 2013, 11:39 (EEST)
 * @revised	June 8, 2013, 11:39 (EEST)
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
	 * @return	sidebar`
	 * 		The sidebar element.
	 */
	public function build($dock = self::LEFT, $parent = NULL)
	{
		parent::build($dock);
		
		if (!is_null($parent))
			$this->setParent($parent, $dock);
		
		return $this;
	}
}
//#section_end#
?>