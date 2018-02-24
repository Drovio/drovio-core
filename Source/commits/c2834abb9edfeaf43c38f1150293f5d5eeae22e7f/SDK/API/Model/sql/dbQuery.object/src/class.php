<?php
//#section#[header]
// Namespace
namespace API\Model\sql;

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
 * @namespace	\sql
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Core", "test::sqlTester");
importer::import("DEV", "Core", "sql::sqlQuery");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \DEV\Core\test\sqlTester;
use \DEV\Core\sql\sqlQuery;

/**
 * Redback database query
 * 
 * Represents a redback's database query object.
 * 
 * @version	1.0-1
 * @created	August 14, 2014, 13:45 (EEST)
 * @revised	September 17, 2014, 12:30 (EEST)
 */
class dbQuery
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
	 * The query library directory.
	 * 
	 * @type	string
	 */
	private $directory = "/System/Library/Core/SQL";
	
	/**
	 * Whether to force load from deployed library.
	 * 
	 * @type	boolean
	 */
	private $forceDeployed;
	
	/**
	 * Constructor function. Initializes the query variables.
	 * 
	 * @param	string	$id
	 * 		The query id.
	 * 
	 * @param	string	$domain
	 * 		The query domain.
	 * 
	 * @param	boolean	$forceDeployed
	 * 		Setting this variable to true, the system will load the query from the deployed sql library overriding the sql tester mode status.
	 * 
	 * @return	void
	 */
	public function __construct($id, $domain, $forceDeployed = FALSE)
	{
		$this->id = $id;
		$this->domain = $domain;
		$this->forceDeployed = $forceDeployed;
	}
	
	/**
	 * Returns the executable query from the library.
	 * 
	 * @param	array	$attr
	 * 		An associative array of the query attributes.
	 * 
	 * @return	string
	 * 		The executable sql query.
	 */
	public function getQuery($attr = array())
	{
		// Check if sql tester is activated (TRUE for now)
		if (!$this->forceDeployed && sqlTester::status())
		{
			$q = new sqlQuery($this->domain, $this->id);
			return $q->getQuery($attr);
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
	 * Get the filename of the query to be stored to the exported library.
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