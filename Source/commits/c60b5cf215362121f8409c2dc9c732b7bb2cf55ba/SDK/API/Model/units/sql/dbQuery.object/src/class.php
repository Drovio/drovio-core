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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::sql::dvbQuery");
importer::import("API", "Developer", "profiler::sqlTester");
importer::import("API", "Model", "units::sql::sqlQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\components\sql\dvbQuery;
use \API\Developer\profiler\sqlTester;
use \API\Model\units\sql\sqlQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * The database Query.
 * 
 * Represents a redback's database query.
 * 
 * @version	{empty}
 * @created	August 8, 2013, 18:04 (EEST)
 * @revised	December 20, 2013, 19:18 (EET)
 */
class dbQuery extends sqlQuery
{
	/**
	 * The query id.
	 * 
	 * @type	string
	 */
	private $id;
	/**
	 * The query domain.
	 * 
	 * @type	string
	 */
	private $domain;
	/**
	 * The query access level.
	 * 
	 * @type	integer
	 */
	private $access_level = 1;
	
	/**
	 * The query library directory.
	 * 
	 * @type	string
	 */
	private $directory = "/System/Library/Core/SQL";
	
	/**
	 * Constructor function. Initializes the query variables.
	 * 
	 * @param	string	$id
	 * 		The query id.
	 * 
	 * @param	string	$domain
	 * 		The query domain.
	 * 
	 * @return	void
	 */
	public function __construct($id, $domain)
	{
		$this->id = $id;
		$this->domain = $domain;
		$this->load();
	}
	
	/**
	 * Loads the query and its info from the library.
	 * 
	 * @return	void
	 */
	private function load()
	{
		// Load index file
		$parser = new DOMParser();
		try
		{
			$parser->load($this->directory."/map.xml", TRUE);
			$query = $parser->find("q.".$this->id);
			if (!is_null($query))
				$this->access_level = $parser->attr($query, "level");
		}
		catch (Exception $ex)
		{
		}
	}
	
	/**
	 * Returns the executable query from the library.
	 * 
	 * @param	array	$attr
	 * 		An associative array of the query attributes.
	 * 
	 * @return	string
	 * 		The executable query.
	 */
	public function getQuery($attr = array())
	{
		// Check if sql tester is activated (TRUE for now)
		if (sqlTester::status())
		{
			$q = new dvbQuery($this->domain, $this->id);
			return $q->getExecutable($attr);
		}
		else
		{
			// Get executable filename
			$fileName = $this->getFileName();
			$query = "";
	
			// Acquire executable query file
			$nsdomain = str_replace('.', '/', $this->domain);
			if (file_exists(systemRoot.$this->directory."/".$nsdomain."/".$fileName.".sql"))
			{
				$query = fileManager::get(systemRoot.$this->directory."/".$nsdomain."/".$fileName.".sql");
				$query = trim($query);
				
				// Replace Attributes
				foreach ($attr as $key => $value)
				{
					$query = str_replace("$".$key, $value, $query);
					$query = str_replace("{".$key."}", $value, $query);
				}
			}
			else
				throw new Exception("Database Query '$this->domain -> $this->id' not found.");

			return $query;
		}
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$attr
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getQuery() instead.
	 */
	public function get_query($attr = array())
	{
		return $this->getQuery($attr);
	}
	
	/**
	 * Gets the access level of the query.
	 * 
	 * @return	integer
	 * 		The access level.
	 */
	public function getLevel()
	{
		return $this->access_level;
	}
	
	/**
	 * Returns the filename of the query to be stored to the exported library.
	 * 
	 * @return	string
	 * 		The query php filename.
	 */
	private function getFileName()
	{
		return "q.".hash("md5", "_q_".$this->id, FALSE);
	}
}
//#section_end#
?>