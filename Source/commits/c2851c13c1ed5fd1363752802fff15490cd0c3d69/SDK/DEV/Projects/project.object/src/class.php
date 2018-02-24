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
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Profile", "team");
importer::import("API", "Security", "account");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Projects", "projectStatus");

use \SYS\Comm\db\dbConnection;
use \API\Developer\resources\paths;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \API\Security\account;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \DEV\Version\vcs;
use \DEV\Projects\projectStatus;

/**
 * Developer Project
 * 
 * Manages developer projects, creating, updating and getting the proper information.
 * 
 * @version	2.0-1
 * @created	February 18, 2014, 12:04 (EET)
 * @revised	August 14, 2014, 15:57 (EEST)
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateStatus()
	{
		projectStatus::update($this->id);
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
	 * Publishes the resources of the current project to the given folder.
	 * You can provide a specific inner folder for partial coverage.
	 * It cleans the publish folder before copying.
	 * 
	 * @param	string	$publishFolder
	 * 		The published resource folder.
	 * 
	 * @param	string	$innerFolder
	 * 		The inner folder of the resources to be published.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publishResources($publishFolder, $innerFolder = "")
	{
		// Create or clean the publish folder
		folderManager::create(systemRoot.$publishFolder);
		folderManager::clean(systemRoot.$publishFolder);
		
		// Copy resources to publish folder
		$resourceFolder = $this->getResourcesFolder();
		return folderManager::copy(systemRoot.$resourceFolder."/".$innerFolder, systemRoot.$publishFolder, TRUE);
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
	 * Get all project releases.
	 * 
	 * @return	array
	 * 		All project releases.
	 */
	public function getReleases()
	{
		// Create a db release entry
		$dbc = new dbConnection();
		$dbq = new dbQuery("33130745222361", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $this->id;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Add an account to the project.
	 * 
	 * @param	string	$version
	 * 		The project release version.
	 * 
	 * @param	string	$changelog
	 * 		The project release changelog.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function release($version = "0.1", $changelog = "")
	{
		// Set time and token for release
		$time = time();
		$token = md5($this->id."_".$version."_".$time);
		
		// Create a db release entry
		$dbc = new dbConnection();
		$dbq = new dbQuery("2147269649798", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $this->id;
		$attr['version'] = $version;
		$attr['token'] = $token;
		$attr['time'] = $time;
		$attr['changelog'] = $changelog;
		$result = $dbc->execute($dbq, $attr);
		
		// Update release log
		$info = $this->info();
		$redProject = ($info['projectType'] == 1 || $info['projectType'] == 2 || $info['projectType'] == 3);
		if ($redProject)
			self::updateReleaseLog($version, $changelog);
		
		// If success, return token
		if ($result)
			return $token;
		
		return $result;
	}
	
	/**
	 * Gets all team's projects.
	 * 
	 * @return	array
	 * 		An array of all projects.
	 */
	public static function getTeamProjects()
	{
		$teamID = team::getTeamID();
		if (empty($teamID))
			return array();
		
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Get query
		$dbq = new dbQuery("29578611645313", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
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
	
	/**
	 * Get the release log from the project log.
	 * 
	 * @return	array
	 * 		An array of the release log.
	 * 		
	 * 		Schema:
	 * 		['version']['timestamp']
	 * 		['version']['changelog']
	 */
	public function getReleaseLog()
	{
		// Init project
		$project = new project($projectID);
		$rootFolder = $project->getRootFolder();
		$releaseLogFilePath = $rootFolder."/Log/release.xml";
		
		// Init parser
		$parser = new DOMParser();
		try
		{
			$parser->load($releaseLogFilePath);
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		// Init log
		$log = array();
		
		// Get release log entries
		$log = array();
		$release_entries = $parser->evaluate("//release");
		foreach ($release_entries as $entry)
		{
			$logEntry = array();
			$version = $parser->attr($entry, "version");
			$logEntry['timestamp'] = $parser->attr($entry, "timestamp");
			$logEntry['changelog'] = $parser->innerHTML($entry);
			
			$log[$version][] = $logEntry;
		}
		
		return $log;
	}
	
	/**
	 * Update the project status log with a new entry.
	 * 
	 * @param	string	$version
	 * 		The version of the release.
	 * 
	 * @param	string	$changelog
	 * 		The release changelog.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function updateReleaseLog($version, $changelog)
	{
		// Init project
		$project = new project($projectID);
		$rootFolder = $project->getRootFolder();
		$releaseLogFilePath = $rootFolder."/Log/release.xml";
		
		// Init parser
		$parser = new DOMParser();
		try
		{
			$parser->load($releaseLogFilePath);
			$root = $parser->evaluate("//log")->item(0);
		}
		catch (Exception $ex)
		{
			// Create file
			fileManager::create(systemRoot.$releaseLogFilePath, "", TRUE);
			
			// Create root
			$root = $parser->create("log");
			$parser->append($root);
			$parser->save(systemRoot.$releaseLogFilePath);
		}
		
		// Get timestamp
		$timestamp = time();
		
		// Add release to log
		$entry = $parser->create("release");
		$parser->innerHTML($entry, $changelog);
		$parser->prepend($root, $entry);
		$parser->attr($entry, "version", $version);
		$parser->attr($entry, "timestamp", $timestamp);
		
		// Update file
		return $parser->update();
	}
}
//#section_end#
?>