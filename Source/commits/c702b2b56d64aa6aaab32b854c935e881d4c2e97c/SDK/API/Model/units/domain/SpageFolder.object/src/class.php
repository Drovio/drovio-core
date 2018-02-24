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

importer::import("API", "Model", "units::domain::Sdomain");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Model\units\domain\Sdomain;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	March 24, 2014, 10:59 (EET)
 * @revised	March 24, 2014, 10:59 (EET)
 * 
 * @deprecated	Use \API\Resources\pages\pageFolder instead.
 */
class SpageFolder
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$folder_id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function info($folder_id)
	{
		// Get domain info from Database
		$dbq = new dbQuery("228088174", "units.domains.folders");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['id'] = $folder_id;
		$result = $dbc->execute_query($dbq, $attr);
		
		// Fetch Result
		return $dbc->fetch($result);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$folder_id
	 * 		{description}
	 * 
	 * @param	{type}	$delimiter
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function trail($folder_id, $delimiter = "/")
	{
		// Get Domain's Path
		$folder_info = self::info($folder_id);
		$domain_info = Sdomain::info($folder_info['domain']);
		$domain_path = $domain_info['path'];
		
		// Get page Folder's hierarchy Path
		$dbq = new dbQuery("1482332828", "units.domains.folders");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['id'] = $folder_id;
		$result = $dbc->execute_query($dbq, $attr);
		
		// Form folder path
		$path = "/";
		while ($row = $dbc->fetch($result))
			$path .= $row['name'].$delimiter;
		
		return $path;
	}
}
//#section_end#
?>