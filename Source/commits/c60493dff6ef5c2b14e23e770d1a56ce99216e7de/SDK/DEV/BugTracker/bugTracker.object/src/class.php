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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Security", "account");

use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Security\account;

/**
 * Bug Tracker
 * 
 * Class to report and review bugs
 * 
 * @version	{empty}
 * @created	June 30, 2014, 20:24 (EEST)
 * @revised	July 4, 2014, 21:07 (EEST)
 */
class bugTracker
{
	/**
	 * The folter were the bugs  data is stored
	 * 
	 * @type	string
	 */
	const FOLDER = "/bugs/";

	/**
	 * Severity identification : Feature request
	 * 
	 * @type	string
	 */
	const SV_FEATURE = 'feature';
	/**
	 * Severity identification : Minor bug
	 * 
	 * @type	string
	 */
	const SV_MINOR = 'minor';
	/**
	 * Severity identification : Major bug
	 * 
	 * @type	string
	 */
	const SV_MAJOR = 'major';
	/**
	 * Severity identification : Critical bug
	 * 
	 * @type	string
	 */
	const SV_CRITICAL = 'critical'; 
	
	/**
	 * Priority identification : high
	 * 
	 * @type	string
	 */
	const PR_HIGH = 'high';
	/**
	 * Priority identification : normal
	 * 
	 * @type	string
	 */
	const PR_NORMAL = 'normal';
	/**
	 * Priority identification : low
	 * 
	 * @type	string
	 */
	const PR_LOW = 'low';
		
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
		$filePath = paths::getSysDynRsrcPath().self::FOLDER;
		if (!file_exists(systemRoot.$filePath.$fileName))
		{
			$s = $this->createFile(self::FOLDER, $fileName);
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
		
		$prop = $parser->create("priority", self::PR_NORMAL);
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
	 * Changes / Set the bug status
	 * 
	 * @param	DOMParser	$parser
	 * 		The DOMParser in which the bug data is loaded
	 * 
	 * @param	DOMnode	$issue
	 * 		The DOMnode representing the status node
	 * 
	 * @param	string	$status
	 * 		The status to be set
	 * 
	 * @return	boolean
	 * 		True on success, false on failure
	 */
	public function setStatus($parser, $issue, $status)
	{
		if(!is_null($issue))
		{				
			$prop = $parser->evaluate("bug/status", $issue)->item(0);	
			$prop->nodeValue = $status;
		}
		return $issue;
	}
	
	/**
	 * Get an array of all bugs
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getAllBugs()
	{
		// Check if log file exists
		$fileName = $this->getFileName($this->pid);
		$filePath = paths::getSysDynRsrcPath().self::FOLDER;
		if (!file_exists(systemRoot.$filePath.$fileName))
			return array();
			
		// Load log file 
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//BT_Bug")->item(0);
		$issueList = $parser->evaluate("bug", $root);
		 
		$issuesArray = array();
		foreach($issueList as $issue) 
		{			
			$item = array();
		
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
			
			
			$issuesArray[] = $item;
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
		$filePath = paths::getSysDynRsrcPath().self::FOLDER;
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
		}
			
		return $item;
	}
	
	/**
	 * Creates the file for the bugs to be stored
	 * 
	 * @param	string	$contentFolder
	 * 		The path to the folder
	 * 
	 * @param	string	$fileName
	 * 		the name of the file
	 * 
	 * @return	void
	 */
	private function createFile($contentFolder, $fileName)
	{
		$filePath = paths::getSysDynRsrcPath().$contentFolder;
		//echo systemRoot.$filePath.$fileName;
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