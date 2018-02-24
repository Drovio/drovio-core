<?php
//#section#[header]
// Namespace
namespace DEV\BugTracker;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	BugTracker
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Security", "account");
importer::import("DEV", "Projects", "project");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Security\account;
use \DEV\Projects\project;
/**
 * Bug Tracker
 * 
 * Class to report and review bugs
 * 
 * @version	3.0-1
 * @created	June 30, 2014, 20:24 (EEST)
 * @revised	July 30, 2014, 21:11 (EEST)
 */
class bugTracker
{
	/**
	 * The folter were the bugs  data is stored
	 * 
	 * @type	string
	 */
	const FOLDER = "/Issues/";

	/**
	 * Severity identification : Feature request
	 * 
	 * @type	string
	 */
	const SV_FEATURE = 1;
	/**
	 * Severity identification : Minor bug
	 * 
	 * @type	string
	 */
	const SV_MINOR = 2;
	/**
	 * Severity identification : Major bug
	 * 
	 * @type	string
	 */
	const SV_MAJOR = 3;
	/**
	 * Severity identification : Critical bug
	 * 
	 * @type	string
	 */
	const SV_CRITICAL = 4; 
	
	/**
	 * Priority identification : high
	 * 
	 * @type	string
	 */
	const PR_HIGH = 5;
	/**
	 * Priority identification : normal
	 * 
	 * @type	string
	 */
	const PR_NORMAL = 3;
	/**
	 * Priority identification : low
	 * 
	 * @type	string
	 */
	const PR_LOW = 2;
		
        /**
         * Status identification : The bug is new
         * 
         * @type	string
         */
        const ST_NEW = "new";
        /**
         * Status identification : The bug was acknoledged by a mainteiner
         * 
         * @type	string
         */
        const ST_ACK = "acknowledged";
        /**
         * Status identification : The bug confirmed to be valid
         * 
         * @type	string
         */
        const ST_CONFIRMED = "confirmed";
        /**
         * Status identification : The bug was assigned to a developer
         * 
         * @type	string
         */
        const ST_ASSIGNED = "assigned";
        /**
         * Status identification : The bug was resolved
         * 
         * @type	string
         */
        const ST_RESOLVED = "resolved";
        /**
         * Status identification : The bug was rejected
         * 
         * @type	string
         */
        const ST_REJECTED = "rejected";


	/**
	 * The Constructor Method
	 * 
	 * @param	string	$pid
	 * 		The project id
	 * 
	 * @return	void
	 */
	public function __construct($pid)
	{
		$this->pid = $pid;
	}
	
	/**
	 * Registers a new bug
	 * 
	 * @param	string	$title
	 * 		The bug title
	 * 
	 * @param	string	$description
	 * 		The bug description
	 * 
	 * @param	string	$type
	 * 		The bug type
	 * 
	 * @param	string	$severity
	 * 		The bug severity
	 * 
	 * @param	string	$email
	 * 		The email of the user who filed the bug. Empty string if the user is a register user
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function fileBug($title, $description, $type, $severity = self::PR_LOW, $email = '')
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
		{
			$s = $this->createFile($filePath, $fileName);
			if(!$s)
				return FALSE;
		}
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);
		
		// New issue
		$entry = $parser->create("bug");
		$parser->append($root, $entry);
		$parser->attr($entry, "id", time());
		
		
		/* User - Reporter Defined Properties */
		$prop = $parser->create("title", $title);
		$parser->append($entry, $prop);
		
		$prop = $parser->create("description", $description);
		$parser->append($entry, $prop);
		
		$prop = $parser->create("type", $type);
		$parser->append($entry, $prop);
		
		$prop = $parser->create("severity", $severity);
		$parser->append($entry, $prop);	
		
		/* Developer - Bug Admin Defined Properties */
		$prop = $parser->create("valid", "1");
		$parser->append($entry, $prop);
		
		$prop = $parser->create("priority", strval(self::PR_NORMAL));
		$parser->append($entry, $prop);	
		
		$prop = $parser->create("notes", "");
		$parser->append($entry, $prop);
		
		
		/* System - Automatic Defined Properties */
		$prop = $parser->create("status", self::ST_NEW);
		$parser->append($entry, $prop);	
		
		
		$prop = $parser->create("date_created",  strval(time()));
		$parser->append($entry, $prop);
		
		$prop = $parser->create("date_updated",  strval(time()));
		$parser->append($entry, $prop);
		
		$info = account::info();
		if(!is_null($info))
		{
			$prop = $parser->create("account_id", $info['accountID']);
			$parser->append($entry, $prop);
		}
		else
		{
			$prop = $parser->create("email", $email);
			$parser->append($entry, $prop);
		}
		
	
		return $parser->update();
	}
	
	/**
	 * Changes / Set the status of a bug node
	 * 
	 * @param	DOMParser	$parser
	 * 		The DOMParser in which the bug data is loaded
	 * 
	 * @param	DOMNode	$issue
	 * 		The DOMnode representing the status node
	 * 
	 * @param	string	$value
	 * 		The status value to be set
	 * 
	 * @return	DOMNode
	 * 		The changed DOMNode
	 */
	private function _setStatus($parser, $issue, $value)
	{
		if(!is_null($issue))
		{				
			$prop = $parser->evaluate("status", $issue)->item(0);	
			$prop->nodeValue = $value;
		}
		return $issue;
	}
	
	/**
	 * Changes / Set the priority of a bug node
	 * 
	 * @param	DOMParser	$parser
	 * 		The DOMParser in which the bug data is loaded
	 * 
	 * @param	DOMNode	$issue
	 * 		The DOMnode representing the status node
	 * 
	 * @param	string	$value
	 * 		The priority value to be set
	 * 
	 * @return	DOMNode
	 * 		The changed DOMNode
	 */
	private function _setPriority($parser, $issue, $value)
	{
		if(!is_null($issue))
		{				
			$prop = $parser->evaluate("priority", $issue)->item(0);	
			$prop->nodeValue = $value;
		}
		return $issue;
	}
	
	/**
	 * Changes / Set the severity of a bug node
	 * 
	 * @param	DOMParser	$parser
	 * 		The DOMParser in which the bug data is loaded
	 * 
	 * @param	DOMNode	$issue
	 * 		The DOMnode representing the status node
	 * 
	 * @param	string	$value
	 * 		The severity value to be set
	 * 
	 * @return	DOMNode
	 * 		The changed DOMNode
	 */
	private function _setSeverity($parser, $issue, $value)
	{
		if(!is_null($issue))
		{				
			$prop = $parser->evaluate("severity", $issue)->item(0);	
			$prop->nodeValue = $value;
		}
		return $issue;
	}
	
	/**
	 * Changes / Set the bug status
	 * 
	 * @param	string	$id
	 * 		The bug id
	 * 
	 * @param	string	$value
	 * 		The status to be set
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function setStatus($id, $value)
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
			return FALSE;
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);		 
		$issue = $parser->evaluate('bug[@id="'.$id.'"]', $root)->item(0);
		
		if(!is_null($issue))
		{
			$this->_setStatus($parser, $issue, $value);
			return  $parser->update();
		}
		return FALSE;
	}
	
	/**
	 * Changes / Set the bug severity
	 * 
	 * @param	string	$id
	 * 		The bug id
	 * 
	 * @param	string	$value
	 * 		The status to be set
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function setSeverity($id, $value)
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
			return FALSE;
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);		 
		$issue = $parser->evaluate('bug[@id="'.$id.'"]', $root)->item(0);
		
		if(!is_null($issue))
		{
			$this->_setSeverity($parser, $issue, $value);
			return  $parser->update();
		}
		return FALSE;
	}
	
	/**
	 * Changes / Set the bug priority
	 * 
	 * @param	string	$id
	 * 		The bug id
	 * 
	 * @param	string	$value
	 * 		The status to be set
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function setPriority($id, $value)
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
			return FALSE;
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);		 
		$issue = $parser->evaluate('bug[@id="'.$id.'"]', $root)->item(0);
		
		if(!is_null($issue))
		{
			$this->_setPriority($parser, $issue, $value);
			return  $parser->update();
		}
		return FALSE;
	}
	
	/**
	 * Get an array of all bugs
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use getBugList instead
	 */
	public function getAllBugs()
	{
		return $this->getBugList();
	}
	
	/**
	 * Assigns the bug to a developer
	 * 
	 * @param	string	$id
	 * 		The bug id
	 * 
	 * @param	string	$accoundId
	 * 		The user account id
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function assign($id, $accoundId)
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
			return FALSE;
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);		 
		$issue = $parser->evaluate('bug[@id="'.$id.'"]', $root)->item(0);
		
		if(!is_null($issue))
		{
			$prop  = $parser->evaluate("status", $issue)->item(0);
			$prop->nodeValue = self::ST_ASSIGNED;
		
			$prop = $parser->evaluate("dateUpdated",  $issue)->item(0);
			$prop->nodeValue = strval(time());
			
			$prop = $parser->evaluate("assignedTo",  $issue)->item(0);			
			if(is_null($prop))
			{
				$prop = $parser->create("assignedTo");
				$parser->append($issue, $prop);
			}
			$prop->nodeValue = strval($accoundId);
			
			return  $parser->update();
		}
		return FALSE;
	}
	
	/**
	 * Get an array of all bugs
	 * 
	 * @param	string	$filter
	 * 		Parameter to filter the retrieved bugs
	 * 
	 * @param	string	$order
	 * 		Parameter to order the retrieved bugs
	 * 
	 * @param	integer	$start
	 * 		Number from where and after the results will be return
	 * 
	 * @param	integer	$limit
	 * 		The number of the results that will be returned
	 * 
	 * @return	array
	 * 		Array of bugs, each 'bug' is an array holding bugs properties.
	 */
	public function getBugList($assignedTo = '', $filter = '', $order = '', $start = 0, $limit ='')
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
			return array();
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		$filterBean = '';
		if(!empty($filter))
			$filterBean .='[status="'.$filter.'"]';
		if(!empty($assignedTo))
			$filterBean .='[assignedTo="'.$assignedTo.'"]';
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);
		$issueList = $parser->evaluate("bug".$filterBean, $root);
		 
		$issuesArray = array();
		foreach($issueList as $issue) 
		{			
			$item = array();
		
			$item['id'] = $parser->attr($issue, "id");			
			$item['title'] = $parser->evaluate("title", $issue)->item(0)->nodeValue;
			$item['type'] = $parser->evaluate("type", $issue)->item(0)->nodeValue; // not by the user
			$item['severity'] = $parser->evaluate("severity", $issue)->item(0)->nodeValue; // delete
			$item['date'] = $parser->evaluate("date_created", $issue)->item(0)->nodeValue;
			$item['valid'] = $parser->evaluate("valid", $issue)->item(0)->nodeValue; // delete
			$item['status'] = $parser->evaluate("status", $issue)->item(0)->nodeValue; 
			$item['priority'] = $parser->evaluate("priority", $issue)->item(0)->nodeValue; 
			$item['dateUpdated'] = $parser->evaluate("date_updated", $issue)->item(0)->nodeValue; 
			$item['assignedTo'] = $parser->evaluate("assignedTo", $issue)->item(0)->nodeValue;  
			$item['dateAssigned'] = $parser->evaluate("dateAssigned", $issue)->item(0)->nodeValue;
			$item['accountID'] = $parser->evaluate("account_id", $issue)->item(0)->nodeValue;
			
			
			$issuesArray[] = $item;
		}
		
		// Order the array
		// Obtain a list of columns
		if(!empty($order))
		{
			foreach ($issuesArray as $key => $row) 
			{ 
				$orderingValue[$key]  = $row[$order];
			}
			// Sort the data with volume descending, edition ascending
			// Add $data as the last parameter, to sort by the common key
			array_multisort($orderingValue, SORT_DESC, $issuesArray);
			//array_multisort($orderingValue, SORT_ASC, $issuesArray);
		}
		
		// Slice array if pagination parameters are present
		if(!empty($limit))
		{
			$issuesArray = array_slice($issuesArray, $start, $limit); 
		}
		
		
		return $issuesArray;
	}
	
	/**
	 * Get an array with the information of a bug
	 * 
	 * @param	string	$id
	 * 		the bug id
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getBug($id)
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
			return array();
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);
		 
		$issue = $parser->evaluate('bug[@id="'.$id.'"]', $root)->item(0);
		
		$item = array();
		if(!is_null($issue))
		{ 
			$item['id'] = $parser->attr($issue, "id");			
			$item['title'] = $parser->evaluate("title", $issue)->item(0)->nodeValue;
			$item['description'] = $parser->evaluate("description", $issue)->item(0)->nodeValue;
			$item['type'] = $parser->evaluate("type", $issue)->item(0)->nodeValue; // not by the user
			$item['severity'] = $parser->evaluate("severity", $issue)->item(0)->nodeValue; // delete
			$item['date'] = $parser->evaluate("date_created", $issue)->item(0)->nodeValue;
			$item['valid'] = $parser->evaluate("valid", $issue)->item(0)->nodeValue; // delete
			$item['status'] = $parser->evaluate("status", $issue)->item(0)->nodeValue; 
			$item['priority'] = $parser->evaluate("priority", $issue)->item(0)->nodeValue; 
			$item['notes'] = $parser->evaluate("notes", $issue)->item(0)->nodeValue; 
			$item['dateUpdated'] = $parser->evaluate("date_updated", $issue)->item(0)->nodeValue; 
			$item['assignedTo'] = $parser->evaluate("assignedTo", $issue)->item(0)->nodeValue;  
			$item['dateAssigned'] = $parser->evaluate("dateAssigned", $issue)->item(0)->nodeValue;
			$item['accountID'] = $parser->evaluate("account_id", $issue)->item(0)->nodeValue;
		}
			
		return $item;
	}
	
	/**
	 * Resolves the bug
	 * 
	 * @param	string	$id
	 * 		The bug id
	 * 
	 * @param	string	$comments
	 * 		The developers notes for her actions
	 * 
	 * @param	string	$status
	 * 		The staus to be assigned on the bug
	 * 
	 * @return	void
	 */
	public function solveBug($id, $comments, $status = self::ST_RESOLVED)
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = $this->getServiceFolder();
		if (!file_exists(systemRoot.$filePath.$fileName))
			return FALSE;
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);		 
		$issue = $parser->evaluate('bug[@id="'.$id.'"]', $root)->item(0);
		
		if(!is_null($issue))
		{
			$prop  = $parser->evaluate("status", $issue)->item(0);
			$prop->nodeValue = $status;
		
			//$prop = $parser->create("dateSolved",  strval(time()));
			$prop = $parser->evaluate("dateUpdated",  $issue)->item(0);
			$prop->nodeValue = strval(time());
			
			$prop = $parser->evaluate("notes",  $issue)->item(0);
			$prop->nodeValue = $comments;
			
			return  $parser->update();
		}
		return FALSE;
	}
	
	/**
	 * Given the priority code returns the priority user friendly name
	 * 
	 * @param	integer	$priority
	 * 		The priority code as it is defined by classes const values
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getPriorityName($priority)
	{
		$array = self::getPriorityOptions();
		return $array[$priority];
	}
	
	/**
	 * Get all available priority options
	 * 
	 * @return	array
	 * 		An associative array code => frindly name
	 */
	public static function getPriorityOptions()
	{
		$priority = array();  
		$priority[self::PR_HIGH] = 'high';
		$priority[self::PR_NORMAL] = 'normal';
		$priority[self::PR_LOW] = 'low';	
		return $priority;
	}
	
	/**
	 * Given the severity code returns the severity user friendly name
	 * 
	 * @param	integer	$severity
	 * 		The severity code as it is defined by classes const values
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getSeverityName($severity)
	{
		$array = self::getSeverityOptions();
		return $array[$severity];
	}
	
	/**
	 * Get all available severityoptions
	 * 
	 * @return	array
	 * 		An associative array code => frindly name
	 */
	public static function getSeverityOptions()
	{
		$severity = array(); 
		$severity[self::SV_FEATURE] = 'feature'; 
		$severity[self::SV_MINOR] = 'minor';
		$severity[self::SV_MAJOR] = 'major';
		$severity[self::SV_CRITICAL] = 'critical'; 	
		return $severity;
	}
	
	
	/**
	 * Creates the file for the bugs to be stored
	 * 
	 * @param	string	$filePath
	 * 		The path to the folder
	 * 
	 * @param	string	$fileName
	 * 		the name of the file
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	private function createFile($filePath, $fileName)
	{
		fileManager::create(systemRoot.$filePath.$fileName, "", TRUE);
		
		// Create DOMParser
		$parser = new DOMParser();
		
		// Create root
		$root = $parser->create("BugTracker");
		$parser->append($root);
		
		$entry = $parser->create("BT_Bug");
		$parser->append($root, $entry);
		
		$entry = $parser->create("BT_Exception");
		$parser->append($root, $entry);
		
		// Save Log
		return $parser->save(systemRoot.$filePath, $fileName, TRUE);
	}

	/**
	 * Returns the path to the folder that bug files are stored
	 * 
	 * @return	string
	 * 		{description}
	 */
	private function getServiceFolder()
	{
		$project = new project($this->pid);
		$dir = $project->getRootFolder().self::FOLDER;
		return $dir;
	}
	
	/**
	 * Return the a hashed unique name for the bug data file according to the project it belongs to
	 * 
	 * @param	string	$id
	 * 		the project id
	 * 
	 * @return	string
	 * 		{description}
	 */
	private function getFileName($id)
	{
		return "bt".md5("bugs_".$id).".xml";
	}
}
//#section_end#
?>