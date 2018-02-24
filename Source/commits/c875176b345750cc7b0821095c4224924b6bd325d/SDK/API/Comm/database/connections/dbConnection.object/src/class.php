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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connectors::mysql_dbConnector");
importer::import("API", "Developer", "profiler::logger");

use \API\Comm\database\connectors\mysql_dbConnector;
use \API\Developer\profiler\logger;

/**
 * Database Connection
 * 
 * Connects to any database with the proper database connector.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:31 (EEST)
 * @revised	December 16, 2013, 11:50 (EET)
 */
class dbConnection
{
	/**
	 * Database connector
	 * 
	 * @type	iDbConnector
	 */
	protected $dbConnector;
	
	/**
	 * Database engine
	 * 
	 * @type	string
	 */
	protected $dbType;
	/**
	 * The database URL host
	 * 
	 * @type	string
	 */
	protected $host;
	/**
	 * The database name
	 * 
	 * @type	string
	 */
	protected $database;
	
	/**
	 * Database username
	 * 
	 * @type	string
	 */
	private $username;
	/**
	 * Database password
	 * 
	 * @type	string
	 */
	private $password;
	
	/**
	 * Set connection options
	 * 
	 * @param	string	$dbType
	 * 		The database engine type
	 * 
	 * @param	string	$host
	 * 		The database host URL
	 * 
	 * @param	string	$database
	 * 		The database name
	 * 
	 * @param	string	$username
	 * 		The user's username
	 * 
	 * @param	string	$password
	 * 		The user's password
	 * 
	 * @return	void
	 */
	public function options($dbType, $host, $database, $username, $password)
	{
		// Initialize Variables
		$this->dbType = $dbType;
		$this->host = $host;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;

		// Get db Connector according to dbType
		$dbConnectorName = '\\API\\Comm\\database\\connectors\\'.strtolower($this->dbType)."_dbConnector";
		$this->dbConnector = new $dbConnectorName();
		
		// Set dbConnector Handler
		$this->dbConnector->setHandler($this->host, $this->username, $this->password, $this->database);
	}
	
	/**
	 * Executes a query to the database. It supports multiple queries separated with ";".
	 * 
	 * @param	string	$query
	 * 		The query to be executed. It supports many queries separated by ";".
	 * 
	 * @return	mixed
	 * 		Returns FALSE on failure.
	 * 		For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a mysqli_result object.
	 * 		For other successful queries mysqli_query() will return TRUE.
	 * 
	 * @throws	Exception
	 */
	public function execute($query)
	{
		try
		{
			// Log activity
			logger::log("Executing SQL Query to ".$this->host, logger::INFO, $query, logger::DATABASE_IO);
			
			// Execute
			return $this->dbConnector->execute($query);
		}
		catch (Exception $ex)
		{
			// Log Exception Message
			logger::log("Query Execution failed. Message error: ".$ex->getMessage(), logger::ERROR);
			
			// Return false
			return FALSE;
		}
	}
	
	/**
	 * Clears a string given and returns the cleared one.
	 * 
	 * @param	string	$resource
	 * 		The string to be cleared.
	 * 
	 * @return	string
	 * 		The cleared string.
	 */
	public function escape($resource)
	{
		// If the resource is numeric, there is no need of escaping
		if (is_numeric($resource))
			return $resource;
		
		// Return escaped resource
		return $this->dbConnector->escape($resource);
	}
	
	/**
	 * Fetch results from resource.
	 * 
	 * @param	resource	$resource
	 * 		The database results resource.
	 * 
	 * @param	boolean	$all
	 * 		Whether it will fetch the entire resource into one array.
	 * 
	 * @return	array
	 * 		An array of results.
	 */
	public function fetch($resource, $all = FALSE)
	{
		return $this->dbConnector->fetch($resource, $all);
	}
	
	/**
	 * Transform a resource to an array with the specified key value assignment
	 * 
	 * @param	resource	$resource
	 * 		The resource to parse.
	 * 
	 * @param	string	$key
	 * 		The field of the table that will act as key.
	 * 
	 * @param	string	$value
	 * 		The field of the table that will act as value
	 * 
	 * @return	array
	 * 		The associative array.
	 */
	public function toArray($resource, $key, $value)
	{
		$result = array();
		while($row = $this->fetch($resource))
			$result[$row[$key]] = $row[$value];
		
		// Reset the iterator position
		$this->seek($resource, 0);
		
		return $result;
	}
	
	/**
	 * Sets the iterator of the resource to a given position
	 * 
	 * @param	resource	$resource
	 * 		The resource given
	 * 
	 * @param	integer	$row
	 * 		The position where the iterator will be placed
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	public function seek($resource, $row)
	{
		return $this->dbConnector->seek($resource, $row);
	}

	/**
	 * Returns the count of rows of the given resource
	 * 
	 * @param	resource	$resource
	 * 		The given resource
	 * 
	 * @return	number
	 * 		{description}
	 */
	public function get_num_rows($resource)
	{
		return $this->dbConnector->get_num_rows($resource);
	}
	
	/**
	 * Execute a given query to the database
	 * 
	 * @param	dbQuery	$dbQuery
	 * 		The dbQuery to be executed
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the query
	 * 
	 * @return	mixed
	 * 		{description}
	 * 
	 * @deprecated	Use execute() instead.
	 */
	public function execute_query($dbQuery, $attr = array())
	{
		return $this->execute($dbQuery, $attr);
	}
	
	/**
	 * Executes a general query transaction
	 * 
	 * @param	array	$dbQueries
	 * 		The list of queries to be executed as a transaction
	 * 
	 * @param	{type}	$attr
	 * 		{description}
	 * 
	 * @return	mixed
	 * 		{description}
	 * 
	 * @deprecated	Use execute() instead.
	 */
	public function execute_transaction($dbQueries, $attr = array())
	{
		if (!is_array($dbQueries))
				$this->execute_query($dbQuery, $attr, $user);
				
		$queries = array();
		foreach ($dbQueries as $dbQuery)
			$queries[] = $dbQuery->get_query($attr);
		
		$result = $this->dbConnector->execute_transaction($queries);
		return $result;
	}
	
	/**
	 * Transform a resource to an array with the specified key value assignment
	 * 
	 * @param	resource	$resource
	 * 		The resource to be transformed
	 * 
	 * @param	string	$key
	 * 		The field of the table that will act as key
	 * 
	 * @param	string	$value
	 * 		The field of the table that will act as value
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use toArray() instead.
	 */
	public function to_array($resource, $key, $value)
	{
		$result = array();
		while($row = $this->fetch($resource))
			$result[$row[$key]] = $row[$value];
		
		// Reset the iterator position
		$this->seek($resource, 0);
		
		return $result;
	}
	
	/**
	 * Transform a resource to a full array
	 * 
	 * @param	resource	$resource
	 * 		The resource to be transformed
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use fetch(resource, TRUE) instead.
	 */
	public function toFullArray($resource)
	{
		$result = array();
		while($row = $this->fetch($resource))
			$result[] = $row;
		
		// Reset the iterator position
		$this->seek($resource, 0);
		
		return $result;
	}
}
//#section_end#
?>