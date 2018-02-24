<?php
//#section#[header]
// Namespace
namespace DEV\WebTemplates;

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
 * @package	WebTemplates
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Projects", "project");

use \DEV\Projects\project;

/**
 * Web Template Project
 * 
 * Object class to manage web template projects
 * 
 * @version	{empty}
 * @created	July 7, 2014, 20:34 (EEST)
 * @revised	July 7, 2014, 20:34 (EEST)
 */
class template
{
	/**
	 * The contructor methos
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Put your constructor method code here.
	}
}
//#section_end#
?>