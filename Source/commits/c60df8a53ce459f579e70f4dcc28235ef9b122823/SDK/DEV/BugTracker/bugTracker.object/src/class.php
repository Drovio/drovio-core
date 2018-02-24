<?php
//#section#[header]
// Namespace
namespace DEV\BugTracker;

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
 * @package	BugTracker
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Bug Tracker
 * 
 * Class to report and review bugs
 * 
 * @version	{empty}
 * @created	June 30, 2014, 20:24 (EEST)
 * @revised	June 30, 2014, 20:24 (EEST)
 */
class bugTracker
{
	/**
	 * The Constructor Method
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