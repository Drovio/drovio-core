<?php
//#section#[header]
// Namespace
namespace DEV\Projects;

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
 * @package	Projects
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "account");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;
use \API\Security\account;

/**
 * Developer Project
 * 
 * Manages developer projects, creating, updating and getting the proper information.
 * 
 * @version	{empty}
 * @created	February 18, 2014, 12:04 (EET)
 * @revised	February 27, 2014, 11:51 (EET)
 */
class project
{
	/**
	 * The project id.
	 * 
	 * @type	integer
	 */
	private $id;
	/**
	 * The project unique name.
	 * 
	 * @type	string
	 */
	private $name;
	
	/**
	 * Initializes the project by id or by name.
	 * 
	 * @param	integer	$id
	 * 		The project id.
	 * 
	 * @param	string	$name
	 * 		The project name.
	 * 
	 * @return	void
	 */
	public function __construct($id = "", $name = "")
	{
		// Load project information
		$dbc = new interDbConnection();
		
		$attr = array();
		if (!empty($id))
		{
			$attr['id'] = $id;
			$dbq = new dbQuery("826439764", "developer.projects");
		}
		else if (!empty($name))
		{
			$attr['name'] = $name;
			$dbq = new dbQuery("406610094", "developer.projects");
		}

		if (!empty($attr))
		{
			$result = $dbc->execute($dbq, $attr);
			$project = $dbc->fetch($result);

			if (!empty($project))
			{
				$this->id = $project['id'];
				$this->name = $project['name'];
			}
		}
	}
	
	/**
	 * Create a new developer project.
	 * 
	 * @param	string	$title
	 * 		The project title.
	 * 
	 * @param	string	$type
	 * 		The project type.
	 * 
	 * @param	string	$description
	 * 		The project description.
	 * 
	 * @return	mixed
	 * 		If success, return the project id created. FALSE otherwise.
	 */
	public function create($title, $type, $description = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("906810998", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['type'] = $type;
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
		{
			$this->id = $project['id'];
			return $this->id;
		}
		
		return FALSE;
	}
	
	/**
	 * Gets the project information with the given id or name.
	 * 
	 * @return	mixed
	 * 		An array of information if project found, NULL otherwise.
	 */
	public function info()
	{
		$dbc = new interDbConnection();
		$dbq = new dbQuery("826439764", "developer.projects");
		
		// If id not isset, return NULL
		if (!isset($this->id))
			return NULL;
		
		$attr = array();
		$attr['id'] = $this->id;
		$result = $dbc->execute($dbq, $attr);
		
		$project = $dbc->fetch($result);
		if ($project)
			return $project;
		
		return NULL;
	}
	
	/**
	 * Updates a project's basic information.
	 * 
	 * @param	string	$title
	 * 		The project's new title.
	 * 
	 * @param	string	$description
	 * 		The project's new description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($title, $description = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("994839582", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Sets the unique project name.
	 * 
	 * @param	string	$name
	 * 		The project's unique name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setName($name)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("2100666044", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Sets the project's category.
	 * 
	 * @param	string	$category
	 * 		The project category.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setCategory($category)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("892768812", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		$attr['category'] = $category;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Update a project's status.
	 * 
	 * @param	integer	$status
	 * 		The project's new status id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateStatus($status)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("254607052", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		$attr['status'] = $status;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Get the project's repository folder.
	 * 
	 * @return	string
	 * 		The project's repository folder.
	 */
	public function getRepository()
	{
		$repository = paths::getRepositoryPath();
		$projectFolder = "p".md5("devProject_".$this->id).".project";
		
		return $repository."/".$projectFolder;
	}
	
	/**
	 * Get the project's publish folder.
	 * 
	 * @return	string
	 * 		The project's publish folder.
	 */
	public function getPublishedPath()
	{
		$library = paths::getPublishedPath();
		$projectFolder = "p".md5("devProject_".$this->id).".project";
		
		return $library."/".$projectFolder;
	}
	
	/**
	 * Gets all account's projects.
	 * 
	 * @return	array
	 * 		An array of all projects.
	 */
	public static function getMyProjects()
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("25020897459919", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($dbq, $attr);
		
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Validates whether the logged in account has access to this project.
	 * 
	 * @return	boolean
	 * 		True is account has access, false otherwise.
	 */
	public function validate()
	{
		// Get account projects
		$dbc = new interDbConnection();
		$dbq = new dbQuery("25020897459919", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($dbq, $attr);
		
		// Check if project id is in projects
		while ($project = $dbc->fetch($result))
			if ($project['id'] == $this->id)
				return TRUE;
			
		return FALSE;
	}
	
	/**
	 * Add an account to the project.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to be added to the project.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function addAccountToProject($accountID)
	{
		// Get account projects
		$dbc = new interDbConnection();
		$dbq = new dbQuery("17407435658338", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['pid'] = $this->id;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get all project accounts with the person information along.
	 * In case of a managed account, the person information should be ignored.
	 * 
	 * @return	array
	 * 		An array of all account and person information.
	 * 		It also includes the accountID and the personID fields separately.
	 */
	public function getProjectAccounts()
	{
		// Get account projects
		$dbc = new interDbConnection();
		$dbq = new dbQuery("32597504132115", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $this->id;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>