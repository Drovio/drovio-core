<?php
//#section#[header]
// Namespace
namespace API\Comm\database\connectors;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Comm
 * @namespace	\database\connectors
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "profiler::logger");

use \API\Developer\profiler\logger;

/**
 * MySQL Database Connector
 * 
 * Connector for MySQL Databases
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:33 (EEST)
 * @revised	July 30, 2013, 12:55 (EEST)
 */
class mysql_dbConnector
{
	/**
	 * Connection id
	 * 
	 * @type	string
	 */
	private $connection;
	/**
	 * The host URL
	 * 
	 * @type	string
	 */
	private $host;
	/**
	 * The user's username
	 * 
	 * @type	string
	 */
	private $username;
	/**
	 * The user's password
	 * 
	 * @type	string
	 */
	private $password;
	/**
	 * The database's name
	 * 
	 * @type	string
	 */
	private $database;
	
	/**
	 * Initialize the object with the proper properties
	 * 
	 * @param	string	$host
	 * 		The host's URL
	 * 
	 * @param	string	$username
	 * 		The user's username
	 * 
	 * @param	string	$password
	 * 		The user's password
	 * 
	 * @param	string	$database
	 * 		The database's name
	 * 
	 * @return	void
	 */
	public function setHandler($host, $username, $password, $database)
	{
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
	}
	
	/**
	 * Executes a query to the database. It supports multiple queries separated with ";".
	 * 
	 * @param	string	$query
	 * 		The query to be executed.
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
		// Check query
		$query = trim($query).";";
		
		// Split queries (if any, separated with ";")
		$queries = array();
		
		// Capture strings and ";"
		preg_match_all("/([\'\"])(?:(?!\\1)[^\\\\]|\\\\[\w\W])*\\1|;/", $query, $matches, PREG_OFFSET_CAPTURE);
		// Filter out strings
		$points = array_filter((array)$matches[0], function($candidate) {
			return is_array($candidate) && $candidate[0] == ";";
		});
		// Split on ";"
		$prev = 0;
		foreach ($points as $point)
		{
		     $queries[] = trim(substr($query, $prev, $point[1] - $prev));
		     $prev = $point[1]+1;
		}
		// Remove last query if empty
		if ($queries[count($queries)-1] == "")
			unset($queries[count($queries)-1]);
		
		// Log activity
		logger::log("Executing SQL Query to ".$this->host, logger::INFO, $query);
		
		return $this->executeTransaction($queries);
	}
	
	/**
	 * Executes a transaction to the database.
	 * 
	 * @param	array	$queries
	 * 		An array of query strings.
	 * 
	 * @return	mixed
	 * 		Returns FALSE on failure.
	 * 		For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a mysqli_result object.
	 * 		For other successful queries mysqli_query() will return TRUE.
	 * 
	 * @throws	Exception
	 */
	private function executeTransaction($queries)
	{
		// Connect to database
		$this->connect();
		
		// Disable autocommit for transaction
		mysqli_autocommit($this->connection, FALSE);
		
		// Start Transaction
		foreach ($queries as $q)
		{
			$result = @mysqli_query($this->connection, $q);
			if (!$result)
			{
				// Get Exception
				$exception = @mysqli_error($this->connection);
				
				// Rollback
				@mysqli_rollback($this->connection);
				
				// Disconnect
				$this->disconnect();
				
				// Throw the Exception
				throw new Exception($exception);
			}
		}
		
		// Commit Transaction
		@mysqli_commit($this->connection);
		
		// Disconnect from database
		$this->disconnect();
		
		// Return final result
		return $result;
	}
	
	/**
	 * Fetch an assoc row of the resource given
	 * 
	 * @param	resource	$resource
	 * 		The database resource.
	 * 
	 * @param	boolean	$full
	 * 		Whether to fetch as a full array with all the rows.
	 * 
	 * @return	array
	 * 		The fetched array with one or all the rows.
	 */
	public function fetch($resource, $full = FALSE)
	{
		// If set to fetch array
		if ($full)
		{
			$resultArray = array();
			while ($row = $this->fetch($resource))
				$resultArray[] = $row;
				
			return $resultArray;
		}
		
		// Fetch row
		return @mysqli_fetch_assoc($resource);
	}
	
	/**
	 * Adjusts the result pointer to an arbitrary row in the result
	 * 
	 * @param	resource	$resource
	 * 		The database resource.
	 * 
	 * @param	integer	$row
	 * 		The row number.
	 * 
	 * @return	boolean
	 * 		Returns TRUE on success or FALSE on failure.
	 */
	public function seek($resource, $row)
	{
		return @mysqli_data_seek($resource, $row);
	}
	
	/**
	 * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection.
	 * 
	 * @param	string	$resource
	 * 		The string we want to escape.
	 * 
	 * @return	string
	 * 		Returns an escaped string.
	 */
	public function escape($resource)
	{
		// Connect to database
		$this->connect();
		
		// Escape string
		$escaped = @mysqli_real_escape_string($this->connection, $resource);
		
		// Disconnect from database
		$this->disconnect();
		
		// Return escaped value
		return $escaped;
	}
	
	/**
	 * Get the resource row count.
	 * 
	 * @param	resource	$resource
	 * 		The database resource.
	 * 
	 * @return	integer
	 * 		Returns the number of rows of the resource.
	 */
	public function get_num_rows($resource)
	{
		return @mysqli_num_rows($resource);
	}
	
	/**
	 * Connects to the server.
	 * 
	 * @return	void
	 */
	private function connect()
	{
		// Connect to the database
		$this->connection = @mysqli_connect($this->host, $this->username, $this->password);
		
		// If no connection, throw Exception
		if (!$this->connection)
		{
			logger::log("Could not connect to MySQL Database Server '".$this->host."'.", logger::ERROR);
			throw new Exception("Could not connect to MySQL Database Server '".$this->host."'.");
		}
			
		// Set unicode names
		@mysqli_query($this->connection, "SET NAMES utf8");
		
		// Select database
		$this->select();
	}
	
	/**
	 * Selects the database.
	 * 
	 * @return	void
	 */
	private function select()
	{
		// Select Database
		$success = @mysqli_select_db($this->connection, $this->database);
		
		if (!$success)
		{
			logger::log("Could not select Database '".$this->database."'.", logger::ERROR);
			throw new Exception("Could not select Database '".$this->database."'.");
		}
	}
	
	/**
	 * Disconnects from the server.
	 * 
	 * @return	void
	 */
	private function disconnect()
	{
		// Disconnect from the server
		@mysqli_close($this->connection);
		
		// Unset variable
		unset($this->connection);
	}
	
	/**
	 * Execute a single query
	 * 
	 * @param	string	$query
	 * 		The SQL Query
	 * 
	 * @return	mixed
	 * 		{description}
	 * 
	 * @deprecated	Use execute() instead.
	 */
	public function execute_query($query)
	{
		// Log activity
		logger::log("Executing SQL query '".$query."' to ".$this->host, logger::INFO, "transaction description");
		
		try
		{
			// Connect to database
			$this->connect();
	
			// Set autocommit for query
			mysqli_autocommit($this->connection, TRUE);
	
			// Execute Query
			$result = mysqli_query($this->connection, $query);
			
			if (!$result)
			{
				// Get Exception
				$exception = mysqli_error($this->connection);
				
				// Rollback
				mysqli_rollback($this->connection);
				
				// Disconnect
				$this->disconnect();
				
				// Throw Exception
				throw new Exception($exception);
			}
			
			// Disconnect from database
			$this->disconnect();
		}
		catch (Exception $ex)
		{
			logger::log("Query Execution failed. Message error: ".$ex->getMessage(), 1);
			return NULL;
		}

		// Return final result
		return $result;
	}
	
	/**
	 * Execute a query transaction (queries separated by ;)
	 * 
	 * @param	string	$query
	 * 		The SQL Query
	 * 
	 * @return	mixed
	 * 		{description}
	 * 
	 * @deprecated	Use execute() instead.
	 */
	public function execute_query_transaction($query)
	{
		// Split queries
		$queries = array();
		$queries = explode(";", $query);
		
		// Remove last empty query
		if ($queries[count($queries)-1] == "")
			unset($queries[count($queries)-1]);
		
		// Execute Transaction
		return $this->execute_transaction($queries);
	}
	
	/**
	 * Execute a transaction (multiple queries)
	 * 
	 * @param	array	$queries
	 * 		The array of SQL Queries
	 * 
	 * @return	mixed
	 * 		{description}
	 * 
	 * @deprecated	Use execute() instead.
	 */
	public function execute_transaction($queries)
	{
		// Log activity
		logger::log("Executing SQL transaction to ".$this->host, logger::INFO, "transaction description");
		
		try
		{
			// Connect to database
			$this->connect();
			
			// Disable autocommit for transaction
			mysqli_autocommit($this->connection, FALSE);
			
			// Start Transaction
			foreach ($queries as $q)
			{
				$result = mysqli_query($this->connection, $q);
				if (!$result)
				{
					// Get Exception
					$exception = mysqli_error($this->connection);
					
					// Rollback
					mysqli_rollback($this->connection);
					
					// Disconnect
					$this->disconnect();
					
					// Throw Exception
					throw new Exception($exception);
				}
			}
			// Commit Transaction
			mysqli_commit($this->connection);
			
			// Disconnect from database
			$this->disconnect();
		}
		catch (Exception $ex)
		{
			logger::log("Transaction Execution failed. Message error: ".$ex->getMessage(), 1);
			return NULL;
		}
		
		// Return final result
		return $result;
	}
	
	/**
	 * Fetch an assoc array of the resource given
	 * 
	 * @param	resource	$resource
	 * 		The database resource
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use fetch() instead.
	 */
	public function fetch_all($resource)
	{
		return @mysqli_fetch_array($resource);
	}
	
	/**
	 * Clears a query from unescaped strings
	 * 
	 * @param	resource	$resource
	 * 		The database resource
	 * 
	 * @return	mixed
	 * 		{description}
	 * 
	 * @deprecated	Use escape() instead.
	 */
	public function clear_resource($resource)
	{
		try
		{
			// Connect to database
			$this->connect();
			
			// Clear string
			$escaped = mysqli_real_escape_string($this->connection, $resource);
			
			// Disconnect from database
			$this->disconnect();
		}
		catch (Exception $ex)
		{
			return NULL;
		}
		
		// Return escaped value
		return $escaped;
	}
}
//#section_end#
?>