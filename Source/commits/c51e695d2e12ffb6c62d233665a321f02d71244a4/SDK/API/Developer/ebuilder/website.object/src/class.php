<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Developer", "ebuilder::wsManager");
importer::import("API", "Developer", "ebuilder::templateManager");
importer::import("API", "Developer", "ebuilder::wsComponents::wsPage");
importer::import("API", "Developer", "ebuilder::wsComponents::wsScript");
importer::import("API", "Developer", "ebuilder::wsComponents::wsStyle");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\versionControl\vcsManager;
use \API\Developer\ebuilder\wsManager;
use \API\Developer\ebuilder\templateManager;
use \API\Developer\ebuilder\wsComponents\wsPage;
use \API\Developer\ebuilder\wsComponents\wsScript;
use \API\Developer\ebuilder\wsComponents\wsStyle;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;

/**
 * Ebuilder Website
 * 
 * This class is responsible for the development of an eBuilder website.
 * 
 * @version	{empty}
 * @created	June 5, 2013, 17:11 (EEST)
 * @revised	July 31, 2013, 14:30 (EEST)
 */
class website extends vcsManager
{
	/**
	 * Index file name
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "/index.xml";
	/**
	 * Configuration file name
	 * 
	 * @type	string
	 */
	const CONFIGURATION_FILE = "/settings.xml";
	
	/**
	 * Website's reporitory folder
	 * 
	 * @type	string
	 */
	const REPOSITORY_FOLDER = "/Repository";
	/**
	 * Website's release folder
	 * 
	 * @type	string
	 */
	const RELEASE_FOLDER = "/Release";
	/**
	 * Website's content folder
	 * 
	 * @type	string
	 */
	const CONTENT_FOLDER = "/Content";
	
	/**
	 * Website's mapping folder
	 * 
	 * @type	string
	 */
	const MAPPING_FOLDER = "/.website";
	/**
	 * Website's template folder
	 * 
	 * @type	string
	 */
	const TEMPLATE_FOLDER = "/template";
	/**
	 * Website's pages folder
	 * 
	 * @type	string
	 */
	const PAGES_FOLDER = "/pages";
	/**
	 * Website's styles folder
	 * 
	 * @type	string
	 */
	const STYLES_FOLDER = "/styles";
	
	/**
	 * Website's scripts folder
	 * 
	 * @type	string
	 */
	const SCRIPTS_FOLDER = "/scripts";
	
	/**
	 * Website's media folder
	 * 
	 * @type	string
	 */
	const MEDIA_FOLDER = "/media";
	
	/**
	 * Website's extensions folder
	 * 
	 * @type	string
	 */
	const EXTENSIONS_FOLDER = "/extensions";
	
	/**
	 * Website's configuration folder
	 * 
	 * @type	string
	 */
	const CONFIGURATION_FOLDER = "/config";
	
	/**
	 * Current released website's folder
	 * 
	 * @type	string
	 */
	const CURRENT_RELEASE_FOLDER = "/current";
	/**
	 * Website's history folder
	 * 
	 * @type	string
	 */
	const RELEASE_HISTORY_FOLDER = "/history";
	
	/**
	 * Website's id
	 * 
	 * @type	integer
	 */
	private $websiteId;
	
	/**
	 * Website's development root path
	 * 
	 * @type	string
	 */
	private $devPath;
	
	/**
	 * Names of sitemaps reserved for eBuilder use
	 * 
	 * @type	array
	 */
	private $reservedSitemaps = array(".eBuilder", ".website", "Resources", "Extensions");
	/**
	 * Names of pages in root sitemap reserved for eBuilder use
	 * 
	 * @type	array
	 */
	private $reservedPages = array("config", "init");
	
	/**
	 * Constructor Method. Initializes the website
	 * 
	 * @param	integer	$websiteId
	 * 		Website's id. If supplied, it is used for initialization purposes.
	 * 
	 * @return	void
	 */
	public function __construct($websiteId = "")
	{
		if (!empty($websiteId))
			$this->init($websiteId);
	}
	
	/**
	 * Initializes the website with all the names and paths.
	 * 
	 * @param	integer	$websiteId
	 * 		The website's id.
	 * 
	 * @return	void
	 */
	private function init($websiteId)
	{
		$this->websiteId = $websiteId;
		
		$websiteMan = new wsManager();
		$this->devPath = $websiteMan->getDevFolder($websiteId);
	}
	
	/**
	 * Creates a new website in the user's folder.
	 * 
	 * @param	string	$wsName
	 * 		Website name
	 * 
	 * @param	string	$wsDescription
	 * 		Website description
	 * 
	 * @param	string	$wsProjectId
	 * 		Website id
	 * 
	 * @param	string	$wsTemplateId
	 * 		Website's template id
	 * 
	 * @param	{type}	$defaultLanguageId
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function create($wsName, $wsDescription, $wsProjectId, $wsTemplateId, $defaultLanguageId)
	{
		// Check privileges??? here, not in wsManager???
		// ...
		/*
		// Get website type from template. (...)
		$tInfo = template::getTemplateInfo($wsTemplateId);
		$wsTypeId = $tInfo['templateType'];
		// Temp project id (...). Should be given from the user / bmapp.
		$wsProjectId = 4;
		
		//$websiteId = $this->register($wsName, $wsDescription, $wsTypeId, $wsProjectId);
		if (empty($websiteId))
			return FALSE;
		*/
		$websiteId = $wsName;
		
		// Initialize website
		$this->init($websiteId);
		
		// Initialize Website Structure Files
		return $this->createWebsiteStructure($wsTemplateId, $defaultLanguageId);
	}
	
	/**
	 * Adds a new entry for the new website in the database.
	 * 
	 * @param	string	$wsName
	 * 		Website's name
	 * 
	 * @param	string	$wsDescription
	 * 		Website's description
	 * 
	 * @param	string	$wsTypeId
	 * 		Website's type (from template)
	 * 
	 * @param	string	$wsProjectId
	 * 		Website's project id
	 * 
	 * @return	string
	 * 		The website's id.
	 */
	private function register($wsName, $wsDescription, $wsTypeId, $wsProjectId)
	{
		// Register site on database
		// Initialize query [Add Website] and connection
		$dbc = new interDbConnection();
		$dbq = new dbQuery(1807515685, "ebuilder.website");
				
		// Initialize status as "Under Construction"
		$wsUnderConstruction = 3;
		
		// Set Query Attributes
		$attr = array();
		$attr['title'] = $wsName;
		$attr['description'] = $wsDescription;
		$attr['siteType_id'] = $wsTypeId;
		$attr['status_id'] = $wsUnderConstruction;
		$attr['project_id'] = $wsProjectId;
		
		//$result = $dbc->execute_query($dbq, $attr);
		// Check if query was successful
		if (empty($result))
			return FALSE;	
		
		// Get id
		$row = $dbc->fetch($result);
		// Return website id.
		return $row['last_id'];
	}
	
	/**
	 * Creates the website's structure.
	 * 
	 * @param	string	$wsTemplateId
	 * 		Website's template id
	 * 
	 * @param	{type}	$defaultLanguageId
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	private function createWebsiteStructure($wsTemplateId, $defaultLanguageId)
	{
		// Create Website Folders
		$this->createWebsiteFolders();
		
		// Create Website Mapping
		$this->createWebsiteMapping();
		
		// Create Website Configuration
		$this->createWebsiteConfiguration($wsTemplateId);
		
		// Import Template in website
		// ... Update with templateManager when ready
		/*$templateMan = new templateManager();
		$templateMan->insertToWebsite($wsTemplateId, systemRoot.$this->devPath.self::REPOSITORY_FOLDER.self::TEMPLATE_FOLDER);*/
		// What happens with the file GlobalSectionID.php ?
		// ...
		
		// Nothing to do with Extensions during creation
		
		// Create Website Release History index
		$this->createWebsiteHistoryIndexing();
		
		// Release initial state of the website
		$this->release();
		return TRUE;
	}
	
	/**
	 * Creates all the website structure's necessary folders.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	private function createWebsiteFolders()
	{
		// Create Repository folders structure
		folderManager::create(systemRoot.$this->devPath.self::REPOSITORY_FOLDER);
		$repositoryPath = $this->devPath.self::REPOSITORY_FOLDER;
		
		folderManager::create(systemRoot.$this->getMappingPath());		
		folderManager::create(systemRoot.$repositoryPath.self::TEMPLATE_FOLDER);
		folderManager::create(systemRoot.$repositoryPath.self::PAGES_FOLDER);
		// ___ Create Pages VCS structure
		$this->VCS_initialize($repositoryPath.self::PAGES_FOLDER, "", "pagesRoot", "pages");
		$this->VCS_createStructure();
		
		folderManager::create(systemRoot.$repositoryPath.self::STYLES_FOLDER);
		// ___ Create Styles VCS structure
		$this->VCS_initialize($repositoryPath.self::STYLES_FOLDER, "", "stylesRoot", "styles");
		$this->VCS_createStructure();
		
		folderManager::create(systemRoot.$repositoryPath.self::SCRIPTS_FOLDER);
		// ___ Create Scripts VCS structure
		$this->VCS_initialize($repositoryPath.self::SCRIPTS_FOLDER, "", "scriptsRoot", "scripts");
		$this->VCS_createStructure();
		
		folderManager::create(systemRoot.$repositoryPath.self::MEDIA_FOLDER);		
		folderManager::create(systemRoot.$repositoryPath.self::EXTENSIONS_FOLDER);
		folderManager::create(systemRoot.$this->getConfigurationPath());		
		
		// Create Release folders structure
		folderManager::create(systemRoot.$this->devPath.self::RELEASE_FOLDER);
		folderManager::create(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::CURRENT_RELEASE_FOLDER);
		folderManager::create(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::RELEASE_HISTORY_FOLDER);
		
		// Create Content folder
		folderManager::create(systemRoot.$this->devPath.self::CONTENT_FOLDER);
		// Anything else with content. Only the root folder exists atm. 
		// Need to create / copy locale structure for default language? For other languages?
		// ...
		
		return TRUE;
	}
	
	/**
	 * Creates the website's mapping file.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	private function createWebsiteMapping()
	{
		// Create Website index root
		$parser = new DOMParser();
		$root = $parser->create("website");
		$parser->append($root);
		
		// Populate index
		$pageElement = $parser->create("pages");
		$parser->append($root, $pageElement);
		
		$stylesElement = $parser->create("styles");
		$parser->append($root, $stylesElement);
		
		$scriptsElement = $parser->create("scripts");
		$parser->append($root, $scriptsElement);
		
		$mediaElement = $parser->create("media");
		$parser->append($root, $mediaElement);
		
		return $parser->save(systemRoot.$this->getMappingPath(), self::INDEX_FILE, $format = TRUE);
	}
	
	/**
	 * Creates the website's configuration file.
	 * 
	 * @param	string	$templateId
	 * 		Website's template id
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	private function createWebsiteConfiguration($templateId)
	{
		// Create Website configuration root
		$parser = new DOMParser();
		$root = $parser->create("configuration");
		$parser->append($root);
		
		// Populate configuration file
		// ...
		// Link website with template
		$tmpl = DOM::create("template", "", $templateId);
		$parser->append($root, $tmpl);
		
		return $parser->save(systemRoot.$this->getConfigurationPath(), self::CONFIGURATION_FILE, $format = TRUE);
	}
	
	/**
	 * Creates the website's history indexing file.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	private function createWebsiteHistoryIndexing()
	{
		// Create Website history index root
		$parser = new DOMParser();
		$root = $parser->create("history");
		//$parser->attr($root, "website", $this->websiteId);
		$parser->append($root);
		
		return $parser->save(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::RELEASE_HISTORY_FOLDER, self::INDEX_FILE, $format = TRUE);
	}
	
	/**
	 * Updates the website's history indexing file with a new entry.
	 * 
	 * @param	string	$description
	 * 		Description of the release.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	private function updateWebsiteHistoryIndexing($description)
	{
		// Load website history index
		$parser = new DOMParser();
		$parser->load($this->devPath.self::RELEASE_FOLDER.self::RELEASE_HISTORY_FOLDER.self::INDEX_FILE);
		
		$historyRoot = $parser->evaluate("//history")->item(0);

		$timestamp = time();
		// Populate index
		// ... needs final xml model
		$entry = $parser->create("entry", "", $this->websiteId."_".$timestamp);
		//$parser->attr($entry, "version", $wsVersion);
		$parser->attr($entry, "timestamp", $timestamp);
		$parser->append($historyRoot, $entry);
		$desc = $parser->create("releaseDescription", $description);
		$parser->append($entry, $desc);
		
		return $parser->save(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::RELEASE_HISTORY_FOLDER, self::INDEX_FILE, $format = TRUE);
	}
	
	/**
	 * Adds a new page (php) to the website.
	 * 
	 * @param	string	$pageName
	 * 		Name of the new page.
	 * 
	 * @param	string	$sitemapPath
	 * 		The sitemap path in which the page will be placed
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function addPage($pageName, $sitemapPath = "")
	{
		// Load website index
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		
		// Normalize sitemap path
		$smPath = str_replace(":", "/", $sitemapPath);
		$smPath = directory::normalize("/".$smPath);
		$smPath = trim($smPath, "/");
		
		// Check if page is reserved for eBuilder
		if (isReservedPage($pageName, $sitemapPath))
			return FALSE;
		
		if (!empty($smPath))
			$smPath = "/".$smPath;
		
		$sitemapPathRoot = $parser->evaluate("//website/pages".$smPath);
		// Sitemap path doesn't exist. Aborting...
		if ($sitemapPathRoot->length == 0)
			return FALSE;
			
		$sitemapPathRoot = $sitemapPathRoot->item(0);
		
		// Check if page already exists
		$page = $parser->evaluate("page[@name='$pageName']", $sitemapPathRoot);
		if (!is_null($page))
			return TRUE;
		
		// Create page index entry
		$pgElement = $parser->create("page");
		$parser->attr($pgElement, "name", $pageName);
		$parser->append($sitemapPathRoot, $pgElement);
		$parser->save(systemRoot.$this->getMappingPath(), self::INDEX_FILE, $format = TRUE);
		
		// Create page
		$wsPage = new wsPage($this->devPath.self::REPOSITORY_FOLDER.self::PAGES_FOLDER.$smPath);
		return $wsPage->create($pageName);
	}
	
	/**
	 * Creates a new sitemap path
	 * 
	 * @param	string	$smPath
	 * 		Parent sitemap path
	 * 
	 * @return	boolean
	 * 		Status of the process. If parent sitemap path doesn't exist, then the sitemap path will not be created
	 */
	public function addSitemapPath($smPath)
	{
		// Load website index
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		
		// Normalize sitemap path
		$smPath = str_replace(":", "/", $sitemapPath);
		$smPath = directory::normalize("/".$smPath);
		$smPath = trim($smPath, "/");
		
		// Root sitemap path exists
		if (empty($smPath))
			return TRUE;
		
		// Check if sitemap path is reserved for eBuilder
		if (isReservedSitemap($smPath))
			return FALSE;
		
		$smPath = "/".$smPath;
		$smpRoot = $parser->evaluate("//website/pages".$smPath);
		// Requested sitemap path exists
		if ($smpRoot->length != 0)
			return TRUE;
	
		$smpParent = $parser->evaluate("//website/pages".dirname($smPath));
		// Parent sitemap path doesn't exist. Aborting...
		if ($smpParent->length == 0)
			return FALSE;
			
		$smpParent = $smpParent->item(0);
		$smpName = basename($smPath);
		
		// Create sitemap path index entry
		$smpElem = $parser->create("sitemappath");
		$parser->attr($smpElem, "name", $smpName);
		$parser->append($smpParent, $smpElem);
		$parser->save(systemRoot.$this->getMappingPath(), self::INDEX_FILE, $format = TRUE);
			
		// ___ Create sitemap path VCS structure
		$this->VCS_initialize($this->devPath.self::REPOSITORY_FOLDER.self::PAGES_FOLDER.$smPath, "", "sitemapPathRoot", "sitemapPath");
		$this->VCS_createStructure();
		
		return TRUE;
	}
	
	/**
	 * Adds a new script (js) to the website.
	 * 
	 * @param	string	$scriptName
	 * 		Name of the new script.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function addScript($scriptName)
	{
		// Load website index
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		
		$scriptsRoot = $parser->evaluate("//website/scripts")->item(0);
		
		// Check if script already exists
		$script = $parser->find("scr_".$scriptName, $scriptsRoot);
		if (!is_null($script))
			return TRUE;
		
		// Create script index entry
		$scriptElement = $parser->create("script", "", "scr_".$scriptName);
		$parser->attr($scriptElement, "name", $scriptName);
		$parser->append($scriptRoot, $scriptElement);
		$parser->save(systemRoot.$this->getMappingPath(), self::INDEX_FILE, $format = TRUE);
		
		// Create script
		$wsScript = new wsScript($this->devPath.self::REPOSITORY_FOLDER.self::SCRIPTS_FOLDER);
		return $wsScript->create($scriptName);
	}
	
	/**
	 * Adds a new style (css) to the website.
	 * 
	 * @param	string	$styleName
	 * 		Name of the new style.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function addStyle($styleName)
	{
		// Load website index
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		
		$stylesRoot = $parser->evaluate("//website/styles")->item(0);
		
		// Check if style already exists
		$style = $parser->find("stl_".$styleName, $stylesRoot);
		if (!is_null($style))
			return TRUE;
		
		// Create style index entry
		$styleElement = $parser->create("style", "", "stl_".$styleName);
		$parser->attr($styleElement, "name", $styleName);
		$parser->append($stylesRoot, $styleElement);
		$parser->save(systemRoot.$this->getMappingPath(), self::INDEX_FILE, $format = TRUE);
		
		// Create style
		$wsStyle = new wsStyle($this->devPath.self::REPOSITORY_FOLDER.self::STYLES_FOLDER);
		return $wsStyle->create($styleName);
	}
	
	/**
	 * Adds new media to the website.
	 * 
	 * @param	string	$mediaName
	 * 		Name of the new media.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function addMedia($mediaName)
	{
		// To be implemented...
		// May need media type as argument.
		// May need a resourceManager object.
		return FALSE;
	}
	
	/**
	 * Acquires an existing page from the website.
	 * 
	 * @param	string	$pageName
	 * 		Name of the page in the website.
	 * 
	 * @param	string	$sitemapPath
	 * 		The sitemap path of the page
	 * 
	 * @return	mixed
	 * 		The returned page, or null if the page doesn't exist.
	 */
	public function getPage($pageName, $sitemapPath = "")
	{
		// Load website index
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		
		// Normalize sitemap path
		$smPath = str_replace(":", "/", $sitemapPath);
		$smPath = directory::normalize("/".$smPath);
		$smPath = trim($smPath, "/");
		if (!empty($smPath))
			$smPath = "/".$smPath;
		
		$sitemapPathRoot = $parser->evaluate("//website/pages".$smPath);
		// Sitemap path doesn't exist. So does the page.
		if ($sitemapPathRoot->length == 0)
			return NULL;
			
		$sitemapPathRoot = $sitemapPathRoot->item(0);
		
		// Check if page already exists
		$page = $parser->evaluate("page[@name='$pageName']", $sitemapPathRoot);
		if (is_null($page))
			return NULL;
		
		return new wsPage($this->devPath.self::REPOSITORY_FOLDER.self::PAGES_FOLDER.$smPath, $pageName);
	}
	
	/**
	 * Acquires an existing script from the website.
	 * 
	 * @param	string	$scriptName
	 * 		Name of the script in the website.
	 * 
	 * @return	mixed
	 * 		The returned script, or null if the script doesn't exist.
	 */
	public function getScript($scriptName)
	{
		if (!objectExists("scripts", $scriptName, "scr"))
			return NULL;
		
		return new wsScript($this->devPath.self::REPOSITORY_FOLDER.self::SCRIPTS_FOLDER, $scriptName);
	}
	
	/**
	 * Acquires an existing style from the website.
	 * 
	 * @param	string	$styleName
	 * 		Name of the style in the website.
	 * 
	 * @return	mixed
	 * 		The returned style, or null if the style doesn't exist.
	 */
	public function getStyle($styleName)
	{
		if (!objectExists("styles", $styleName, "stl"))
			return NULL;
		
		return new wsStyle($this->devPath.self::REPOSITORY_FOLDER.self::STYLES_FOLDER, $styleName);
	}
	
	/**
	 * Acquires existing media from the website.
	 * 
	 * @param	string	$mediaName
	 * 		Name of the media in the website.
	 * 
	 * @return	mixed
	 * 		The returned media, or null if the media don't exist.
	 */
	public function getMedia($mediaName)
	{
		// To be implemented...
		// May need media type as argument.
		// May need a resourceManager object.
		return NULL;
	}
		
	/**
	 * Acquires a list of the website's pages.
	 * 
	 * @param	string	$sitemapPath
	 * 		A sitemap path as a page source
	 * 
	 * @return	array
	 * 		An array holding the names of the pages.
	 */
	public function getPageList($sitemapPath = "")
	{
		// Normalize sitemap path
		$smPath = str_replace(":", "/", $sitemapPath);
		$smPath = directory::normalize("/".$smPath);
		$smPath = trim($smPath, "/");
		if (!empty($smPath))
			$smPath = "/".$smPath;
		
		return $this->getObjectList("page", "pages".$smPath);
	}
	
	/**
	 * Acquires a list of the website's scripts.
	 * 
	 * @return	array
	 * 		An array holding the names of the scripts.
	 */
	public function getScriptList()
	{
		return $this->getObjectList("script");
	}
	
	/**
	 * Acquires a list of the website's styles.
	 * 
	 * @return	array
	 * 		An array holding the names of the styles.
	 */
	public function getStyleList()
	{
		return $this->getObjectList("style");
	}
	
	/**
	 * Acquires a list of the website's media.
	 * 
	 * @return	array
	 * 		An array holding the names of the media.
	 */
	public function getMediaList()
	{
		// To be implemented...
		// May need media type as optional argument. [get all images, etc...]
		// May need a resourceManager object.
		// return $this->getObjectList("media", "media");
		return array();
	}
	
	/**
	 * Returns the path to the website mapping folder.
	 * 
	 * @return	string
	 * 		The returned path.
	 */
	private function getMappingPath()
	{
		return $this->devPath.self::REPOSITORY_FOLDER.self::MAPPING_FOLDER;
	}
	
	/**
	 * Returns the path to the website configuration folder.
	 * 
	 * @return	string
	 * 		The returned path.
	 */
	private function getConfigurationPath()
	{
		return $this->devPath.self::REPOSITORY_FOLDER.self::CONFIGURATION_FOLDER;
	}
	
	/**
	 * Checks if an object (page, script, style, media) exists in the website.
	 * 
	 * @param	string	$objectPool
	 * 		Objects pool name. ("scripts", "styles", "pages", "media")
	 * 
	 * @param	string	$objectName
	 * 		Name of the object to check.
	 * 
	 * @param	string	$objectCode
	 * 		Objects identification code ("scr", "stl", "pg", "media")
	 * 
	 * @return	boolean
	 * 		Returns TRUE if the object exists, or FALSE otherwise.
	 */
	private function objectExists($objectPool, $objectName, $objectCode)
	{
		// Check if object exists in the website
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		$objectsRoot = $parser->evaluate("//website/".$objectPool)->item(0);
		$object = $parser->find($objectCode."_".$objectName, $objectsRoot);
		if (is_null($object))
			return FALSE;
		return TRUE;
	}
	
	/**
	 * Returns a list of objects in the website.
	 * 
	 * @param	string	$objectType
	 * 		Type of the objects to acquire. ("script", "style", "page", "media")
	 * 
	 * @param	string	$objectPool
	 * 		Objects pool name. ("scripts", "styles", "pages", "media")
	 * 
	 * @return	array
	 * 		An array holding the objects' names.
	 */
	private function getObjectList($objectType, $objectPool = "")
	{
		// Load website index
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		
		if (empty($objectPool))
			$objectPool = $objectType."s";
		
		// Get the root
		$objectsRoot = $parser->evaluate("//website/".$objectPool);
		
		// Get all objects
		$objects = array();
		
		if ($objectsRoot->length == 0)
			return NULL;
		
		$elements = $parser->evaluate($objectType, $objectsRoot->item(0));
		foreach ($elements as $elem)
			$objects[] = $parser->attr($elem, "name");
		
		return $objects;
	}
	
	/**
	 * Maps a developing website to the released website.
	 * 
	 * @return	boolean
	 * 		Status of the process.
	 */
	public function release()
	{
		// Deploy current state in Release path's current site.
		// Need to add/remove/rearrange stuff here, according to the "Published Website Structure"
		// ...
		
		$status = $this->createReleaseStructure();
		
		if (!$status)
		{
			// ... Get first zip if it exists and extract it in the current release path
			return FALSE;
		}
		
		// Update history of the website
		$this->updateWebsiteHistoryIndexing("Initial Website State");
		
		// Create zip archive of current state in history
		$contents = directory::getContentList(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::CURRENT_RELEASE_FOLDER, TRUE);
		$destination = systemRoot.$this->devPath.self::RELEASE_FOLDER.self::RELEASE_HISTORY_FOLDER;
		$archiveName = "/version_description.zip"; // Needs proper name (...)
		zipManager::create($destination.$archiveName, $contents);
		
		return TRUE;
	}
	
	/**
	 * Creates release structure of the website
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	private function createReleaseStructure()
	{
		// Current Release Folder
		$currentReleaseFolder = $this->devPath.self::RELEASE_FOLDER.self::CURRENT_RELEASE_FOLDER;
		
		// Empty Current Release Folder
		folderManager::clean(systemRoot.$currentReleaseFolder);
		
		$this->releaseWebsite($currentReleaseFolder);
		
		$this->releaseResources($currentReleaseFolder);
		
		$this->releaseExtensions($currentReleaseFolder);
		
		// Create init / config files?
		// ...
		
		// Release sitemap
		$this->releaseSitemap();
		return TRUE;
	}
	
	/**
	 * Initializes website's release ".website" folder
	 * 
	 * @param	string	$currentReleaseFolder
	 * 		The release folder
	 * 
	 * @return	void
	 */
	private function releaseWebsite($currentReleaseFolder)
	{
		// Create website folder structure
		folderManager::create(systemRoot.$currentReleaseFolder."/.website");
		folderManager::create(systemRoot.$currentReleaseFolder."/.website/Template");
		// ... Do something with page structures. Let templateManager handle this?
		/*
		Template/
			{PageStructureName}.xml
			{PageStructureName}_{globalID}.php
		*/
		
		folderManager::create(systemRoot.$currentReleaseFolder."/.website/Config");
		// Copy config? Needs proper structure...
		fileManager::copy(systemRoot.$this->getConfigurationPath().self::CONFIGURATION_FILE, systemRoot.$currentReleaseFolder."/.website/Config".self::CONFIGURATION_FILE);		
		
		// Copy Content? Needs final structure...
		folderManager::copy(systemRoot.$this->devPath.self::CONTENT_FOLDER, systemRoot.$currentReleaseFolder."/.website");
		
		// Mapping pages me structures? hardcoded i se xml?
		// ...
	}
	
	/**
	 * Releases websites resources
	 * 
	 * @param	string	$currentReleaseFolder
	 * 		The release folder
	 * 
	 * @return	void
	 */
	private function releaseResources($currentReleaseFolder)
	{
		// Create Resources folder structure
		folderManager::create(systemRoot.$currentReleaseFolder."/Resources");
		folderManager::create(systemRoot.$currentReleaseFolder."/Resources/styles");
		// Merge and release styles? ...
		$styles = $this->getStyleList();
		$merged = "";
		foreach ($styles as $stlName)
		{
			$wsStyle = $this->getStyle($stlName);
			$merged .= $wsStyle->getSourceCode()."\n";
		}
		fileManager::create(systemRoot.$currentReleaseFolder."/Resources/styles/styles.css", $contents = $merged);
		
		folderManager::create(systemRoot.$currentReleaseFolder."/Resources/scripts");
		// Merge and release scripts? ...
		$scripts = $this->getScriptList();
		$merged = "";
		foreach ($scripts as $scrName)
		{
			$wsScript = $this->getScript($scrName);
			$merged .= $wsScript->getSourceCode()."\n";
		}
		fileManager::create(systemRoot.$currentReleaseFolder."/Resources/scripts/scripts.js", $contents = $merged);
		
		folderManager::create(systemRoot.$currentReleaseFolder."/Resources/media");
		// Release media? ...
		folderManager::create(systemRoot.$currentReleaseFolder."/Resources/extensions");
		// Get extensions resources from extManager...
	}
	
	/**
	 * Releases the extensions
	 * 
	 * @param	string	$currentReleaseFolder
	 * 		The release folder
	 * 
	 * @return	void
	 */
	private function releaseExtensions($currentReleaseFolder)
	{
		// Create Extensions folder structure
		folderManager::create(systemRoot.$currentReleaseFolder."/Extensions");
		// Get extension files from extManager...
	}

	/**
	 * Releases the website's sitemap
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	private function releaseSitemap()
	{
		// Load website index
		$parser = new DOMParser();
		$parser->load($this->getMappingPath().self::INDEX_FILE);
		
		// Get the root
		$pagesRoot = $parser->evaluate("//website/pages");
		
		return $this->_releaseSitemap($parser, $pagesRoot, "");
	}
	
	/**
	 * Helps in website's sitemap release
	 * 
	 * @param	DOMParser	$parser
	 * 		Sitemap's parser
	 * 
	 * @param	DOMElement	$rootElement
	 * 		Root element of each iteration
	 * 
	 * @param	string	$sitemapPath
	 * 		Parent sitemap path
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	private function _releaseSitemap($parser, $rootElement, $sitemapPath)
	{
		$pages = $this->getPageList($sitemapPath);
		foreach ($pages as $pageName)
		{
			$page = $this->getPage($pageName, $sitemapPath);
			$page->release(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::CURRENT_RELEASE_FOLDER.$sitemapPath);
		}
		
		$smPaths = $parser->evaluate("sitemappath", $rootElement);
		foreach ($smPaths as $smp)
		{
			$smpName = $parser->attr($smp, "name");
			folderManager::create(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::CURRENT_RELEASE_FOLDER.$sitemapPath, $smpName);
			$this->_releaseSitemap($parser, $smp, $sitemapPath."/".$smpName);
		}
		return TRUE;
	}
	
	/**
	 * Checks if a page name is reserved by eBuilder
	 * 
	 * @param	string	$pageName
	 * 		Page name
	 * 
	 * @param	string	$sitemapPath
	 * 		Page's sitemap path
	 * 
	 * @return	boolean
	 * 		True if the page name is reserved
	 */
	private function isReservedPage($pageName, $sitemapPath)
	{
		if (empty($sitemapPath) && in_array($pageName, $this->reservedPages))
			return TRUE;
		return FALSE;
	}
	
	/**
	 * Checks if a sitemap path is reserved
	 * 
	 * @param	string	$sitemapPath
	 * 		Sitemap path to check
	 * 
	 * @return	boolean
	 * 		True if the sitemap path is reserved
	 */
	private function isReservedSitemap($sitemapPath)
	{
		if (in_array($sitemapPath, $this->reservedSitemaps))
			return TRUE;
		return FALSE;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function online(){}
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function offline(){}
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function setState(){}
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function update(){}
}
//#section_end#
?>