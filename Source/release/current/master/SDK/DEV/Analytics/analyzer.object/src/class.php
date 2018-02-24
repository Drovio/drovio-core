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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Analytics", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");

use \DEV\Analytics\db\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * Analytics Class - Analyzer
 * 
 * Class to store, load and analyze object visits and loads
 * 
 * @version	0.2-1
 * @created	August 12, 2014, 20:09 (EEST)
 * @updated	February 16, 2015, 21:08 (EET)
 */
class analyzer
{
	/**
	 * The project id
	 * 
	 * @type	string
	 */
	private $projectID;

	/**
	 * The Constructor Method
	 * 
	 * @param	string	$projectID
	 * 		The project id
	 * 
	 * @return	void
	 */
	public function __construct($projectID)
	{
		$this->projectID = $projectID;
	}
	
	/**
	 * Log a request
	 * 
	 * @param	string	$page
	 * 		The page url that is requested, can be given as parameter, otherwise it will be taken from the $_SERVER
	 * 
	 * @param	string	$context
	 * 		The current contexts that is loaded e.g moduleID, appView, extenssionView
	 * 
	 * @param	string	$extras
	 * 		Extra paremeters to log
	 * 
	 * @param	string	$origin
	 * 		The requesters' origin page (http referrer)
	 * 
	 * @return	void
	 */
	public function log($page = '', $context = '', $extras = '', $origin = '')
	{
	}
}
//#section_end#
?>