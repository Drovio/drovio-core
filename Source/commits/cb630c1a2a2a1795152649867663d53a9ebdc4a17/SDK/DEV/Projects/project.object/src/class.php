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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");
importer::import("API", "Profile", "account");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Projects", "projectReadme");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \API\Profile\account;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \DEV\Version\vcs;
use \DEV\Projects\projectLibrary;
use \DEV\Projects\projectReadme;
use \DEV\Profiler\logger;
use \DEV\Resources\paths;

/**
 * Developer Project
 * 
 * Manages developer projects, creating, updating and getting the proper information.
 * 
 * @version	12.0-1
 * @created	February 18, 2014, 12:04 (EET)
 * @updated	July 2, 2015, 18:38 (EEST)
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
	 * All the projects that the given account is member of.
	 * 
	 * @type	array
	 */
	private static $projects;
	
	/**
	 * Initializes the project by id or by name.
	 * 
	 * @param	integer	$id
	 * 		The project id.
	 * 		Leave empty for new project or name initialization.
	 * 		It is empty by default.
	 * 
	 * @param	string	$name
	 * 		The project name.
	 * 		Leave empty for new project or id initialization.
	 * 		It is empty by default.
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
		$attr['tid'] = team::getTeamID();
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		if ($project)
		{
			// Get id
			$this->id = $project['id'];
			
			// Return project id
			return $this->id;
		}
		
		return FALSE;
	}
	
	/**
	 * Get all accounts connected to a project.
	 * 
	 * You must be a member of the project.
	 * 
	 * @return	array
	 * 		An array of account id, title and person's first and last name.
	 */
	public function getProjectAccounts()
	{
		// Get project info
		$projectInfo = $this->info();
		
		// Validate account to project or project must be public
		if (!$this->validate() && !$projectInfo['open'])
		{
			// Log activity
			$accountID = account::getAccountID();
			$projectID = $projectInfo['id'];
			logger::log("Account [$accountID] doesn't have access to project [$projectID] to get project accounts.", logger::WARNING);
			
			// Return NULL
			return NULL;
		}
			
		$dbc = new dbConnection();
		$dbq = new dbQuery("32597504132115", "developer.projects");
		
		// If info not set, return NULL
		$this->loadInfo();
		if (!isset($this->id))
			return NULL;
		
		$attr = array();
		$attr['pid'] = $this->id;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all projects that the current account is member of.
	 * 
	 * @return	array
	 * 		An array of all project information.
	 */
	public static function getAccountProjects()
	{
		// Check account id
		$accountID = account::getAccountID();
		if (empty($accountID))
			return array();
		
		// Fetch projects from db
		$dbc = new dbConnection();
		$dbq = new dbQuery("17901555462787", "developer.projects");
		
		$attr = array();
		$attr['aid'] = $accountID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Gets the project information with the given id or name.
	 * 
	 * You must be a member of the project.
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
		
		// Get project public information
		$attr = array();
		$attr['id'] = $this->id;
		$result = $dbc->execute($dbq, $attr);
		$projectInfo = $dbc->fetch($result);
		if (empty($projectInfo))
			return NULL;
		
		// Get project icon url
		$iconUrl = $this->getIconUrl();
		if (!empty($iconUrl))
			$projectInfo['icon_url'] = $iconUrl;
		
		// Return project info
		return $projectInfo;
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
	 * You must be a member of the project.
	 * 
	 * @param	string	$title
	 * 		The project's new title.
	 * 
	 * @param	string	$description
	 * 		The project's new description.
	 * 
	 * @param	boolean	$open
	 * 		Indicates a Redback open project.
	 * 		It is FALSE by default.
	 * 
	 * @param	boolean	$public
	 * 		Indicates a Redback public project.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($title, $description = "", $open = FALSE, $public = FALSE)
	{
		// Load project info
		$this->loadInfo();
		
		// Validate account to project
		if (!$this->validate())
		{
			// Log activity
			$accountID = account::getAccountID();
			logger::log("Account [$accountID] doesn't have access to project [$this->id] to update info.", logger::WARNING);
			
			// Return FALSE
			return FALSE;
		}
			
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("994839582", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['open'] = ($open ? 1 : 0);
		$attr['public'] = ($public ? 1 : 0);
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Updates project's icon image.
	 * 
	 * You must be a member of the project.
	 * 
	 * @param	data	$icon
	 * 		The image data.
	 * 		The image should be in png format.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateProjectIcon($icon)
	{
		// Validate account to project
		if (!$this->validate())
		{
			// Log activity
			$accountID = account::getAccountID();
			logger::log("Account [$accountID] doesn't have access to project [$this->id] to update info.", logger::WARNING);
			
			// Return FALSE
			return FALSE;
		}
		
		// Get project's icon path
		$projectIconPath = $this->getResourcesFolder()."/.assets/icon.png";
		
		// Remove icon if empty
		if (is_null($icon))
			fileManager::remove(systemRoot.$projectIconPath);
		
		// If icon is empty other than null, return false
		if (empty($icon))
			return FALSE;
		
		// Update icon
		return fileManager::create(systemRoot.$projectIconPath, $icon);
	}
	
	/**
	 * Update the project's team owner
	 * 
	 * You must be a member of the project.
	 * 
	 * @param	integer	$teamID
	 * 		The new team owner id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateTeam($teamID)
	{
		// Validate account to project
		if (!$this->validate())
		{
			// Log activity
			$accountID = account::getAccountID();
			logger::log("Account [$accountID] doesn't have access to project [$this->id] to change team.", logger::WARNING);
			
			// Return FALSE
			return FALSE;
		}
		
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("16190686569661", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		$attr['tid'] = $teamID;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Sets the unique project name.
	 * 
	 * You must be a member of the project.
	 * 
	 * @param	string	$name
	 * 		The project's unique name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setName($name)
	{
		// Load project info
		$this->loadInfo();
		
		// Validate account to project
		if (!$this->validate())
		{
			// Log activity
			$accountID = account::getAccountID();
			$projectID = $projectInfo['id'];
			logger::log("Account [$accountID] doesn't have access to project [$this->id] to update info.", logger::WARNING);
			
			// Return FALSE
			return FALSE;
		}
			
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("2100666044", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $this->id;
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Gets the project's roof repository folder
	 * 
	 * You must be a member of the project.
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
	 * You must be a member of the project.
	 * 
	 * @return	string
	 * 		The project's repository folder.
	 */
	public function getRepository()
	{
		// Get Source Repository folder
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
	 * You must be a member of the project.
	 * 
	 * @return	string
	 * 		The project's resources folder.
	 */
	public function getResourcesFolder()
	{
		$resourcesFolder = $this->getRootFolder()."/Resources";
		if (!file_exists(systemRoot.$resourcesFolder))
			folderManager::create(systemRoot.$resourcesFolder);
		
		return $resourcesFolder;
	}
	
	/**
	 * Get the project's icon url from the repository.
	 * 
	 * @return	string
	 * 		The project's icon url.
	 */
	public function getProjectIconUrl()
	{
		// Get project icon url
		$projectIconPath = $this->getResourcesFolder()."/.assets/icon.png";
		if (file_exists(systemRoot.$projectIconPath))
		{
			// Resolve path
			$projectIconUrl = str_replace(paths::getRepositoryPath(), "", $projectIconPath);
			return url::resolve("repo", $projectIconUrl);
		}
		
		return NULL;
	}
	
	/**
	 * Get the project's icon url from the repository.
	 * 
	 * @return	string
	 * 		The project's icon url.
	 * 
	 * @deprecated	Use getProjectIconUrl() instead.
	 */
	public function getIconUrl()
	{
		return $this->getProjectIconUrl();
	}
	
	/**
	 * Publish common project assets.
	 * 
	 * @param	string	$version
	 * 		The version of the project to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($version)
	{
		// Validate account to project
		if (!$this->validate())
			return FALSE;
			
		// Get publish folder
		$publishFolder = projectLibrary::getPublishedPath($this->id, $version);
		
		// Publish readme document
		$readmeDocument = new projectReadme($this->getRootFolder(), TRUE);
		$innerFolder = $readmeDocument->getDocumentFolderName();
		if (file_exists(systemRoot.$this->getRootFolder()."/".$innerFolder))
		{
			folderManager::create(systemRoot.$publishFolder."/".$innerFolder);
			folderManager::copy(systemRoot.$this->getRootFolder()."/".$innerFolder, systemRoot.$publishFolder."/".$innerFolder, TRUE);
		}
		
		return TRUE;
	}
	
	/**
	 * Publishes the resources of the current project to the given folder.
	 * You can provide a specific inner folder for partial coverage.
	 * 
	 * You must be a member of the project.
	 * 
	 * @param	string	$publishFolder
	 * 		The published resource folder.
	 * 
	 * @param	string	$innerFolder
	 * 		The inner folder of the resources to be published.
	 * 
	 * @param	boolean	$cleanFolder
	 * 		If set to TRUE, it cleans the publish folder before copying.
	 * 		It is TRUE by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publishResources($publishFolder, $innerFolder = "", $cleanFolder = TRUE)
	{
		// Validate account to project
		if (!$this->validate())
			return FALSE;
			
		// Create the publish folder
		folderManager::create(systemRoot.$publishFolder);
		if ($cleanFolder)
			folderManager::clean(systemRoot.$publishFolder);
		
		// Copy resources to publish folder
		$resourceFolder = $this->getResourcesFolder();
		return folderManager::copy(systemRoot.$resourceFolder."/".$innerFolder, systemRoot.$publishFolder, TRUE);
	}
	
	/**
	 * Get the project's publish folder.
	 * 
	 * You must be a member of the project.
	 * 
	 * @param	string	$version
	 * 		The project version.
	 * 
	 * @return	string
	 * 		The project's publish folder.
	 * 
	 * @deprecated	Use projectLibrary::getPublishedPath() instead.
	 */
	public function getPublishedPath($version)
	{
		// Validate account to project
		if (!$this->validate())
			return NULL;
		
		// Load info and get published path from id and version
		$this->loadInfo();
		return projectLibrary::getPublishedPath($this->id, $version);
	}
	
	/**
	 * Get all project releases.
	 * 
	 * You must be a member of the project.
	 * 
	 * @return	array
	 * 		An array of all releases in descending order according to the release creation date. Each array object contains an array with the available information of every release.
	 */
	public function getReleases()
	{
		// Get project info
		$projectInfo = $this->info();
		
		// Validate account to project
		if (!$this->validate() && !$projectInfo['open'])
			return NULL;
			
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
	 * Create a project release.
	 * 
	 * You must be a member of the project or the project must be public.
	 * 
	 * @param	string	$version
	 * 		The project release version.
	 * 
	 * @param	string	$title
	 * 		The project release title.
	 * 		The project title is usually used but changes can be made.
	 * 
	 * @param	string	$changelog
	 * 		The project release changelog.
	 * 
	 * @return	mixed
	 * 		The release token on success, false on failure.
	 */
	public function release($version = "0.1", $title = "", $changelog = "")
	{
		// Validate account to project
		if (!$this->validate())
			return FALSE;
			
		// Set time, token and version for release
		$time = time();
		$version = (empty($version) ? "0.1" : $version);
		$token = md5($this->getID()."_".$version."_".$time);
		
		// Set project title (if empty)
		if (empty($title))
		{
			$projectInfo = $this->info();
			$title = $projectInfo['title'];
		}
		
		// Create a db release entry
		$dbc = new dbConnection();
		$dbq = new dbQuery("2147269649798", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $this->getID();
		$attr['version'] = $version;
		$attr['title'] = $title;
		$attr['token'] = $token;
		$attr['time'] = $time;
		$attr['changelog'] = $changelog;
		$result = $dbc->execute($dbq, $attr);
		
		// If success, return token
		if ($result)
			return $token;
		
		return $result;
	}
	
	/**
	 * Remove a project release from the database.
	 * The project release must not be the last release and approved.
	 * If the release goes to published or rejected status, it cannot be removed.
	 * 
	 * @param	string	$version
	 * 		The project release version to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function unrelease($version)
	{
		// Validate account to project
		if (!$this->validate())
			return FALSE;
		
		// Check if version is empty
		if (empty($version))
			return FALSE;
		
		// Check for sure that it's not the last release, and marked as approved
		$lastVersion = projectLibrary::getLastProjectVersion($this->getID());
		if ($version == $lastVersion)
		{
			// Get release status id
			$releaseInfo = projectLibrary::getProjectReleaseInfo($this->getID(), $version);
			if ($releaseInfo['status_id'] == 2)
				return FALSE;
		}
		
		// Remove release entry
		$dbc = new dbConnection();
		$dbq = new dbQuery("20440751952483", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $this->getID();
		$attr['version'] = $version;
		$result = $dbc->execute($dbq, $attr);
		
		// Remove release folder
		if ($result)
		{
			$releaseFolder = projectLibrary::getPublishedPath($this->getID(), $version);
			folderManager::remove(systemRoot."/".$releaseFolder, "", TRUE);
		}
		
		return $result;
	}
	
	/**
	 * Validates whether the logged in account has access to this project.
	 * 
	 * @return	boolean
	 * 		True is account has access, false otherwise.
	 */
	public function validate()
	{
		// Get project id
		$projectID = $this->getID();
		
		// Check cache
		if (account::validate() && isset(self::$projects[$projectID]))
			return self::$projects[$projectID];
		
		// Validate account
		if (!account::validate())
			return FALSE;
			
		// Get account projects
		$dbc = new dbConnection();
		$dbq = new dbQuery("32597504132115", "developer.projects", TRUE);
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$result = $dbc->execute($dbq, $attr);
		
		// Check if project id is in projects
		$accountID = account::getAccountID();
		while ($account = $dbc->fetch($result))
			if ($account['accountID'] == $accountID)
			{
				self::$projects[$projectID] = TRUE;
				return TRUE;
			}
		
		self::$projects[$projectID] = FALSE;
		return FALSE;
	}
	
	/**
	 * Remove a project from the repository.
	 * The project must have been already deleted from the database.
	 * 
	 * You must be a member of the project.
	 * 
	 * @return	array
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Check if project's info is empty (project not in db)
		$info = $this->info();
		if (!empty($info))
			return FALSE;
		
		// Validate account to project
		if (!$this->validate())
			return FALSE;
		
		// Remove project folder
		$projectFolder = $this->getRootFolder();
		return folderManager::remove(systemRoot."/".$projectFolder, "", TRUE);
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