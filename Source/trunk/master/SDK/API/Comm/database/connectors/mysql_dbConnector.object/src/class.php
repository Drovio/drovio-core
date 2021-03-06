<?php
//#section#[header]
// Namespace
namespace API\Comm\database\connectors;

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
 * @namespace	\database\connectors
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * MySQL Database Connector
 * 
 * Connector for MySQL Databases
 * 
 * @version	2.0-2
 * @created	April 23, 2013, 13:33 (EEST)
 * @updated	October 7, 2015, 23:53 (EEST)
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
	 * All types of query are considered transactions.
	 * 
	 * @param	string	$query
	 * 		The query to be executed.
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
	public function execute($query, $commit = TRUE)
	{
		// Check query
		$query = trim($query).";";
		
		// Capture strings and ";" and Filter out strings
		preg_match_all("/([\'\"])(?:(?!\\1)[^\\\\]|\\\\[\w\W])*\\1|;/", $query, $matches, PREG_OFFSET_CAPTURE);
		$points = array_filter((array)$matches[0], function($candidate) {
			return is_array($candidate) && $candidate[0] == ";";
		});
		
		// Split on ";"
		$prev = 0;
		$queries = array();
		foreach ($points as $point)
		{
		     $queries[] = trim(substr($query, $prev, $point[1] - $prev));
		     $prev = $point[1] + 1;
		}
		
		// Remove last query if empty
		$lastIndex = count($queries) - 1;
		if (empty($queries[$lastIndex]))
			unset($queries[$lastIndex]);
		
		// Execute transaction
		return $this->executeTransaction($queries, $commit);
	}
	
	/**
	 * Executes a transaction to the database.
	 * 
	 * @param	array	$queries
	 * 		An array of query strings.
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
	private function executeTransaction($queries, $commit = TRUE)
	{
		// Connect to database
		$this->connect();
		
		// Disable autocommit for transaction
		mysqli_autocommit($this->connection, FALSE);
		
		// Begin transaction (this also enables table locking for security)
		mysqli_query($this->connection, "BEGIN TRANSACTION");
		// PHP >= 5.5.0
		// mysqli_begin_transaction($this->connection);
		
		// Execute all transaction queries
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

				// Throw the Exception
				throw new Exception($exception);
			}
		}
		
		// Commit Transaction
		if ($commit)
			mysqli_commit($this->connection);
		
		// Disconnect from database
		$this->disconnect();
		
		// Return final result
		return $result;
	}
	
	/**
	 * Commits the current transaction for the database connection.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function commit()
	{
		// Commit to database
		return mysqli_commit($this->connection);
	}
	
	/**
	 * Rollbacks the current transaction for the database.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function rollback()
	{
		// Rollback database to previous state
		return mysqli_rollback($this->connection);
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
			
			// PHP >= 5.3
			// return @mysqli_fetch_all($resource);
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
	 * 
	 * @throws	Exception
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
	 * 
	 * @throws	Exception
	 */
	private function connect()
	{
		// Connect to the database
		$this->connection = @mysqli_connect($this->host, $this->username, $this->password);
		
		// If no connection, throw Exception
		if (!$this->connection)
			throw new Exception("Could not connect to MySQL Database Server '".$this->host."'.");
			
		// Set unicode names
		@mysqli_query($this->connection, "SET NAMES utf8");
		
		// Select database
		$this->select();
	}
	
	/**
	 * Selects the database.
	 * 
	 * @return	void
	 * 
	 * @throws	Exception
	 */
	private function select()
	{
		// Check if there is a database to select
		if (empty($this->database))
			return;
			
		// Select Database
		$success = @mysqli_select_db($this->connection, $this->database);
		
		if (!$success)
			throw new Exception("Could not select Database '".$this->database."'.");
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
}
//#section_end#
?>