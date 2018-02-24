<?php
//#section#[header]
// Namespace
namespace API\Model\units\domain;

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
 * @namespace	\units\domain
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	March 24, 2014, 11:09 (EET)
 * @revised	March 24, 2014, 11:09 (EET)
 * 
 * @deprecated	Use \API\Resources\pages\domain instead.
 */
class Sdomain
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function info($description)
	{
		// Get domain info from Database
		$dbq = new dbQuery("758802961", "units.domains");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['name'] = $description;
		$result = $dbc->execute_query($dbq, $attr);
		
		// Fetch Result
		return $dbc->fetch($result);
	}
}
//#section_end#
?>