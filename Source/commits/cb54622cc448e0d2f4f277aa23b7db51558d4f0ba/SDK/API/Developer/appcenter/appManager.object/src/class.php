<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\appcenter
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "ServiceManager");
importer::import("API", "Security", "account");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "DOMParser");

use \API\Resources\DOMParser;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\versionControl\vcsManager;
use \API\Developer\profiler\tester;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;
use \API\Profile\ServiceManager;
use \API\Security\account;
use \API\Resources\storage\session;

/**
 * Application Manager
 * 
 * Manager for the user's application folder.
 * 
 * @version	{empty}
 * @created	June 4, 2013, 13:06 (EEST)
 * @revised	April 6, 2014, 21:24 (EEST)
 * 
 * @deprecated	Use \DEV\Apps\appManager instead.
 */
class appManager
{
	/**
	 * The developer's application root folder.
	 * 
	 * @type	string
	 */
	private $devRootFolder;
	
	/**
	 * Constructor Method. Initializes the Developer's service.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize Service
		$pServices = new ServiceManager("Developer");
		$this->devRootFolder = $pServices->getAccountFolder("Developer")."/Applications/";
	}
	
	/**
	 * Gets the application's folder inside the user's folder.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application folder path.
	 */
	public function getDevAppFolder($appID)
	{
		return $this->devRootFolder."app".$appID.".app/";
	}
	
	/**
	 * Gets the application's publish folder.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application publish folder path.
	 */
	public static function getPublishedAppFolder($appID)
	{
		return paths::getPublishedPath()."/Applications/app".md5($appID).".app/";
	}
	
	/**
	 * Opens an application for editing (session storage).
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public static function openApplication($appID)
	{
		// Get open applications
		$appSession = session::get("applications", NULL, "appCenter");
		if (is_null($appSession))
			$appSession = array();
		
		// Add application to session holder
		$appSession[$appID] = TRUE;
		session::set("applications", $appSession, "appCenter");
	}
	
	/**
	 * Closes an open application (remove from session).
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public static function closeApplication($appID)
	{
		// Remove application from session holder
		$appSession = session::get("applications", NULL, "appCenter");
		unset($appSession[$appID]);
	}
	
	/**
	 * Checks whether the application is open.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True if application is open, false otherwise.
	 */
	private static function validateApplication($appID)
	{
		// Get open applications
		$appSession = session::get("applications", NULL, "appCenter");
		
		// See if application exists and is valid
		return (isset($appSession[$appID]) && $appSession[$appID]);
	}
	
	/**
	 * Validates and returns information of a given application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	array
	 * 		An array of application data, as fetched from database.
	 */
	public static function getApplicationData($appID)
	{
		// Validate application
		if (!self::validateApplication($appID))
			return NULL;
			
		// Load application data
		$dbc = new interDbConnection();
		$q = new dbQuery("1622380717", "apps");
		$attr = array();
		$attr['appID'] = $appID;
		$attr['accountID'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		$applicationData = $dbc->fetch($result);
		return $applicationData;
	}
	
	/**
	 * Updates application's information.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$fullName
	 * 		The full name of the application.
	 * 
	 * @param	string	$privacy
	 * 		The scope identifier.
	 * 		private | public
	 * 
	 * @param	string	$tags
	 * 		The application tags separated by space.
	 * 
	 * @param	string	$name
	 * 		The unique name identifier of the application.
	 * 
	 * @param	string	$description
	 * 		The application description
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateApplicationData($appID, $fullName, $privacy, $tags = "", $name = "", $description = "")
	{
		// Validate application
		if (!self::validateApplication($appID))
			return NULL;
			
		// Load application data
		$dbc = new interDbConnection();
		$q = new dbQuery("1262332867", "apps");
		$attr = array();
		$attr['id'] = $appID;
		$attr['fullName'] = $fullName;
		$attr['name'] = $name;
		$attr['scope'] = $privacy;
		$attr['tags'] = $tags;
		$attr['description'] = $description;
		return $result = $dbc->execute($q, $attr);
	}
	
	/**
	 * Gets the redback's shared library list.
	 * 
	 * @return	array
	 * 		The shared library in a $library => $object array.
	 */
	public static function getSharedLibraryList()
	{
		$libs = array();
		$parser = new DOMParser();
		try
		{
			$parser->load("/System/Resources/appCenter/core.xml");
		}
		catch (Exception $ex)
		{
			return $libs;
		}

		// Get objects
		$objects = $parser->evaluate("//object");
		foreach ($objects as $object)
		{
			// Get parents
			$package = $object->parentNode;
			$library = $package->parentNode;
			
			// Set names
			$libraryName = $parser->attr($library, "name");
			$packageName = $parser->attr($package, "name");
			$objectName = $parser->attr($object, "name");
			
			$libs[$libraryName][] = $packageName.'::'.$objectName;
		}
		
		return $libs;
	}
}
//#section_end#
?>