<?php
//#section#[header]
// Namespace
namespace RTL\Comm;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	RTL
 * @package	Comm
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ENP", "Comm", "dbConnection");

use \ENP\Comm\dbConnection as eDbConnection;

/**
 * Redback Retail Database Connection handler
 * 
 * Connects to Redback's retail database and executes all the red sql queries.
 * 
 * @version	1.0-1
 * @created	December 10, 2014, 11:05 (EET)
 * @updated	July 24, 2015, 11:21 (EEST)
 * 
 * @deprecated	Use \ENP\Comm\dbConnection instead.
 */
class dbConnection extends eDbConnection {}
//#section_end#
?>