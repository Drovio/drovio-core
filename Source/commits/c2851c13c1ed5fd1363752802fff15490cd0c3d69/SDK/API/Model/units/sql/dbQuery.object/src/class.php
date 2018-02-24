<?php
//#section#[header]
// Namespace
namespace API\Model\units\sql;

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
 * @package	Model
 * @namespace	\units\sql
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Model", "sql::dbQuery");

use \API\Model\sql\dbQuery as newDbQuery;

/**
 * The database Query.
 * 
 * Represents a redback's database query.
 * 
 * @version	0.1-1
 * @created	August 8, 2013, 18:04 (EEST)
 * @revised	August 14, 2014, 13:47 (EEST)
 * 
 * @deprecated	Use \API\Model\sql\dbQuery instead.
 */
class dbQuery extends newDbQuery {}
//#section_end#
?>