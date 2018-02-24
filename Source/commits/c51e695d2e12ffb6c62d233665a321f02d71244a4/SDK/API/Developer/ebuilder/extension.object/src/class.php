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

importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "ebuilder::extManager");
importer::import("API", "Developer", "ebuilder::extComponents::extSrcObject");
importer::import("API", "Developer", "ebuilder::extComponents::extPage");
importer::import("API", "Developer", "ebuilder::extComponents::extScript");
importer::import("API", "Developer", "ebuilder::extComponents::extStyle");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "filesystem::folderManager"); 
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Developer", "profiler::logger");

use \API\Developer\profiler\logger;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\misc\vcs;
use \API\Developer\ebuilder\extManager;
use \API\Developer\ebuilder\extComponents\extSrcObject;
use \API\Developer\ebuilder\extComponents\extPage;
use \API\Developer\ebuilder\extComponents\extScript;
use \API\Developer\ebuilder\extComponents\extStyle;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \API\Resources\geoloc\locale;

importer::import("API", "Profile", "user");
use \API\Profile\user;


/**
 * extension development manager
 * 
 * Manages extension development proccess, release and deploy procedures.
 * 
 * @version	{empty}
 * @created	June 5, 2013, 17:10 (EEST)
 * @revised	July 31, 2013, 12:25 (EEST)
 */
class extension
{	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const MAPPING_FOLDER = "/.extension";	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SOURCE_FOLDER = "/src";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const STYLES_FOLDER = "/styles";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SCRIPTS_FOLDER = "/scripts";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const VIEWS_FOLDER = "/views";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const MEDIA_FOLDER = "/media";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const CONFIG_FOLDER = "/config";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const REPOSITORY = "/Repository";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const RELEASE = "/Release";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const CONTENT = "/Content";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const RESOURCES = "/Resourses";
	
	
	private $vcs;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $namePrefix ='';
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $folderExt = '';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $parser;
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $isDeployed = FALSE;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $objectId = '';
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $groupId = '';	 
			
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $workingFolder = '';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $libName = '';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private static $defaultLocale = 'en_US';
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $defaultStatusCode = '1';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $cfVarJqUsage = '0';
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $cfVarAssets = '0';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private static $status = array(
			'deploy' => '2',
			'project' => '1',
			'revise' => '3');
			
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private static $query = array(
			'getExtensionInfo' => '1621207856',
			'addExtension' => '75967096',
			'addExtensionLiteral' => '499755108',
			'getUserExtensions' => '345034401',
			'getAllCategories' => '499199859',
			'deleteExtensionLiteral' => '');	
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $xmlNodes = array(
			'indexRoot' => 'extension',
			'view' => 'view',
			'theme' => 'theme',
			'style' => 'style',
			'script' => 'script',
			'source' => 'src',
			'phpClass' => 'pc');
			
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $xmlIdPrefix = array(
			'view' => 'vw',
			'theme' => 'th',
			'style' => 'st',
			'script' => 'js',
			'phpScript' => 'psct',
			'phpClass' => 'pc');
	/**
	 * Constructor Method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$objectManager = new extManager(); 
		
		$this->repositoryPath = $objectManager->getRepositoryPath();
		$this->deployPath = $objectManager->getDeployPath();
		
		$this->namePrefix = $objectManager->getNamePrefix();
		$this->folderExt = $objectManager->getFolderExt();
		
		$this->libName = 'extension';
		
		//logger::log("appendBefore() takes no empty elements.", logger::ERROR);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getJqUsage()
	{
		return $this->cfVarJqUsage;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getAssetsExistance()
	{
		return $this->cfVarAssets;
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$jqUsage
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function setJqUsage($jqUsage)
	{
		$this->cfVarJqUsage = $jqUsage;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$assets
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function setAssetsExistance($assets)
	{
		$this->cfVarAssets = $assets;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$objectId
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function load($objectId)
	{
		$this->objectId= $objectId;
		$resultArray = $this->getExtensionInfo($objectId);
		if(empty($resultArray))
		{
			$this->objectId = '';
			return FALSE;				
		}
			
		$objectFolderName = $this->namePrefix.$this->objectId.$this->folderExt;			
		$this->workingFolder = $this->repositoryPath."/".$objectFolderName;
		// Init vcs
		$this->vcs = new vcs($this->workingFolder, TRUE);
		
		// Load xml index
		$this->parser = new DOMParser();		
		try
		{
			// Load index file
			$this->parser->load(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/index.xml", FALSE, FALSE);
		}
		catch (Exception $ex)
		{
			echo $ex;
			return FALSE;		
		}
		
		// Load Configuration Xml values to context
		$this->loadExtensionConfig();
		
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function loadExtensionConfig()
	{
		$parser = new DOMParser();
		$parser->load(systemRoot.$this->workingFolder.self::CONFIG_FOLDER."/"."settings.xml", FALSE, FALSE);
		
		$extConfig = $parser->evaluate("//extension")->item(0);
		
		// Load Attributes
		$cfVarJqUsage = $parser->attr($extConfig, "jqUsage");
		$cfVarAssets = $parser->attr($extConfig, "assets");
				
		$this->cfVarJqUsage = $cfVarJqUsage;
		$this->cfVarAssets  = $cfVarAssets;
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function reCreate($id)
	{
		$this->objectId = $id;
		$objectFolderRoot = $this->repositoryPath;
		$objectFolderName = $this->namePrefix.$this->objectId.$this->folderExt;		
		$this->workingFolder  = $objectFolderRoot."/".$objectFolderName;		
		
		// Init vcs
		$this->vcs = new vcs($this->workingFolder, TRUE);
		$this->vcs->createStructure();
		
		// Create Folders
		$this->buildTemporaryNoVcs();		
		
		//$success = $this->buildCatalogs();
		$success = $this->createIndex();
					
		if(!$success)
			return FALSE;
		
		$this->initStructure();
		
		return TRUE;
	}	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @param	{type}	$category
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function create($name, $description, $category) 
	{
		$profile = user::profile(); 
		$userId = $profile['id'];
	
		//Prevent from creating, when an extension is already loaded
		if(!empty($this->objectId))
			return FALSE;	
		
		// Create database entry
		$dbc = new interDbConnection();	
		
		// #Insert Into templates array
		$dbq = new dbQuery(self::$query['addExtension'], "ebuilder.extension");
						
		// Set Query Attributes						
		$attr = array();
		$attr['category'] = $category; 
		$attr['status'] = $this->defaultStatusCode;
		$attr['userId'] = $userId; 
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
		$row = $dbc->fetch($defaultResult);
		$this->objectId = $row['last_id'];		
		
		// Check if template db entry is created
		if(empty($this->objectId) || is_null($this->objectId))
			return FALSE;		
		
		$this->addExtensionLiteral($this->objectId, locale::getDefault(), $name, $description);			
		
		// Declare Repository Object folder
		$objectFolderRoot = $this->repositoryPath;
		$objectFolderName = $this->namePrefix.$this->objectId.$this->folderExt;		
		$this->workingFolder = $objectFolderRoot."/".$objectFolderName;
		
		// Init vcs
		$this->vcs = new vcs($this->workingFolder, TRUE);
		$this->vcs->createStructure();
		
		// Create Directory Object structure
		$this->buildTemporaryNoVcs();
		
		//$success = $this->buildCatalogs();
		$success = $this->createIndex();
		
		if(!$success)
			return FALSE;
			
		
		// Build init state of object
		$this->initStructure();
		
		return TRUE;
	}
	
	private function createIndex()
	{
		$rootPath = systemRoot.$this->workingFolder;
		
		$success = folderManager::create($rootPath.self::MAPPING_FOLDER);
		
		if(!$success)
			return FALSE;		
	
		// Create Object index / mapping XML
		$this->parser = new DOMParser();		
		$root = $this->parser->create($this->xmlNodes['indexRoot']);
		$this->parser->append($root);		
		$success = $this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		
		if(!$success)
			return FALSE;
			
		// Initialize Index XML srtucture
		$this->initIndexXml($this->workingFolder);
		
		return TRUE;
	}
	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function buildTemporaryNoVcs()
	{	
		$rootPath = systemRoot.$this->workingFolder;
	
		
		// #Create Styles Root Folder
		$success = folderManager::create($rootPath.self::STYLES_FOLDER);
			
		$success = folderManager::create($rootPath.self::MEDIA_FOLDER);
		
		
		// #Create Configuration Folder
		$success = folderManager::create($rootPath.self::CONFIG_FOLDER);
		
		$success = folderManager::create($rootPath.self::CONTENT);		
		

			return TRUE;		
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function initStructure()
	{
		// Create Default Content Folder
		//folderManager::create(systemRoot.$this->workingFolder.self::CONTENT."/".locale::getDefault());		
	
		// Create Main Package
		$this->addSrcPackage("Main");
		
		// Create Main View
		//$this->addView("MainView");
		
		// Create main Script and Style
		//$this->addScript("Main");
		//$this->addTheme("Main");
		
		// Add additional - non vcs
		//$this->addStyle();
		
		echo $this->createExtensionConfig();
		$this->updateExtensionConfig();
		
		//$this->createUserConfig();
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function initIndexXML()
	{	
		// Return FALSE and exit, in error or if template is in deploy state	
		if($this->isDeployed)
			return FALSE;
		
		$root = $this->parser->evaluate("//".$this->xmlNodes['indexRoot'])->item(0);
			
		//Add index xml views root entry
		$xmlEntry = $this->parser->create($this->xmlNodes['view'].'s');
		$this->parser->append($root, $xmlEntry);
		
		//Add index xml themes root entry
		$xmlEntry = $this->parser->create($this->xmlNodes['theme'].'s');
		$this->parser->append($root, $xmlEntry);
		
		//Add index xml general css root entry
		$xmlEntry = $this->parser->create($this->xmlNodes['style'].'s');
		$this->parser->append($root, $xmlEntry);
		
		//Add index xml js script root entry
		$xmlEntry = $this->parser->create($this->xmlNodes['script'].'s');
		$this->parser->append($root, $xmlEntry);
		
		//Add index xml php script root entry
		$xmlEntry = $this->parser->create($this->xmlNodes['source']);
		$this->parser->append($root, $xmlEntry);		
			// Create Library Index
			$lib = $this->parser->create("Library");
			$this->parser->attr($lib, "name", $this->libName);
			$this->parser->append($xmlEntry, $lib);
		
		// Save File
		$saveFlag = $this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function createExtensionConfig()
	{
		// Create Website configuration root
		$parser = new DOMParser();
		$root = $parser->create("configuration");
		$parser->append($root);
		
		$extConfig = $parser->create("extension");
		$parser->append($root, $extConfig);
				
				echo systemRoot.$this->workingFolder.self::CONFIG_FOLDER."/"."settings.xml";
				
		return $parser->save(systemRoot.$this->workingFolder.self::CONFIG_FOLDER."/", "settings.xml", $format = TRUE);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function createUserConfig()
	{
		
	}
		
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function updateExtensionConfig()
	{
		// Create Website configuration root
		$parser = new DOMParser();
		$parser->load(systemRoot.$this->workingFolder.self::CONFIG_FOLDER."/"."settings.xml", FALSE, FALSE);
		
		$extConfig = $parser->evaluate("//extension")->item(0);					
		$parser->attr($extConfig, "jqUsage", $this->cfVarJqUsage);
		$parser->attr($extConfig, "assets", $this->cfVarAssets);
	
		return $parser->save(systemRoot.$this->workingFolder.self::CONFIG_FOLDER."/", "settings.xml", $format = TRUE);
	}	 
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function addSrcPackage($packageName)
	{
		$root = $this->parser->evaluate("//Library")->item(0);
		
		// Check if package exists
		$package = $this->parser->evaluate("//package[@id='$packageName']")->item(0);
		
		if (!is_null($package))
			return FALSE;
				
		// Create Package Entry
		$package = $this->parser->create("package", "", $packageName);
		$this->parser->attr($package, "name", $packageName);
		$this->parser->append($root, $package);
		
		// Save File
		$this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		
		return TRUE;
	}	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function addSrcNamespace($packageName, $nsName, $parentNs = "")
	{
		// Library 			
		$root = $this->parser->evaluate("//Library")->item(0);
		
		// Search for package
		$package = $this->parser->evaluate("//package[@id='$packageName']")->item(0);
		if (is_null($package))
			throw new Exception("Package '$packageName' doesn't exist inside Library '$libName'.");
		
		// Get parent namespace
		if ($parentNs == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $parentNs);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $this->parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$packageName'.");
		}
		
		// Search for same namespace
		$namespace = $this->parser->evaluate("namespace[@name='$nsName']", $parent)->item(0);

		if (!is_null($namespace))
			return FALSE;
			
		$namespace = $this->parser->create("namespace");
		$this->parser->attr($namespace, "name", $nsName);
		$this->parser->append($parent, $namespace);		
		
		$this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);		
		
		return TRUE;	
	}
		
	/**
	 * {description}
	 * 
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The namespace name.
	 * 
	 * @param	string	$objectName
	 * 		The object name.
	 * 
	 * @param	string	$title
	 * 		The object title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function addSrcObject($packageName, $objectName, $nsName = "")
	{
		// Search for package
		$package = $this->parser->evaluate("//package[@id='$packageName']")->item(0);
		if (is_null($package))
			throw new Exception("Package '$packageName' doesn't exist inside Library '$this->libName'.");
		
		// If not namespace given, get package as parent
		if ($nsName == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $nsName);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $this->parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$nsName' doesn't exist inside Package '$libName' -> '$packageName'.");
		}
			
		// Search for same object
		$object = $this->parser->evaluate("object[@name='$objectName']", $parent)->item(0);
		
		if (!is_null($object))
			return FALSE;

		$object = $this->parser->create("object");
		$this->parser->attr($object, "name", $objectName);
		$this->parser->append($parent, $object);
		
		$saveFlag = $this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		if (!$saveFlag)
			return FALSE;
		
		// Build object
		$extSrcObject = new extSrcObject($this->workingFolder.self::SOURCE_FOLDER, $includeRelease = TRUE, $this->libName, $packageName, $nsName, $objectName);
		return $extSrcObject->create($objectName);
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function addScript($name)
	{
		// Chech existance
		if ($this->objectExists($this->xmlNodes['script'].'s', $name, $this->xmlIdPrefix['script']))
			return FALSE;
		
		// Add to index
		$root = $this->parser->evaluate("//".$this->xmlNodes['script'].'s')->item(0);
		//Create		
		$xmlEntry = $this->parser->create($this->xmlNodes['script'], "", $this->xmlIdPrefix['script']."_".$name);
		$this->parser->attr($xmlEntry, "name", $name);
		
		$this->parser->append($root, $xmlEntry);
		$this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		
		// Build object
		$extScript = new extScript($this->workingFolder.self::REPOSITORY.self::SCRIPTS_FOLDER);
		$extScript->create($name);
		
		return TRUE;
	}	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function addView($name)
	{
		// Chech existance
		if ($this->objectExists($this->xmlNodes['view'].'s', $name, $this->xmlIdPrefix['view']))
			return FALSE;
		
		// Add to index
		$root = $this->parser->evaluate("//".$this->xmlNodes['view'].'s')->item(0);
		//Create		
		$xmlEntry = $this->parser->create($this->xmlNodes['view'], "", $this->xmlIdPrefix['view']."_".$name);
		$this->parser->attr($xmlEntry, "name", $name);
		
		$this->parser->append($root, $xmlEntry);
		$this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		
		// Build object
		$extPage = new extPage($this->workingFolder.self::REPOSITORY.self::VIEWS_FOLDER);
		$extPage->create($name);
		
		return TRUE;
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function addTheme($name)
	{
		// Chech existance
		if ($this->objectExists($this->xmlNodes['theme'].'s', $name, $this->xmlIdPrefix['theme']))
			return FALSE;
		
		// Add to index
		$root = $this->parser->evaluate("//".$this->xmlNodes['theme'].'s')->item(0);
		//Create		
		$xmlEntry = $this->parser->create($this->xmlNodes['theme'], "", $this->xmlIdPrefix['theme']."_".$name);
		$this->parser->attr($xmlEntry, "name", $name);
		
		$this->parser->append($root, $xmlEntry);
		$this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		
		// Build object
		$extStyle = new extStyle($this->workingFolder.self::REPOSITORY.self::STYLES_FOLDER."/themes");
		$extStyle->create($name);
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function addStyle()
	{
		$this->updateMainStyle("");
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$templateId
	 * 		{description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function addExtensionLiteral($templateId, $locale, $name, $description)
	{
		// Create database entry
		$dbc = new interDbConnection();	
					
		// #Insert Into templates Literal array
		$dbq = new dbQuery(self::$query['addExtensionLiteral'], "ebuilder.extension");
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $templateId;
		$attr['locale'] = $locale;
		$attr['title'] = $name;
		$attr['description'] = $description;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateMainStyle($code = "")
	{
		// Get Object Folder Path
		$sourceFile = systemRoot.$this->workingFolder.self::STYLES_FOLDER."/style.css";	
		
		// If code is empty, create an empty Style file
		if ($code == "")
			$code = phpParser::comment("Write Your Style Code Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Save javascript file
		return fileManager::create($sourceFile, $code);
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$rootName
	 * 		{description}
	 * 
	 * @param	{type}	$nodeName
	 * 		{description}
	 * 
	 * @param	{type}	$idPrefix
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function objectExists($rootName, $nodeName, $idPrefix)
	{
		// Check if object exists in the website
		$objectsRoot = $this->parser->evaluate("//".$this->xmlNodes['indexRoot']."/".$rootName)->item(0);
		$object = $this->parser->find($idPrefix."_".$nodeName, $objectsRoot);
		if (is_null($object))
			return FALSE;
		return TRUE;
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$rootName
	 * 		{description}
	 * 
	 * @param	{type}	$nodeName
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function getObjectsList($rootName, $nodeName)
	{
		$objectwArray = array();	 		
		$root = $this->parser->evaluate('//'.$rootName)->item(0); 
		if(is_null($root))
			return FALSE;
		
		$innerNodes = $this->parser->evaluate('//'.$nodeName, $root);		
		foreach ($innerNodes as $node)
		{
			array_push($objectwArray, $this->parser->attr($node, 'name'));
		}
		
		return $objectwArray;
	}	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getAllViews()
	{
		return $this->getObjectsList($this->xmlNodes['view'].'s', $this->xmlNodes['view']);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getAllJsScripts()
	{
		return $this->getObjectsList($this->xmlNodes['script'].'s', $this->xmlNodes['script']);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getAllThemes()
	{
		return $this->getObjectsList($this->xmlNodes['theme'].'s', $this->xmlNodes['theme']);
	}	
		
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getLibraryObjects()
	{
		$info = array();		
		
		// Get Library
		$libraryBase = $this->parser->evaluate("//Library[@name='$this->libName']")->item(0);

		// Get Library Objects
		$objects = $this->parser->evaluate("//object");

		$info = array();
		foreach ($objects as $o)
		{
			// Build Object
			$initName = $this->parser->attr($o, "name");
			
			$opts = array();
			$opts['title'] = $this->parser->attr($o, "title");
			$opts['name'] = $initName;
			
			// Get Namespace
			$namespace = $initName;
			$parent_namespace = $o->parentNode;
			while ($parent_namespace->tagName == "namespace")
			{
				$namespace = $this->parser->attr($parent_namespace, "name")."::".$namespace;
				$parent_namespace = $parent_namespace->parentNode;
			}
			// Set Package Namespace
			$namespace = $this->parser->attr($parent_namespace, "id")."::".$namespace;
			$opts['import'] = $namespace;
			
			// Set Object
			$info[$initName] = $opts;
		}
		return $info;
	}	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$fullNames
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getPackageList($fullNames = TRUE)
	{
		// Load Library
		$root = $this->parser->evaluate("//Library")->item(0);
				
		$packages = $this->parser->evaluate("//package");
		$pkgArray = array();
		foreach ($packages as $pkg)
			$pkgArray[$this->parser->attr($pkg, "id")] = $this->parser->attr($pkg, "id");

		return $pkgArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getNSList($packageName, $parentNs = "")
	{
		// Load Library
		$root = $this->parser->evaluate("//Library")->item(0);
		
		// Get Package
		$package = $this->parser->evaluate("//package[@id='$packageName']")->item(0);
		
		if ($parentNs == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $parentNs);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $this->parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$packageName'.");
		}
		
		// Get Children namespaces
		$namespaces = $this->parser->evaluate("namespace", $parent);
		
		// Create array
		$nsArray = array();
		foreach ($namespaces as $ns)
		{
			$nsName = $this->parser->attr($ns, "name");
			$tempParent = ($parentNs == "" ? "" : $parentNs."::").$this->parser->attr($ns, "name");
			$nsArray[$nsName] = self::getNSList($path, $libName, $packageName, $tempParent);
		}
		
		return $nsArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getPackageObjects($packageName, $parentNs = "")
	{
		$parentNs = str_replace("_", "::", $parentNs);
		
		// Load Library 
		$root = $this->parser->evaluate("//Library")->item(0);
		
		// Set Query
		if ($parentNs == "")
			$xpathQuery = "//*[@id='$packageName']//object";
		else
		{
			$nss = explode("::", $parentNs);
			$xpathQuery = "namespace[@name='".implode("']/namespace[@name='", $nss)."']//object";
		}
		
		// Get Library Objects
		$objects = $this->parser->evaluate($xpathQuery);
		
		$info = array();
		foreach ($objects as $o)
		{
			// Build Object
			$oName = $this->parser->attr($o, "name");
			
			$opts = array();
			$opts['title'] = $this->parser->attr($o, "title");
			$opts['name'] = $oName;
			
			// Get Namespace
			$namespace = "";//$initName;
			$parent_namespace = $o->parentNode;
			while ($parent_namespace->tagName == "namespace")
			{
				$namespace = $this->parser->attr($parent_namespace, "name").($namespace == "" ? "" : "::".$namespace);
				$parent_namespace = $parent_namespace->parentNode;
			}
			
			// Set Object Paths
			$opts['lib'] = $libName;
			$opts['pkg'] = $packageName;
			$opts['ns'] = $namespace;
			
			// Set Object
			$info[] = $opts;
		}
		return $info;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getNSObjects($packageName, $parentNs = "")
	{
		$parentNs = str_replace("_", "::", $parentNs);
		
		// Load Library  
		$root = $this->parser->evaluate("//Library")->item(0);
		
		// Get Package
		$package = $this->parser->find($packageName);
		
		if ($parentNs == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $parentNs);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $this->parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$packageName'.");
		}

		// Get Library Objects
		$objects = $this->parser->evaluate("object", $parent);

		$info = array();
		foreach ($objects as $o)
		{
			// Build Object
			$initName = $this->parser->attr($o, "name");
			
			$opts = array();
			$opts['title'] = $this->parser->attr($o, "title");
			$opts['name'] = $initName;
			
			// Get Namespace
			$namespace = $initName;
			$parent_namespace = $o->parentNode;
			while ($parent_namespace->tagName == "namespace")
			{
				$namespace = $this->parser->attr($parent_namespace, "name")."::".$namespace;
				$parent_namespace = $parent_namespace->parentNode;
			}
			// Set Package Namespace
			$opts['import'] = $namespace;
			
			// Set Object
			$info[$initName] = $opts;
		}
		return $info;
	}	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getSrcObject($packageName, $objectName, $nsName = "")
	{
		// Search for package
		$package = $this->parser->evaluate("//package[@id='$packageName']")->item(0);
		if (is_null($package))
			throw new Exception("Package '$packageName' doesn't exist inside Library '$this->libName'.");
		
		// If not namespace given, get package as parent
		if ($nsName == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $nsName);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $this->parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$nsName' doesn't exist inside Package '$libName' -> '$packageName'.");
		}
			
		// Search for same object
		$object = $this->parser->evaluate("object[@name='$objectName']", $parent)->item(0);
		
		if (is_null($object))
			return FALSE;				
		
		return new extSrcObject($this->workingFolder.self::REPOSITORY, self::SOURCE_FOLDER, $this->libName, $packageName, $nsName, $objectName); 	
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getScript($name)
	{
		if (!$this->objectExists($this->xmlNodes['script'].'s', $name, $this->xmlIdPrefix['script']))
			return FALSE;
			
		return new extScript($this->workingFolder.self::REPOSITORY.self::SCRIPTS_FOLDER, $name);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getTheme($name)
	{
		if (!$this->objectExists($this->xmlNodes['theme'].'s', $name, $this->xmlIdPrefix['theme']))
			return FALSE;
			
		return new extStyle($this->workingFolder.self::REPOSITORY.self::STYLES_FOLDER."/themes", $name);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getMainStyle()
	{
		$sourceFile = $this->workingFolder.self::STYLES_FOLDER."/style.css";		
		return fileManager::get($sourceFile);	
	}
			
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getView($name)
	{
		if (!$this->objectExists($this->xmlNodes['view'].'s', $name, $this->xmlIdPrefix['view']))
			return FALSE;
						
		$view = new extPage($this->workingFolder.self::REPOSITORY.self::VIEWS_FOLDER, $name);
		$view->loadInfo();
		return $view;
	}
		
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getMediaFolder()
	{
		return $this->workingFolder.self::MEDIA_FOLDER;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getContentFolder()
	{
		return $this->workingFolder.self::CONTENT_FOLDER;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getUserConfigurator()
	{
	
	}	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getExtensionConfig()
	{
	
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deleteSrcPackage($packageName)
	{
		// Chech existance
		if (!$this->objectExists($this->xmlNodes[''].'s', $packageName, $this->xmlIdPrefix['']))
			return FALSE;	
	}	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deleteSrcNamespace($packageName, $nsName, $parentNs = "") 
	{
		// Chech existance
		if (!$this->objectExists($this->xmlNodes[''].'s', $name, $this->xmlIdPrefix['']))
			return FALSE;
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deleteSrcObject($packageName, $objectName, $nsName = "")
	{
		// Chech existance
		if (!$this->objectExists($this->xmlNodes[''].'s', $name, $this->xmlIdPrefix['']))
			return FALSE;
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deleteScript($name)
	{
		// Chech existance
		if (!$this->objectExists($this->xmlNodes['script'].'s', $name, $this->xmlIdPrefix['script']))
			return FALSE;		
		
		// Build object
		$extScript = new extScript($this->workingFolder.self::REPOSITORY.self::SCRIPTS_FOLDER, $name);
		$status = $extScript->delete();
		
		if($status)
		{
			//Delete entry from mapping xml
			
			$objectsRoot = $this->parser->evaluate("//".$this->xmlNodes['indexRoot']."/".$this->xmlNodes['script'].'s')->item(0);
			$object = $this->parser->find($this->xmlIdPrefix['script']."_".$name, $objectsRoot);
			
			//Delete entry
			$this->parser->replace($object, NULL);
			$status = $this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		}		
		if(is_bool($status))
			return $status;
		else
			return $status > 0;		
	}	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deleteView($name)
	{		
		// Chech existance
		if (!$this->objectExists($this->xmlNodes['view'].'s', $name, $this->xmlIdPrefix['view']))
			return FALSE;		
		
		$objectCount = count($this->getAllViews());
		
		// Dont delete the last
		if($objectCount < 2)
			return FALSE;
	
		// Build object
		$extPage= new extPage($this->workingFolder.self::REPOSITORY.self::VIEWS_FOLDER, $name); 
		$status = $extPage->delete();
		
		if($status)
		{
			//Delete entry from mapping xml			
			$objectsRoot = $this->parser->evaluate("//".$this->xmlNodes['indexRoot']."/".$this->xmlNodes['view'].'s')->item(0);
			$object = $this->parser->find($this->xmlIdPrefix['view']."_".$name, $objectsRoot);
			
			//Delete entry
			$this->parser->replace($object, NULL);
			$status = $this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		}		
		if(is_bool($status))
			return $status;
		else
			return $status > 0;
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deleteTheme($name)
	{	
		// Chech existance
		if (!$this->objectExists($this->xmlNodes['theme'].'s', $name, $this->xmlIdPrefix['theme']))
			return FALSE;		
		
		// Build object
		$extScript = new extStyle($this->workingFolder.self::REPOSITORY.self::STYLES_FOLDER."/themes", $name);
		$status = $extScript->delete();
		
		if($status)
		{
			//Delete entry from mapping xml
			
			$objectsRoot = $this->parser->evaluate("//".$this->xmlNodes['indexRoot']."/".$this->xmlNodes['theme'].'s')->item(0);
			$object = $this->parser->find($this->xmlIdPrefix['theme']."_".$name, $objectsRoot);
			
			//Delete entry
			$this->parser->replace($object, NULL);
			$status = $this->parser->save(systemRoot.$this->workingFolder.self::MAPPING_FOLDER."/", "index.xml", FALSE);
		}				
		if(is_bool($status))
			return $status;
		else
			return $status > 0;
	}
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deleteExtensionLiteral($locale)
	{
		// Create database entry
		$dbc = new interDbConnection();	
					
		// #Insert Into templates Literal array
		$dbq = new dbQuery(self::$query['deleteExtensionLiteral'], "ebuilder.extension");
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $this->objectId;
		$attr['locale'] = $locale;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getExtensionInfo($id = '')
	{		
		if(isset($this))
		{
			$id = $this->objectId;
		}
		if(empty($id))
		{
			return array();
		}
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['getExtensionInfo'], "ebuilder.extension");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $id;
		$attr['locale'] = locale::getDefault();
		
		$defaultResult = $dbc->execute_query($dbq, $attr);			
		
		$row = $dbc->fetch($defaultResult);
		//$infoArray['extensionID'] = $row['extensionID'];
		$infoArray['extensionTitle'] = $row['extensionTitle'];
		$infoArray['extensionDescription'] = $row['extensionDescription'];
		$infoArray['extensionStatus'] = $row['extensionStatus'];
		//$infoArray['categoryID'] = $row['categoryID'];
		$infoArray['categoryTitle'] = $row['categoryTitle'];
		$infoArray['categoryDescription'] = $row['categoryDescription'];
			
		return $infoArray;
	}	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function release()
	{
		$path = systemRoot.$this->workingFolder;
		$latest = "/current";
	
		// Empty Release Folder
		folderManager::clean($path.self::RELEASE, $latest);
		
		// Create Release Structure
		// Create Views Root Folder
		$success = folderManager::create($path.self::RELEASE.$latest.self::VIEWS_FOLDER);
		// #Create Source Folder
		$success = folderManager::create($path.self::RELEASE.$latest.self::SOURCE_FOLDER);
		// #Create Scripts Folder
		$success = folderManager::create($path.self::RELEASE.$latest.self::SCRIPTS_FOLDER);
		// #Create Styles Root Folder
		$success = folderManager::create($path.self::RELEASE.$latest.self::STYLES_FOLDER);
			// Create Themes Folder
			$success = folderManager::create($path.self::RELEASE.$latest.self::STYLES_FOLDER."/themes");		
		// #Create Media Folder
		$success = folderManager::create($path.self::RELEASE.$latest.self::MEDIA_FOLDER);
		// #Create Configuration Folder
		$success = folderManager::create($path.self::RELEASE.$latest.self::CONFIG_FOLDER);
		
		
		// Populate Realease Folders form release branch
		// Release Views
		$exportPath = $path.self::RELEASE.$latest.self::VIEWS_FOLDER;
		$objectsArray = $this->getAllViews();
		foreach($objectsArray as $objectName)
		{
			$object = $this->getView($objectName);
			$object->export($exportPath);
		}
		
		// Release Library
		$exportPath = $path.self::RELEASE.$latest.self::STYLES_FOLDER;
		$packagesArray = $this->getPackageList();
		foreach ($packagesArray as $packageName)
		{
			$packageObjects = $this->getPackageObjects($packageName);
			foreach ($packageObjects as $object)
			{
				$object = $this->getSrcObject($packageName, $object['ns'], $object['name']);
				$object->export($exportPath);
			}
		}
		
		
		// Release Themes
		$exportPath = $path.self::RELEASE.$latest.self::STYLES_FOLDER."/themes";
		$objectsArray = $this->getAllThemes();
		foreach($objectsArray as $objectName)
		{
			$object = $this->getTheme($objectName);
			$object->export($exportPath);
		}
		
		// Release style
		$exportPath = $path.self::RELEASE.$latest.self::STYLES_FOLDER;
		fileManager::copy($this->workingFolder.self::STYLES_FOLDER."/style.css", $exportPath."/style.css");	
		
		// Release jsScripts
		$exportPath = $path.self::RELEASE.$latest.self::SCRIPTS_FOLDER;
		$objectsArray = $this->getAllJsScripts();
		foreach($objectsArray as $objectName)
		{
			$object = $this->getScript($objectName);
			$object->export($exportPath);
		}
		
		// Prepare config xml
		$parser = new DOMParser();			
		$root = $parser->create("configuration");
		$parser->append($root);
		
		// Merge Views Configuration With Extension
		$viewArray = $this->getAllViews();
		foreach($viewArray as $view)
		{
			$object = $this->getView($view);
			$object->loadInfo();
			$viewImports = 	$object->getImports();
			
								
			
			//
			$viewEntry = $parser->create("view", "", $view);
			
			
			$parser->append($root, $viewEntry );
			
			
					
					
			
		}
		
		// save xml
		$parser->save($path.self::RELEASE.$latest.self::CONFIG_FOLDER."/", "settings.xml", $format = TRUE);
		
		
		
		// Zip current release and add to History		
		// Create zip archive of current state in history
		//$contents = directory::getContentList(systemRoot.$this->devPath.self::RELEASE_FOLDER.self::CURRENT_RELEASE_FOLDER, TRUE);
		//$destination = systemRoot.$this->devPath.self::RELEASE_FOLDER.self::RELEASE_HISTORY_FOLDER;
		//$archiveName = "/version_description.zip"; // Needs proper name
		//zipManager::create($destination.$archiveName, $contents);
		
		// Update history of the website
		//$this->updateWebsiteHistoryIndexing("Initial Website State");
		
		return TRUE;	
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function deploy()
	{
		$extInfo = $this->getExtensionInfo();
		// Check deploy state
		if(!$extInfo['extensionStatus'] == 2)
		{
			// Change it, if it needs to
		}
		
		$source = systemRoot.$this->workingFolder.self::RELEASE;
		$destination= systemRoot.$this->deployPath."/".$this->namePrefix.$this->objectId.$this->folderExt;		
		
		// Copy files from release/current to library/ebuilder
		folderManager::copy($source, $destination);	
	}
	

}
//#section_end#
?>