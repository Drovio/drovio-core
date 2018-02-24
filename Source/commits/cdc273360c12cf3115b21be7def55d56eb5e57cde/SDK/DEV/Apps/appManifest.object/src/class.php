<?php
//#section#[header]
// Namespace
namespace DEV\Apps;

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
 * @package	Apps
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Apps", "application");

use \API\Resources\DOMParser;
use \DEV\Apps\application;

/**
 * Application Manifest Manager
 * 
 * Manages the application manifest file.
 * It sets application's permissions for the SDK and other important settings that the application needs to work.
 * 
 * @version	0.1-1
 * @created	February 25, 2015, 18:35 (EET)
 * @updated	February 25, 2015, 18:35 (EET)
 */
class appManifest
{
	/**
	 * The manifest index file.
	 * 
	 * @type	string
	 */
	const MANIFEST_FILE = "appManifest.xml";
	
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $app;
	
	/**
	 * Initialize the manifest manager.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public function __construct($appID)
	{
		// Init developer application object
		$this->app = new application($appID);
		
		// Create application manifest file
		$this->create();
	}
	
	/**
	 * Get all application protected permissions regarding the SDK usage.
	 * 
	 * @return	array
	 * 		An array of all permission package ids.
	 */
	public function getPermissions()
	{
		// Initialize
		$appPermissions = array();
		$indexFile = $this->app->getRootFolder()."/".self::MANIFEST_FILE;
		
		// Load application privileges file
		$parser = new DOMParser();
		$parser->load($indexFile);
		
		// Get all application permissions
		$permissions = $parser->evaluate("/manifest/permissions/perm");
		foreach ($permissions as $prm)
			$appPermissions[] = $parser->attr($prm, "id");
		
		return $appPermissions;
	}
	
	/**
	 * Set the application permission packages.
	 * 
	 * @param	array	$permissions
	 * 		An array of all permission package ids to set for the application.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setPermissions($permissions)
	{
		// Initialize
		$indexFile = $this->app->getRootFolder()."/".self::MANIFEST_FILE;
		
		// Load application privileges file
		$parser = new DOMParser();
		$parser->load($indexFile);
		$root = $parser->evaluate("/manifest/permissions")->item(0);
		$parser->innerHTML($root, "");
		
		// Set all application permissions
		foreach ($permissions as $prm)
		{
			$prmElement = $parser->create("perm", "", $prm);
			$parser->append($root, $prmElement);
		}
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Creates the manifest index file for the first time.
	 * 
	 * @return	void
	 */
	private function create()
	{
		// Initialize index file
		$indexFile = $this->app->getRootFolder()."/".self::MANIFEST_FILE;
		
		// Check if file already exists
		if (file_exists(systemRoot.$indexFile))
			return;
		
		// Create file
		fileManager::create(systemRoot.$indexFile, "", TRUE);
		
		// Create Application index root
		$parser = new DOMParser();
		$root = $parser->create("manifest");
		$parser->append($root);
		return $parser->save(systemRoot.$indexFile, FALSE);
	}
}
//#section_end#
?>