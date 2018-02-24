<?php
//#section#[header]
// Namespace
namespace API\Comm\database;

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
 * @namespace	\database
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Comm", "database/connectors/mysql_dbConnector");
importer::import("DEV", "Profiler", "logger");

use \API\Comm\database\connectors\mysql_dbConnector;
use \DEV\Profiler\logger;

/**
 * Database Connection Manager
 * 
 * Connects to any database with the proper database connector.
 * It supports only MySQL for the time being.
 * 
 * @version	3.0-5
 * @created	November 10, 2014, 10:42 (GMT)
 * @updated	October 30, 2015, 17:54 (GMT)
 */
class dbConnection
{
	/**
	 * Database connector.
	 * 
	 * @type	iDbConnector
	 */
	protected $dbConnector;
	
	/**
	 * Database engine.
	 * 
	 * @type	string
	 */
	protected $dbType;
	/**
	 * The database URL host.
	 * 
	 * @type	string
	 */
	protected $host;
	/**
	 * The database name.
	 * 
	 * @type	string
	 */
	protected $database;
	
	/**
	 * The transaction error.
	 * 
	 * @type	string
	 */
	protected $error;
	
	/**
	 * Database username.
	 * 
	 * @type	string
	 */
	private $username;
	/**
	 * Database password.
	 * 
	 * @type	string
	 */
	private $password;
	
	/**
	 * Initialize the database connection manager.
	 * Set connection options.
	 * 
	 * @param	string	$dbType
	 * 		The database engine type.
	 * 
	 * @param	string	$host
	 * 		The database host URL.
	 * 
	 * @param	string	$database
	 * 		The database name.
	 * 
	 * @param	string	$username
	 * 		The user's username.
	 * 
	 * @param	string	$password
	 * 		The user's password.
	 * 
	 * @return	void
	 */
	public function __construct($dbType, $host, $database, $username, $password)
	{
		// Initialize Variables
		$this->options($dbType, $host, $database, $username, $password);
	}
	
	/**
	 * Set connection options.
	 * 
	 * @param	string	$dbType
	 * 		The database engine type.
	 * 
	 * @param	string	$host
	 * 		The database host URL.
	 * 
	 * @param	string	$database
	 * 		The database name.
	 * 
	 * @param	string	$username
	 * 		The user's username.
	 * 
	 * @param	string	$password
	 * 		The user's password.
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
	 * Executes a query to the database.
	 * It supports multiple queries separated with ";".
	 * It also supports attributes using %{attr_name} or {attr_name}.
	 * 
	 * @param	string	$query
	 * 		The query to be executed.
	 * 		It supports many queries separated by ";".
	 * 
	 * @param	array	$attr
	 * 		An associative array of the query attributes.
	 * 		The keys of the array will replace the query attributes with the array key values.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$commit
	 * 		Whether to commit the transaction  after the last query or not.
	 * 		It is TRUE by default.
	 * 
	 * @return	mixed
	 * 		Returns FALSE on failure.
	 * 		For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a mysqli_result object.
	 * 		For other successful queries mysqli_query() will return TRUE.
	 * 
	 * @throws	Exception
	 */
	public function execute($query, $attr = array(), $commit = TRUE)
	{
		// Clear error message
		$this->error = "";
		
		// Set query attributes
		foreach ($attr as $key => $value)
		{
			// Escape value
			$value = $this->escape($value);
			
			// Replace escaped value
			$query = str_replace("$".$key, $value, $query);
			$query = str_replace("%{".$key."}", $value, $query);
			$query = str_replace("{".$key."}", $value, $query);
		}
		
		try
		{
			// Log activity
			logger::getInstance()->log("Executing SQL Query to [".$this->database."] at ".$this->host, logger::DEBUG, $query);
			
			// Execute
			return $this->dbConnector->execute($query, $commit);
		}
		catch (Exception $ex)
		{
			// Store error message
			$this->error = $ex->getMessage();
			
			// Log Exception Message
			logger::getInstance()->log("Query Execution to [".$this->database."] at ".$this->host." failed: ".$this->error, logger::ERROR, $query);
			
			// Return false
			return FALSE;
		}
	}
	
	/**
	 * Commits the current transaction for the database connection.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function commit()
	{
		return $this->dbConnector->commit();
	}
	
	/**
	 * Rollbacks the current transaction for the database.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function rollback()
	{
		return $this->dbConnector->rollback();
	}
	
	/**
	 * Gets the error generated by the previous transaction executed.
	 * 
	 * @return	string
	 * 		The error message thrown by the database connector.
	 */
	public function getError()
	{
		return $this->error;
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
	 * Fetch all the results from resource.
	 * 
	 * @param	resource	$resource
	 * 		The database results resource.
	 * 
	 * @return	array
	 * 		An array of results.
	 */
	public function fetchAll($resource)
	{
		return $this->dbConnector->fetch($resource, TRUE);
	}
	
	/**
	 * Transform a resource to an array with the specified key value assignment.
	 * 
	 * @param	resource	$resource
	 * 		The resource to parse.
	 * 
	 * @param	string	$key
	 * 		The field of the table that will act as key.
	 * 
	 * @param	string	$value
	 * 		The field of the table that will act as value.
	 * 
	 * @return	array
	 * 		The associative array.
	 */
	public function toArray($resource, $key, $value)
	{
		$result = array();
		while ($row = $this->fetch($resource))
			$result[$row[$key]] = $row[$value];
		
		// Reset the iterator position
		$this->seek($resource, 0);
		
		return $result;
	}
	
	/**
	 * Sets the iterator of the resource to a given position
	 * 
	 * @param	resource	$resource
	 * 		The database resource.
	 * 
	 * @param	integer	$row
	 * 		The position where the iterator will be placed.
	 * 
	 * @return	void
	 */
	public function seek($resource, $row)
	{
		return $this->dbConnector->seek($resource, $row);
	}

	/**
	 * Returns the count of rows of the given resource.
	 * 
	 * @param	resource	$resource
	 * 		The database resource.
	 * 
	 * @return	number
	 * 		The row count of the given resource.
	 */
	public function get_num_rows($resource)
	{
		return $this->dbConnector->get_num_rows($resource);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$resource
	 * 		{description}
	 * 
	 * @param	{type}	$key
	 * 		{description}
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @return	void
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
	 * {description}
	 * 
	 * @param	{type}	$resource
	 * 		{description}
	 * 
	 * @return	void
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