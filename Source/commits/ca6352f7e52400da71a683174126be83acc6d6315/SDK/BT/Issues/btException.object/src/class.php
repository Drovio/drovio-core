<?php
//#section#[header]
// Namespace
namespace BT\Issues;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BT
 * @package	Issues
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("API", "Model", "sql/dbQuery");
importer::import("BT", "Comm", "dbConnection");

use \ESS\Protocol\AsCoProtocol;
use \API\Model\sql\dbQuery;
use \BT\Comm\dbConnection;

/**
 * Bug Tracker Exception
 * 
 * This class is responsible managing a project exception and for for logging a new Exception.
 * 
 * @version	0.1-2
 * @created	January 5, 2015, 11:07 (EET)
 * @updated	January 23, 2015, 16:36 (EET)
 */
class btException
{
	/**
	 * The project id.
	 * 
	 * @type	integer
	 */
	private $projectID;
	/**
	 * The exception id.
	 * 
	 * @type	integer
	 */
	private $exceptionID;
	
	/**
	 * Initialize the exception with the project id.
	 * 
	 * @param	integer	$projectID
	 * 		The exception's project id.
	 * 
	 * @param	integer	$exceptionID
	 * 		The exception id (for editing existing exceptions).
	 * 		Leave empty for new exceptions.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function __construct($projectID, $exceptionID = "")
	{
		// Initialize variables
		$this->projectID = $projectID;
		$this->exceptionID = $exceptionID;
	}
	
	/**
	 * Log a new exception to the project.
	 * 
	 * @param	Exception	$exception
	 * 		The exception to log.
	 * 		The function will get all the needed information from the exception object.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($exception)
	{
		// Create exception record to database
		$dbc = new dbConnection();
		$q = new dbQuery("21749972172911", "developer.issues.exceptions");
		
		$attr = array();
		$attr['project_id'] = $this->projectID;
		$attr['code'] = $exception->errCode;
		$attr['message'] = $exception->getMessage();
		$attr['trace'] = $exception->getTraceAsString();
		$attr['server_info'] = $this->getServerInfo();
		$attr['extra_info'] = $this->getExtraInfo();
		$attr['time'] = time();
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Assign the exception to an existing issue.
	 * 
	 * @param	integer	$issueID
	 * 		The issue id to assign the exception.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function assignToIssue($issueID)
	{
		if (empty($this->projectID) || empty($this->exceptionID))
			return FALSE;
		
		// Assign exception to issue
		$dbc = new dbConnection();
		$q = new dbQuery("", "developer.issues.exceptions");
		
		$attr = array();
		$attr['exception_id'] = $this->exceptionID;
		$attr['issue_id'] = $issueID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all server information in json format.
	 * 
	 * @return	string
	 * 		The server information, including request parameters (for get and post methods) in json format.
	 */
	private function getServerInfo()
	{
		// Initialize server info
		$serverInfo = array();
		
		// Add all server variables
		$serverInfo['SERVER'] = $_SERVER;
		
		// Set request method and add request data
		$serverInfo['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];
		if ($_SERVER['REQUEST_METHOD'] == "GET")
			$request_data = $_GET;
		else
			$request_data = $_POST;
		$serverInfo['REQUEST_DATA'] = $request_data;
		
		// Json encode and return
		return json_encode($serverInfo);
	}
	
	/**
	 * Get all extra information in json format.
	 * It includes the path and the subdomain where the exception thrown.
	 * 
	 * @return	string
	 * 		The extra information in json format.
	 */
	private function getExtraInfo()
	{
		// Initialize extra info
		$extraInfo = array();
		
		// Get subdomain paths
		$subdomain = AsCoProtocol::getSubdomain();
		$extraInfo['subdomain'] = (empty($subdomain) ? "www" : $subdomain);
		$extraInfo['path'] = AsCoProtocol::getPath();
		
		// Json encode and return
		return json_encode($extraInfo);
	}
	
	/**
	 * Get all project exceptions.
	 * 
	 * @return	array
	 * 		An array of all exceptions in the given project.
	 */
	public function getAll()
	{
		// Get all exceptions for the current project
		$dbc = new dbConnection();
		$dbq = new dbQuery("34081730638883", "developer.issues");
		$attr = array();
		$attr['pid'] = $this->projectID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>