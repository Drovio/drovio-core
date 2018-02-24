<?php
//#section#[header]
// Namespace
namespace AEL\Platform;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Platform
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Model", "core/manifests");
importer::import("API", "Model", "apps/application");
importer::import("API", "Model", "apps/appSessionManager");
importer::import("API", "Resources", "settingsManager");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Apps", "test/viewTester");
importer::import("DEV", "Apps", "test/sourceTester");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Apps", "appManifest");
importer::import("DEV", "Apps", "appSettings");
importer::import("DEV", "Apps", "views/appView");
importer::import("DEV", "Apps", "source/srcObject");
importer::import("DEV", "Apps", "source/srcPackage");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("AEL", "Literals", "appLiteral");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("BSS", "Market", "appMarket");

use \API\Model\core\manifests as coreManifests;
use \API\Model\apps\application as APIApplication;
use \API\Model\apps\appSessionManager;
use \API\Resources\settingsManager;
use \DEV\Apps\test\appTester;
use \DEV\Apps\test\viewTester;
use \DEV\Apps\test\sourceTester;
use \DEV\Apps\application as devApplication;
use \DEV\Apps\appManifest;
use \DEV\Apps\appSettings;
use \DEV\Apps\views\appView;
use \DEV\Apps\source\srcObject;
use \DEV\Apps\source\srcPackage;
use \DEV\Prototype\sourceMap;
use \AEL\Literals\appLiteral;
use \DEV\Projects\projectLibrary;
use \BSS\Market\appMarket;

/**
 * Application Manager
 * 
 * This is the application manager class.
 * It imports application source objects and load application views.
 * 
 * @version	6.0-2
 * @created	August 23, 2014, 14:53 (BST)
 * @updated	October 22, 2015, 15:13 (BST)
 */
class application
{
	/**
	 * The application view loading depth.
	 * 
	 * @type	integer
	 */
	private static $loadingDepth = 0;
	
	/**
	 * The application id currently running.
	 * 
	 * @type	integer
	 */
	private static $applicationID = NULL;
	
	/**
	 * Application protected packages according to manifest permissions.
	 * 
	 * @type	array
	 */
	private static $protectedPackages = NULL;
	
	/**
	 * Init the literal manager for the application that is currently running.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id currently running.
	 * 		
	 * 		NOTE: To application developers, this will be set only once the first time and cannot be changed after.
	 * 
	 * @return	integer
	 * 		The application id currently running.
	 */
	public static function init($applicationID = "")
	{
		// Set the application id if not set before
		if (empty(self::$applicationID) && !empty($applicationID))
			self::$applicationID = $applicationID;
	
		// Return the current application id (project id)
		return self::$applicationID;
	}
	
	/**
	 * Import an application source object
	 * 
	 * @param	string	$package
	 * 		The object's package name.
	 * 
	 * @param	string	$class
	 * 		The full name of the class (including namespaces separated by "/")
	 * 
	 * @return	void
	 */
	public static function import($package, $class = "")
	{
		// Load the entire package if class is empty
		if (empty($class))
			return self::importPackage($package);
		
		// Break classname
		$class = str_replace("::", "/", $class);
		$classParts = explode("/", $class);
		
		// Get Class Name
		$className = $classParts[count($classParts)-1];
		unset($classParts[count($classParts)-1]);
		
		// Get namespace
		$namespace = implode("/", $classParts);

		// Load the class from repository or from the published version
		if (appTester::publisherLock())
		{
			// Load from the latest published version
			$applicationPath = self::getApplicationPath();
			if (!$applicationPath)
				return FALSE;
				
			return importer::req($applicationPath."/".devApplication::SOURCE_FOLDER."/".srcPackage::LIB_NAME."/".$package."/".$namespace."/".$class.".php", $root = TRUE, $once = TRUE);
		}
		else
		{
			// Initialize source object
			$obj = new srcObject(self::$applicationID, $package, $namespace, $className);
			
			// Check if package is tester
			// If tester, get from trunk
			// Otherwise get from working branch
			if (sourceTester::status(self::$applicationID, $package))
				return $obj->loadFromTrunk();
			else
				return $obj->loadFromWBranch();
		}
	}
	
	/**
	 * Import an entire application source package.
	 * 
	 * @param	string	$package
	 * 		The application source package name.
	 * 
	 * @return	void
	 */
	private function importPackage($package)
	{
		// Load the package from repository or from the published version
		if (appTester::publisherLock())
		{
			// Load from the latest published version
			$applicationPath = self::getApplicationPath();
			if (!$applicationPath)
				return FALSE;
				
			$sm = new sourceMap(systemRoot.$applicationPath, $mapFile = "source.xml");
			$objects = $sm->getObjectList(srcPackage::LIB_NAME, $package);
			foreach ($objects as $object)
			{
				$namespace = str_replace("::", "/", $object["namespace"]);
				$objectPath = "/src/".srcPackage::LIB_NAME."/".$package."/".$namespace."/".$object["name"].".php";
				importer::incl($applicationPath."/".$objectPath, $root = TRUE, $once = TRUE);
			}
		}
		else
		{
			// Get package tester status
			$testerStatus = sourceTester::status(self::$applicationID, $package);
			
			// Load package objects
			$srcp = new srcPackage(self::$applicationID);
			$objects = $srcp->getObjects($package);
			foreach ($objects as $object)
			{
				$obj = new srcObject(self::$applicationID, $package, $object["namespace"], $object["name"]);
				if ($testerStatus)
					$obj->loadFromTrunk();
				else
					$obj->loadFromWBranch();
			}
		}
	}
	
	/**
	 * Load an application view content.
	 * 
	 * @param	string	$viewName
	 * 		The application view full name (include folders separated with "/").
	 * 		If empty, get the default/startup application view name.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		The application view content object.
	 * 		If can be a server report string or the DOMElement of the view.
	 */
	public static function loadView($viewName = "")
	{
		// Get view name
		// If view name is empty, get startup view from app settings
		if (empty($viewName))
		{
			// Get Settings
			$appSettings = self::getAppSettings();
			if ($appSettings)
				$viewName = $appSettings->get("STARTUP_VIEW");
		}
		
		// If view name is still empty, return FALSE
		if (empty($viewName))
			return FALSE;
		
		// Get view name and folder
		$viewName = trim($viewName, "/");
		$viewParts = explode("/", $viewName);
		if (count($viewParts) > 1)
		{
			$viewName = $viewParts[count($viewParts)-1];
			unset($viewParts[count($viewParts)-1]);
			$folderName = implode("/", $viewParts);
		}

		// Load the view from repository or from the published version
		if (appTester::publisherLock())
		{
			// Load from the latest published version
			$applicationPath = self::getApplicationPath();
			if (!$applicationPath)
				return FALSE;

			// Return view path
			$viewName = self::getViewName(self::$applicationID, $viewName);
			return importer::req($applicationPath."/".devApplication::VIEWS_FOLDER."/".$folderName."/".$viewName.".php", $root = TRUE, $once = TRUE);
		}
		else
		{
			// Load the view from the trunk or from the working branch
			$appView = new appView(self::$applicationID, $folderName, $viewName);
			if (viewTester::status(self::$applicationID, $folderName."/".$viewName))
				return $appView->loadFromTrunk();
			else
				return $appView->loadFromWBranch();
		}
	}
	
	/**
	 * Gets the settings manager for the application.
	 * Decides whether to load the settings from the developer folder or the published folder.
	 * 
	 * @return	settingsManager
	 * 		The appSettings or settingsManager object.
	 */
	private static function getAppSettings()
	{
		// Get the app settings from repository or from the published version
		if (appTester::publisherLock())
		{
			// Load from the latest published version
			$applicationPath = self::getApplicationPath();
			if (!$applicationPath)
				return FALSE;
			return new settingsManager($applicationPath, $fileName = "settings", $rootRelative = TRUE);
		}
		else
		{
			return new appSettings(self::$applicationID);
		}
	}
	
	/**
	 * Gets the application's library path to the latest version.
	 * 
	 * @return	string
	 * 		The application library path.
	 */
	private static function getApplicationPath()
	{
		// Get application id
		$applicationID = self::init();
		if (empty($applicationID))
			return NULL;
		
		// Get application version
		$appVersion = self::getApplicationVersion();
		
		// Get application path
		return APIApplication::getApplicationPath($applicationID, $appVersion);
	}
	
	/**
	 * Get application running version according to environment.
	 * 
	 * @return	string
	 * 		The application running version.
	 */
	private static function getApplicationVersion()
	{
		// Get application id
		$applicationID = self::init();
		if (empty($applicationID))
			return NULL;

		// Get team's or app's running version according to running environment
		if (self::onBOSS())
			$appVersion = appMarket::getTeamAppVersion($applicationID);
		else if (self::onAPC())
			$appVersion = appSessionManager::getInstance()->getVersion($applicationID);
		
		// Get last version if external request
		if (empty($appVersion))
			$appVersion = projectLibrary::getLastProjectVersion($applicationID);
		
		// Return version
		return $appVersion;
	}
	
	/**
	 * Check whether the application is running on the Application Center.
	 * 
	 * @return	boolean
	 * 		True if the application is on APC, false otherwise.
	 */
	public static function onAPC()
	{
		// Get subdomain where the application is running
		$subdomain = appTester::currentDomain();

		// Check if it is on Application Center (apps: compatibility)
		return (empty($subdomain) || $subdomain == "www" || $subdomain == "apps");
	}
	
	/**
	 * Check whether the application is running on the Development Environment.
	 * 
	 * @return	boolean
	 * 		True if the application is on DEV, false otherwise.
	 */
	public static function onDEV()
	{
		// Get subdomain where the application is running
		$subdomain = appTester::currentDomain();
		
		// Check if it is on Development Environment
		return ($subdomain == "developers");
	}
	
	/**
	 * Check whether the application is running on the Enterprise dashboard.
	 * 
	 * @return	boolean
	 * 		True if the application is running on enterprise dashboard, false otherwise.
	 */
	public static function onBOSS()
	{
		// Get subdomain where the application is running
		$subdomain = appTester::currentDomain();

		// Check if it is on BOSS (apps: compatibility)
		return (!empty($subdomain) && $subdomain != "www" && $subdomain != "developers" && $subdomain != "apps");
	}
	
	/**
	 * Get all protected packages from application's manifest file from production.
	 * 
	 * @return	void
	 */
	public static function getApplicationProtectedPackages()
	{
		// Check cache
		if (!empty(self::$protectedPackages))
			return self::$protectedPackages;
			
		// Initialize all protected packages
		$allProtectedPackages = array();
		
		// Load core manifests
		$coreManifests = coreManifests::getManifests();
		foreach ($coreManifests as $mfID => $mfInfo)
			foreach ($mfInfo['packages'] as $libraryName => $packages)
				foreach ($packages as $packageName)
					$allProtectedPackages[$mfID][$libraryName][$packageName] = 1;
		
		// Get file path (according to tester)
		$applicationID = self::init();
		if (appTester::publisherLock())
		{
			// Get manifests from application library
			$appVersion = self::getApplicationVersion();
			$appManifests = APIApplication::getApplicationManifests($applicationID, $appVersion);
		}
		else
		{
			// Get manifests from development
			$mf = new appManifest($applicationID);
			$appManifests = $mf->getPermissions();
		}
		
		// Scan all manifests and get packages
		self::$protectedPackages = array();
		foreach ($appManifests as $mfID)
			self::$protectedPackages = array_merge(self::$protectedPackages, $allProtectedPackages[$mfID]);

		return self::$protectedPackages;
	}
	
	/**
	 * Get the published view name.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$name
	 * 		The view name.
	 * 
	 * @return	string
	 * 		The published view name (no extensions included).
	 */
	public static function getViewName($appID, $name)
	{
		return "v".md5("view_".$appID."_".$name);
	}
	
	/**
	 * Increase the loading depth of app views by one.
	 * 
	 * @return	void
	 */
	public static function incLoadingDepth()
	{
		self::$loadingDepth++;
	}
	
	/**
	 * Decrease the loading depth of app views by one.
	 * 
	 * @return	void
	 */
	public static function decLoadingDepth()
	{
		self::$loadingDepth--;
	}
	
	/**
	 * Get the current app view's loading depth, starting from 0.
	 * 
	 * @return	integer
	 * 		The application view loading depth.
	 */
	public static function getLoadingDepth()
	{
		return self::$loadingDepth;
	}
	
	/**
	 * Get the application folder name inside the team's or account's directory.
	 * 
	 * @param	integer	$appID
	 * 		The application id to get the folder name for.
	 * 
	 * @return	string
	 * 		The application folder name.
	 */
	public static function getAppFolderName($appID)
	{
		return APIApplication::getAppFolderName($appID);
	}
}
//#section_end#
?>