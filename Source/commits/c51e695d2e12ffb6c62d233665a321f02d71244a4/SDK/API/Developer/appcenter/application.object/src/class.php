<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter;

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
 * @namespace	\appcenter
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "appcenter::appManager");
importer::import("API", "Developer", "appcenter::appComponents::appSrcPackage");
importer::import("API", "Developer", "appcenter::appComponents::appSrcObject");
importer::import("API", "Developer", "appcenter::appComponents::appView");
importer::import("API", "Developer", "appcenter::appComponents::appScript");
importer::import("API", "Developer", "appcenter::appComponents::appStyle");
importer::import("API", "Developer", "appcenter::appComponents::appSettings");
importer::import("API", "Developer", "appcenter::appComponents::appLiterals");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Security", "account");
importer::import("INU", "Views", "fileExplorer");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\appcenter\appManager;
use \API\Developer\appcenter\appComponents\appSrcPackage;
use \API\Developer\appcenter\appComponents\appSrcObject;
use \API\Developer\appcenter\appComponents\appView;
use \API\Developer\appcenter\appComponents\appScript;
use \API\Developer\appcenter\appComponents\appStyle;
use \API\Developer\appcenter\appComponents\appSettings;
use \API\Developer\appcenter\appComponents\appLiterals;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\misc\vcs;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\geoloc\locale;
use \API\Security\account;
use \INU\Views\fileExplorer;

/**
 * Redback Application
 * 
 * This class is responsible for the development of an application.
 * It works as a provider for access to application components.
 * 
 * @version	{empty}
 * @created	June 3, 2013, 16:19 (EEST)
 * @revised	November 4, 2013, 8:42 (EET)
 */
class application
{
	/**
	 * The index file name.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * The source folder name.
	 * 
	 * @type	string
	 */
	const SOURCE_FOLDER = "/src";
	
	/**
	 * The styles folder name.
	 * 
	 * @type	string
	 */
	const STYLES_FOLDER = "/styles";
	
	/**
	 * The scripts folder name.
	 * 
	 * @type	string
	 */
	const SCRIPTS_FOLDER = "/scripts";
	
	/**
	 * The views folder name.
	 * 
	 * @type	string
	 */
	const VIEWS_FOLDER = "/views";
	
	/**
	 * The resources folder name.
	 * 
	 * @type	string
	 */
	const MEDIA_FOLDER = "/media";
	
	/**
	 * The configuration folder name.
	 * 
	 * @type	string
	 */
	const CONFIG_FOLDER = "/config";
	
	/**
	 * The content folder name.
	 * 
	 * @type	string
	 */
	const CONTENT_FOLDER = "/content";
	
	/**
	 * The private scope name.
	 * 
	 * @type	string
	 */
	const SCOPE_PRIVATE = "private";
	
	/**
	 * The public scope name.
	 * 
	 * @type	stirng
	 */
	const SCOPE_PUBLIC = "public";
	
	/**
	 * The application name.
	 * 
	 * @type	string
	 */
	private $appID;
	
	/**
	 * The application data.
	 * 
	 * @type	array
	 */
	private $appData;
	
	/**
	 * The application's developer path.
	 * 
	 * @type	string
	 */
	private $devPath;
	
	/**
	 * The vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 		Leave empty for new applications.
	 * 
	 * @return	void
	 */
	public function __construct($appID = "")
	{
		if (!empty($appID))
			$this->init($appID);
	}
	
	/**
	 * Gets the vcs manager object.
	 * 
	 * @return	vcs
	 * 		The vcs manager object.
	 */
	public function getVCS()
	{
		return $this->vcs;
	}
	
	/**
	 * Initializes the application with all the names and the paths.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	private function init($appID)
	{
		// Set ID
		$this->appID = $appID;
		$this->appData = appManager::getApplicationData($appID);
		
		// Set vcs folder
		$appMan = new appManager();
		$this->devPath = $appMan->getDevAppFolder($appID);
		$this->vcs = new vcs($this->devPath, TRUE);
	}
	
	/**
	 * Creates a new application.
	 * 
	 * @param	string	$name
	 * 		The application name
	 * 
	 * @param	string	$scope
	 * 		The application scope.
	 * 
	 * @param	string	$fullName
	 * 		The full application name.
	 * 
	 * @param	string	$tags
	 * 		The application tags.
	 * 
	 * @param	string	$description
	 * 		The application description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($name, $scope = self::SCOPE_PRIVATE, $fullName = "", $tags = "", $description = "")
	{
		// Correct scope if wrong
		if ($scope != self::SCOPE_PRIVATE && $scope != self::SCOPE_PUBLIC)
			$scope = self::SCOPE_PRIVATE;
		
		// Create database record 462826332
		$dbc = new interDbConnection();
		$q = new dbQuery("462826332", "apps");
		$attr = array();
		$attr['name'] = $name;
		$attr['fullName'] = $fullName;
		$attr['scope'] = $scope;
		$attr['tags'] = $tags;
		$attr['description'] = $description;
		$attr['authorID'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		
		if (!$result)
			return FALSE;

		// Initialize application
		$app = $dbc->fetch($result);
		$this->init($app['id']);
		
		// Create vcs structure
		$this->vcs->createStructure();
		
		// Initialize Application Structure Files
		$this->createAppStructure();
		
		return TRUE;
	}
	
	/**
	 * Creates a releases and publishes the application.
	 * 
	 * @param	string	$version
	 * 		The release version.
	 * 
	 * @param	string	$description
	 * 		The release description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($version, $description)
	{
		// VCS Release
		$releaseFolder = $this->vcs->release($version, $description);
		if ($releaseFolder === FALSE)
			return FALSE;
			
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = systemRoot.appManager::getPublishedAppFolder($this->appID);
		folderManager::create($publishFolder);
		
		// Clean folder
		folderManager::clean($publishFolder, $name = "", $includeHidden = TRUE);
		
		// Update the database
		$dbc = new interDbConnection();
		$q = new dbQuery("141116177", "apps");
		$attr = array();
		$attr['id'] = $this->appID;
		$result = $dbc->execute($q, $attr);
		
		// Set cssContent && jsContent
		$cssContent = "";
		$jsContent = "";
		
		// Gather Application Styles
		$styles = $this->getStyles();
		foreach ($styles as $style)
		{
			$appStyle = $this->getStyle($style);
			$cssContent .= $appStyle->get()."\n";
		}
		
		// Gather Application Scripts
		$scripts = $this->getScripts();
		foreach ($scripts as $script)
		{
			$appScript = $this->getScript($script);
			$jsContent .= $appScript->get()."\n";
		}
		
		// Publish Views
		$views = $this->getViews();
		foreach ($views as $view)
		{
			// Initialize view
			$appView = $this->getView($view);
			
			// Get css and js
			$cssContent .= $appView->getCSS()."\n";
			$jsContent .= $appView->getJS()."\n";
			
			// Publish html
			$viewHTML = $appView->getHTML();
			fileManager::create($publishFolder."/views/".$view.".view/view.html", $viewHTML, TRUE);
			
			// Publish php
			$viewPHP = $appView->getPHPCode($fukk = TRUE);
			fileManager::create($publishFolder."/views/".$view.".view/view.php", $viewPHP, TRUE);
		}
		
		// Publish Source Objects
		$srcPackage = $this->getSrcPackage();
		$packages = $srcPackage->getPackages($fullNames = TRUE);
		foreach ($packages as $packageName)
		{
			$objects = $srcPackage->getObjects($packageName, $parentNs = "");
			foreach ($objects as $object)
			{
				// Initialize source object
				$appSrcObj = $this->getSrcObject($packageName, $object['namespace'], $object['name']);
				
				// Export css and js
				$cssContent .= $appSrcObj->getCSSCode()."\n";
				$jsContent .= $appSrcObj->getJSCode()."\n";
				
				// Export source
				$objectSource = $appSrcObj->getSourceCode($full = TRUE);
				$namespace = str_replace("::", "/", $object['namespace']);
				$objectPath = $packageName."/".$namespace."/".$object['name'].".php";
				fileManager::create($publishFolder."/src/".$objectPath, $objectSource, TRUE);
			}
		}
		
		// Publish css
		$cssContent = cssParser::format($cssContent);
		fileManager::create($publishFolder."/style.css", $cssContent, TRUE);
		
		// Publish js
		$jsContent = cssParser::format($jsContent);
		fileManager::create($publishFolder."/script.js", $jsContent, TRUE);
		
		// Export Settings
		$appSettings = $this->getSettings();
		fileManager::create($publishFolder."/config/Settings.xml", $appSettings->getXML(), TRUE);
		
		// Export Lilterals
		$appLiterals = $this->getLiterals();
		$active = locale::active();
		foreach ($active as $locale)
		{
			try
			{
				$appLiterals->getXML($locale['locale']);
				$filePath = $publishFolder."/content/".$locale['locale']."/literals.xml";
				fileManager::create($filePath, $appLiterals->getXML($locale['locale']), TRUE);
			}
			catch (Exception $ex)
			{
			}
		}
		
		// Export media
		folderManager::create($publishFolder."/media/");
		folderManager::copy(systemRoot.$this->devPath."/media/", $publishFolder."/media/", TRUE);
		
		return TRUE;
	}
	
	/**
	 * Creates the application structure.
	 * 
	 * @return	void
	 */
	private function createAppStructure()
	{
		// Create application folders
		folderManager::create(systemRoot.$this->devPath."/.app/");
		folderManager::create(systemRoot.$this->devPath.self::MEDIA_FOLDER);
		
		// Create Application Index
		$this->createAppIndex();
		
		// Create Settings File
		$appSettings = $this->getSettings();
		$appSettings->create();
		
		// Create Main View
		$appView = $this->getView();
		$appView->create("MainView");
		
		// Set init settings
		$appSettings->set("STARTUP_VIEW", "MainView");
		$appSettings->set("DEFAULT_LOCALE", locale::getDefault());
	}
	
	/**
	 * Creates the application indexes.
	 * 
	 * @return	void
	 */
	private function createAppIndex()
	{
		// Create Application index root
		$parser = new DOMParser();
		$root = $parser->create("app");
		$parser->append($root);
		$parser->save(systemRoot.$this->devPath."/.app/", self::INDEX_FILE, $format = TRUE);
		
		// Populate index
		$stylesElement = $parser->create("styles");
		$parser->append($root, $stylesElement);
		
		$scriptsElement = $parser->create("scripts");
		$parser->append($root, $scriptsElement);
		
		$viewsElement = $parser->create("views");
		$parser->append($root, $viewsElement);
		
		$parser->save(systemRoot.$this->devPath."/.app/", self::INDEX_FILE, $format = TRUE);
	}
	
	/**
	 * Gets an application view.
	 * 
	 * @param	string	$name
	 * 		The view name.
	 * 
	 * @return	appView
	 * 		The appView object with the view loaded.
	 */
	public function getView($name = "")
	{
		return new appView($this->appID, $this->vcs, $this->devPath, $name);
	}
	
	/**
	 * Gets the application's source package object.
	 * 
	 * @return	appSrcPackage
	 * 		A appSrcPackage object.
	 */
	public function getSrcPackage()
	{
		return new appSrcPackage($this->devPath);
	}
	
	/**
	 * Gets the application's source object for development.
	 * 
	 * @param	string	$package
	 * 		The object's package.
	 * 
	 * @param	string	$namespace
	 * 		The object's namespace.
	 * 
	 * @param	string	$name
	 * 		The object's name.
	 * 
	 * @return	appSrcObject
	 * 		The appSrcObject object.
	 */
	public function getSrcObject($package, $namespace = "", $name = NULL)
	{
		$appName = $this->appData['name'];
		return new appSrcObject($appName, $this->devPath, $package, $namespace, $name);
	}
	
	/**
	 * Gets a script object.
	 * 
	 * @param	string	$name
	 * 		The script name. Leave empty for new scripts.
	 * 
	 * @return	appScript
	 * 		The appScript object loaded with the script name.
	 */
	public function getScript($name = "")
	{
		return new appScript($this->vcs, $this->devPath, $name);
	}
	
	/**
	 * Gets a style object.
	 * 
	 * @param	string	$name
	 * 		The style name. Leave empty for new style.
	 * 
	 * @return	appStyle
	 * 		The appStyle object loaded with the style name.
	 */
	public function getStyle($name = "")
	{
		return new appStyle($this->vcs, $this->devPath, $name);
	}
	
	/**
	 * Gets the application settings manager.
	 * 
	 * @return	appSettings
	 * 		The application settings manager object.
	 */
	public function getSettings()
	{
		return new appSettings($this->vcs);
	}
	
	/**
	 * Gets the application literal manager.
	 * 
	 * @return	appLiterals
	 * 		The application literal manager object.
	 */
	public function getLiterals()
	{
		$appSettings = $this->getSettings();
		return new appLiterals($this->vcs, $appSettings);
	}
	
	/**
	 * Returns a fileExplorer properly set to show the application's media folder.
	 * 
	 * @return	fileExplorer
	 * 		The fileExplorer object.
	 */
	public function getMediaManager()
	{
		return new fileExplorer($this->devPath.self::MEDIA_FOLDER, $id = "appMediaExplorer", $friendlyRootName = "Media", $showHidden = FALSE);
	}
	
	/**
	 * Adds an object's index to the application index file.
	 * 
	 * @param	string	$path
	 * 		The index file path.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$type
	 * 		The object type.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	void
	 */
	public static function addObjectIndex($path, $group, $type, $name)
	{
		// Load application index
		$parser = new DOMParser();
		$parser->load($path."/.app/".self::INDEX_FILE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);
		
		// Create object
		$obj = $parser->create($type);
		$parser->attr($obj, "name", $name);
		$parser->append($root, $obj);
		
		// Update file
		$parser->update();
	}
	
	/**
	 * Gets all application styles.
	 * 
	 * @return	array
	 * 		An array of styles by value.
	 */
	public function getStyles()
	{
		return $this->getIndexObjects("styles", "style");
	}
	
	/**
	 * Gets all application scripts.
	 * 
	 * @return	array
	 * 		An array of scripts by value.
	 */
	public function getScripts()
	{
		return $this->getIndexObjects("scripts", "script");
	}
	
	/**
	 * Gest all application views.
	 * 
	 * @return	array
	 * 		An array of views by value.
	 */
	public function getViews()
	{
		return $this->getIndexObjects("views", "view");
	}
	
	/**
	 * Gets the application object from the index defined by parameters.
	 * 
	 * @param	string	$group
	 * 		The object group.
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @return	array
	 * 		An array of objects by value.
	 */
	private function getIndexObjects($group, $name)
	{
		// Load application index
		$parser = new DOMParser();
		$parser->load($this->devPath."/.app/".self::INDEX_FILE);
		
		// Get The root
		$root = $parser->evaluate("//".$group)->item(0);

		// Get all packages
		$objects = array();
		$elements = $parser->evaluate($name, $root);
		foreach ($elements as $elm)
			$objects[] = $parser->attr($elm, "name");
		
		return $objects;
	}
}
//#section_end#
?>