<?php
//#section#[header]
// Namespace
namespace DEV\Analytics;

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
 * @package	Analytics
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Analytics Class - Analyzer
 * 
 * Class to store, load and analyze object visits and loads
 * 
 * @version	0.1-1
 * @created	August 12, 2014, 19:57 (EEST)
 * @revised	August 12, 2014, 19:57 (EEST)
 */
class analyzer
{
	/**
	 * The folter were the analytics data raw  is stored
	 * 
	 * @type	string
	 */
	const FOLDER = "/Issues/";

	/**
	 * The project id
	 * 
	 * @type	string
	 */
	private $pid;

	/**
	 * The Constructor Method
	 * 
	 * @param	string	$pid
	 * 		The project id
	 * 
	 * @return	void
	 */
	public function __construct($pid)
	{
		$this->pid = $pid;
	}
}
//#section_end#
?>