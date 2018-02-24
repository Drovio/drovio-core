<?php
//#section#[header]
// Namespace
namespace DEV\Websites;

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
 * @package	Websites
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Website Server
 * 
 * A class to manage website's server.xml file. That file keeps all the required information for the various the user has configure to use with the website project
 * 
 * @version	0.1-1
 * @created	October 3, 2014, 21:09 (EEST)
 * @revised	October 3, 2014, 21:09 (EEST)
 */
class wsServer
{
	/**
	 * The class contructor method
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