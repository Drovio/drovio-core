<?php
//#section#[header]
// Namespace
namespace API\Comm\database\connections;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Comm
 * @namespace	\database\connections
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
use \SYS\Comm\db\dbConnection;

/**
 * Internal Database Connection
 * 
 * Connects to system's database and executes the queries
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:32 (EEST)
 * @revised	July 7, 2014, 11:44 (EEST)
 * 
 * @deprecated	Use \SYS\Comm\db\dbConnection instead.
 */
class interDbConnection extends dbConnection {}
//#section_end#
?>