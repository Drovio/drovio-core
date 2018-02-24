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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "account");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Projects", "projectStatus");

use \SYS\Comm\db\dbConnection;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;
use \API\Security\account;
use \API\Resources\filesystem\folderManager;
use \DEV\Version\vcs;
use \DEV\Projects\projectStatus;

/**
 * Developer Project
 * 
 * Manages developer projects, creating, updating and getting the proper information.
 * 
 * @version	v. 0.1-0
 * @created	February 18, 2014, 12:04 (EET)
 * @revised	July 11, 2014, 20:41 (EEST)
 */
class project
{
	/**
	 * The project id.
	 * 
	 * @type	integer
	 */
	protected $id;
	/**
	 * The project unique name.
	 * 
	 * @type	string
	 */
	protected $name;
	
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
		$this->id = $id;
		$this->name = $name;
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
		$dbc = new dbConnection();
		
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
			// Get id
			$this->id = $project['id'];
			
			// Create source folder
			$this->getRepository();
			
			// Create resources folder
			$this->getResourcesFolder();
			
			// Return project id
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
		$dbc = new dbConnection();
		$dbq = new dbQuery("826439764", "developer.projects");
		
		// If info not set, return NULL
		$this->loadInfo();
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
	 * Gets the project id.
	 * 
	 * @return	integer
	 * 		The project id.
	 */
	public function getID()
	{
		$this->loadInfo();
		return $this->id;
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
		$this->loadInfo();
		$dbc = new dbConnection();
		
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
		$this->loadInfo();
		$dbc = new dbConnection();
		
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
		$this->loadInfo();
		$dbc = new dbConnection();
		
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
	 * 
	 * @deprecated	Use \DEV\Projects\projectStatus::update() instead.
	 */
	public function updateStatus($status)
	{
		projectStatus::update($this->id, $status);
	}
	
	/**
	 * Gets the project's roof repository folder
	 * 
	 * @return	string
	 * 		The project's root folder.
	 */
	public function getRootFolder()
	{
		$this->loadInfo();
		$repository = paths::getRepositoryPath();
		$innerFolder = "p".md5("devProject_".$this->id).".project";
		
		$projectFolder = $repository."/".$innerFolder;
		if (!file_exists(systemRoot.$projectFolder))
			folderManager::create(systemRoot.$projectFolder);
		
		return $projectFolder;
	}
	
	/**
	 * Get the project's repository folder.
	 * 
	 * @return	string
	 * 		The project's repository folder.
	 */
	public function getRepository()
	{
		$sourceFolder = $this->getRootFolder()."/Source";
		if (!file_exists(systemRoot.$sourceFolder))
		{
			// Create folder
			folderManager::create(systemRoot.$sourceFolder);
			
			// Create repository structure
			$vcs = new vcs($this->id);
			$vcs->createStructure();
		}
		
		return $sourceFolder;
	}
	
	/**
	 * Get the project's resources folder.
	 * 
	 * @return	void
	 */
	public function getResourcesFolder()
	{
		$resourcesFolder = $this->getRootFolder()."/Resources";
		if (!file_exists(systemRoot.$resourcesFolder))
			folderManager::create(systemRoot.$resourcesFolder);
		
		return $resourcesFolder;
	}
	
	/**
	 * Publishes all the resources of the current project to the given folder.
	 * 
	 * @param	string	$publishFolder
	 * 		The published resource folder.
	 * 
	 * @return	void
	 */
	public function publishResources($publishFolder)
	{
		// Create or clean the publish folder
		folderManager::create(systemRoot.$publishFolder);
		folderManager::clean(systemRoot.$publishFolder);
		
		// Copy resources to publish folder
		$resourceFolder = $this->getResourcesFolder();
		folderManager::copy(systemRoot.$resourceFolder, systemRoot.$publishFolder, TRUE);
	}
	
	/**
	 * Get the project's publish folder.
	 * 
	 * @return	string
	 * 		The project's publish folder.
	 */
	public function getPublishedPath()
	{
		$this->loadInfo();
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
		$dbc = new dbConnection();
		
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
		$this->loadInfo();
		$dbc = new dbConnection();
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
		// Init
		$this->loadInfo();
		$dbc = new dbConnection();
		$dbq = new dbQuery("17407435658338", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['pid'] = $this->id;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Remove an account from the project.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeAccountFromProject($accountID)
	{
		// Init
		$this->loadInfo();
		$dbc = new dbConnection();
		$dbq = new dbQuery("26838717507082", "developer.projects");
		
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
		$this->loadInfo();
		$dbc = new dbConnection();
		$dbq = new dbQuery("32597504132115", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $this->id;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Load project info from database if project id is not set.
	 * 
	 * @return	void
	 */
	private function loadInfo()
	{
		if (!empty($this->id))
			return;
			
		// Load project information
		$dbc = new dbConnection();
		$attr = array();
		$attr['name'] = $this->name;
		$dbq = new dbQuery("406610094", "developer.projects");
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);

		if (!empty($project))
		{
			$this->id = $project['id'];
			$this->name = $project['name'];
		}
	}
}
//#section_end#
?>