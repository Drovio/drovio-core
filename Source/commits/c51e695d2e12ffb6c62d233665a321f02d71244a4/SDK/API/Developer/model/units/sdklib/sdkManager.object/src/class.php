<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\sdklib;

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
 * @namespace	\model\units\sdklib
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "content::resources::mapping");
importer::import("API", "Developer", "model::units::sdklib::sdkObject");
importer::import("API", "Developer", "model::version::vcs");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\content\resources\mapping;
use \API\Developer\model\units\sdklib\sdkObject;
use \API\Developer\model\version\vcs;

/**
 * SDK Library Manager
 * 
 * Handles all the SDK libraries, packages and objects.
 * 
 * @version	{empty}
 * @created	July 3, 2013, 12:57 (EEST)
 * @revised	July 3, 2013, 12:57 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\sdkManager instead.
 */
class sdkManager extends vcs
{
	/**
	 * The repository path for the SDK
	 * 
	 * @type	string
	 */
	const REPOSITORY_PATH = "/Developer/Repositories/Library/SDK/";
	/**
	 * The production path for the SDK
	 * 
	 * @type	string
	 */
	const PATH = "/System/Library/SDK/";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * Create an SDK Library
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function create_library($libName)
	{
		// Create Developer Index 
		$proceed = mapping::create_library($libName);
		
		// If library already exists, abort
		if (!$proceed)
			return FALSE;
			
		// Repository Library Path
		folderManager::create(systemRoot.self::REPOSITORY_PATH, $libName);
		
		// Production Library Path
		folderManager::create(systemRoot.self::PATH, $libName);
		
		// Create Library Index
		self::createLibraryIndex($libName);
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function createLibraryIndex($libName)
	{
		// Library Path
		$libPath = systemRoot.self::PATH."/".$libName."/";
		
		// Create Library Index
		$parser = new DOMParser();
		$root = $parser->create("Library");
		$parser->attr($root, "name", $libName);
		$parser->append($root);
		return $parser->save($libPath, self::INDEX_FILE, TRUE);
	}
	
	/**
	 * Create a library package
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @param	string	$packageName
	 * 		The package name
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create_package($libName, $packageName)
	{
		// Create Index 
		$proceed = mapping::create_package($libName, $packageName);
		
		// If package already exists, abort
		if (!$proceed)
			return FALSE;
			
		// Repository Package Library Path
		folderManager::create(systemRoot.self::REPOSITORY_PATH.$libName."/", $packageName);
			
		// Initialize VCS
		$this->name = $packageName;
		$this->VCS_initialize("/Library/SDK/".$libName."/".$packageName."/", $this->name, sdkObject::FILE_TYPE);
		
		// Create vcs repository structure
		$this->VCS_create_structure();
		
		// Production Package Library Path
		folderManager::create(systemRoot.self::PATH.$libName."/", $packageName);
		
		// Create Package index and library entry
		self::createPackageIndex($libName, $packageName);
		self::addPackageEntry($libName, $packageName);
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function createPackageIndex($libName, $packageName)
	{
		// Library Path
		$packagePath = systemRoot.self::PATH."/".$libName."/".$packageName."/";
		
		// Create Library Index
		$parser = new DOMParser();
		$root = $parser->create("Package");
		$parser->attr($root, "name", $packageName);
		$parser->append($root);
		return $parser->save($packagePath, self::INDEX_FILE, TRUE);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function addPackageEntry($libName, $packageName)
	{
		// Library Path
		$indexFilePath = self::PATH."/".$libName."/";
		
		// Get Library Root Index
		$parser = new DOMParser();
		$parser->load($indexFilePath.self::INDEX_FILE);
		$root = $parser->evaluate("//Library")->item(0);
		
		// Create Package Entry
		$package = $parser->create("package");
		$parser->attr($package, "name", $packageName);
		$parser->append($root, $package);
		
		// Save File
		return $parser->save(systemRoot.$indexFilePath, self::INDEX_FILE, TRUE);
	}
	
	/**
	 * Create a package namespace
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @param	string	$packageName
	 * 		The package name
	 * 
	 * @param	string	$nsName
	 * 		The namespace name
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (multiple namespaces separated by "::")
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create_namespace($libName, $packageName, $nsName, $parentNs = "")
	{
		// Create Index 
		$proceed = mapping::create_namespace($libName, $packageName, $nsName, $parentNs);
		
		// If package already exists, abort
		if (!$proceed)
			return FALSE;

		// Repository Package Library Path
		$parentNs = str_replace("::", "/", $parentNs);
		folderManager::create(systemRoot.self::REPOSITORY_PATH.$libName."/".$packageName."/".$parentNs."/", $nsName);
		
		$this->name = $nsName;
		$this->VCS_initialize("/Library/SDK/".$libName."/".$packageName."/".$parentNs."/".$nsName."/", $this->name, sdkObject::FILE_TYPE);
		
		// Create vcs repository structure
		$this->VCS_create_structure();
		
		// Production Package Library Path
		folderManager::create(systemRoot.self::PATH.$libName."/".$packageName."/".$parentNs."/", $nsName);
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$namespace
	 * 		{description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function addObjectEntry($libName, $packageName, $namespace, $objectName)
	{
		// Package Path
		$indexFilePath = self::PATH."/".$libName."/".$packageName."/";
		
		// Get Library Root Index
		$parser = new DOMParser();
		$parser->load($indexFilePath.self::INDEX_FILE);
		$root = $parser->evaluate("//Package")->item(0);
		
		// Create Package Entry
		$objectFullName = ($namespace == "" ? "" : $namespace."::").$objectName;
		$objectEntry = $parser->create("object");
		$parser->attr($objectEntry, "name", $objectFullName);
		$parser->append($root, $objectEntry);
		
		// Save File
		return $parser->save(systemRoot.$indexFilePath, self::INDEX_FILE, TRUE);
	}
}
//#section_end#
?>