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
/*
interface iDbConnector
{
	// Initialize the object with the proper properties
	public function setHandler($host, $user, $pass, $database);
	
	// Connect to the server
	public function connect();
	
	// Select the database
	public function select();
	
	// Disconnect from the server
	public function disconnect();
	
	// Execute a single query
	public function execute_query($query);
	
	// Execute a query transaction (queries separated by ;)
	public function execute_query_transaction($query);
	
	// Execute a transaction (multiple queries)
	public function execute_transaction($queries);
	
	// Fetch an assoc row
	public function fetch($resource);
	
	// Fetch an assoc array
	public function fetch_all($resource);
	
	// Seek a position in the resource
	public function seek($resource, $row);
	
	// Clears a resource from unescaped strings
	public function clear_resource($query);
	
	// Returns the number of rows of the resource
	public function get_num_rows($resource);
}
*/
//#section_end#
?>