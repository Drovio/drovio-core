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

importer::import("API", "Model", "units::domain::SpageFolder");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Model\units\domain\SpageFolder;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	March 24, 2014, 10:51 (EET)
 * @revised	March 24, 2014, 10:51 (EET)
 * 
 * @deprecated	Use \API\Resources\pages\page instead.
 */
class Spage
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$page_id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function info($page_id)
	{
		// Get page info
		$dbq = new dbQuery("739807288", "units.domains.pages");
		$dbc = new interDbConnection();
		
		$attr = array();
		$attr['id'] = $page_id;
		$result = $dbc->execute_query($dbq, $attr);
		
		return $dbc->fetch($result);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$page_id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function load($page_id)
	{
		// Get page path
		$page_path = self::path($page_id);
		
		// Check if file exists and load page file
		if (file_exists(systemRoot.$page_path))
			include($page_path);
		else // Throw Exception
			throw new Exception("Page file not found.");
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$page_id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function path($page_id)
	{
		// Get page info
		$page_info = self::info($page_id);

		// Get folder trail
		$folder_path = SpageFolder::trail($page_info['folder_id']);
		
		// Create page path
		$page_path = $folder_path.$page_info['file'];
		
		return $page_path;
	}
}
//#section_end#
?>