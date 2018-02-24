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

importer::import("API", "Comm", "database::dbConnection");

use \API\Comm\database\dbConnection as newDbConnection;

/**
 * Database Connection
 * 
 * Connects to any database with the proper database connector.
 * 
 * @version	2.0-1
 * @created	April 23, 2013, 13:31 (EEST)
 * @revised	November 10, 2014, 12:42 (EET)
 * 
 * @deprecated	Use \API\Comm\database\dbConnection instead.
 */
class dbConnection extends newDbConnection {}
//#section_end#
?>