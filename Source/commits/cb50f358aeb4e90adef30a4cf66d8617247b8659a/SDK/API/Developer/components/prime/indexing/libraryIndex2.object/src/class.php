<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime\indexing;

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
 * @namespace	\components\prime\indexing
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Library map index manager.
 * 
 * Creates and gets map index information for object libraries.
 * 
 * @version	{empty}
 * @created	January 13, 2014, 11:18 (EET)
 * @revised	January 13, 2014, 11:56 (EET)
 */
class libraryIndex2
{
	/**
	 * The default map file.
	 * 
	 * @type	string
	 */
	const MAP_FILE = "map.xml";
	
	/**
	 * Create a new library in the map.
	 * 
	 * @param	vcs	$vcs
	 * 		The vcs object with the repository loaded.
	 * 
	 * @param	string	$innerPath
	 * 		The inner repository folder path for the map file.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$fileName
	 * 		The map filename. The default name is the MAP_FILE constant.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createLibrary($vcs, $innerPath, $libName, $fileName = self::MAP_FILE)
	{
		// Create map file (if doesn't exist) or update it
		self::createMapFile($vcs, $path, $fileName);
		
		// Update index
		$itemID = self::getItemID($innerPath."_".$fileName);
		$filePath = $vcs->updateItem($itemID, TRUE);
		
		// Add library to map
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		$root = $parser->evaluate("//map")->item(0);
		
		$libraryEntry = $parser->create("library");
		$parser->attr($libraryEntry, "name", $libName);
		$parser->append($root, $libraryEntry);
		return $parser->update();
	}
	
	/**
	 * Create a new package in the map.
	 * 
	 * @param	vcs	$vcs
	 * 		The vcs object with the repository loaded.
	 * 
	 * @param	string	$innerPath
	 * 		The inner repository folder path for the map file.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$fileName
	 * 		The map filename. The default name is the MAP_FILE constant.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createPackage($vcs, $innerPath, $libName, $packageName, $fileName = self::MAP_FILE)
	{
		// Update index
		$itemID = self::getItemID($innerPath."_".$fileName);
		$filePath = $vcs->updateItem($itemID, TRUE);
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		$base = $parser->evaluate("//library[@name='".$libName."']")->item(0);
		
		// Search for same package
		$package = $parser->evaluate("//library[@name='".$libName."']/package[@name='$packageName']")->item(0);
		if (!is_null($package))
			return FALSE;
		
		$package = $parser->create("package", "", $packageName);
		$parser->attr($package, "name", $packageName);
		$parser->append($base, $package);
		return $parser->update();
	}
	
	/**
	 * Create a new namespace in the map.
	 * 
	 * @param	vcs	$vcs
	 * 		The vcs object with the repository loaded.
	 * 
	 * @param	string	$innerPath
	 * 		The inner repository folder path for the map file.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The namespace name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace name (separated by "::", if any).
	 * 
	 * @param	{type}	$fileName
	 * 		The map filename. The default name is the MAP_FILE constant.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createNamespace($vcs, $innerPath, $libName, $packageName, $nsName, $parentNs = "", $fileName = self::MAP_FILE)
	{
		// Update index
		$itemID = self::getItemID($innerPath."_".$fileName);
		$filePath = $vcs->updateItem($itemID, TRUE);
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Search for package
		$package = $parser->evaluate("//library[@name='".$libName."']/package[@name='$packageName']")->item(0);
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
			$parent = $parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$packageName'.");
		}
		
		// Search for same namespace
		$namespace = $parser->evaluate("namespace[@name='$nsName']", $parent)->item(0);
		if (!is_null($namespace))
			return FALSE;
			
		$namespace = $parser->create("namespace");
		$parser->attr($namespace, "name", $nsName);
		$parser->append($parent, $namespace);
		return $parser->update();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$vcs
	 * 		{description}
	 * 
	 * @param	{type}	$innerPath
	 * 		{description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$fileName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function createObject($vcs, $innerPath, $libName, $packageName, $nsName, $objectName, $title = "", $fileName = self::MAP_FILE)
	{
		// Update index
		$itemID = self::getItemID($innerPath."_".$fileName);
		$filePath = $vcs->updateItem($itemID, TRUE);
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Search for package
		$package = $parser->evaluate("//library[@name='".$libName."']/package[@name='$packageName']")->item(0);
		if (is_null($package))
			return FALSE;
		
		// If not namespace given, get package as parent
		if ($nsName == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $nsName);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				return FALSE;
		}
			
		// Search for same object
		$object = $parser->evaluate("object[@name='$objectName']", $parent)->item(0);
		
		if (!is_null($object))
			return FALSE;

		$object = $parser->create("object");
		$parser->attr($object, "name", $objectName);
		$parser->attr($object, "title", $title);
		$parser->append($parent, $object);
		return $parser->update();
	}
	
	/**
	 * Gets the map index file path from the repository.
	 * 
	 * @param	vcs	$vcs
	 * 		The vcs object with the repository loaded.
	 * 
	 * @param	string	$innerPath
	 * 		The inner repository folder path for the map file.
	 * 
	 * @param	string	$fileName
	 * 		The map filename. The default name is the MAP_FILE constant.
	 * 
	 * @return	string
	 * 		The map index file path.
	 */
	public static function getMapFilePath($vcs, $innerPath, $fileName = self::MAP_FILE)
	{
		$itemID = self::getItemID($innerPath."_".$fileName);
		return $vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Creates the map index file.
	 * 
	 * @param	vcs	$vcs
	 * 		The vcs object with the repository loaded.
	 * 
	 * @param	string	$innerPath
	 * 		The inner repository folder path for the map file.
	 * 
	 * @param	string	$fileName
	 * 		The map filename. The default name is the MAP_FILE constant.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private static function createMapFile($vcs, $innerPath, $fileName = self::MAP_FILE)
	{
		// Get item ID
		$itemID = self::getItemID($innerPath."_".$fileName);
		$itemPath = $innerPath."/".$fileName;
		$itemTrunkPath = $vcs->createItem($itemID, $itemPath, $fileName, $isFolder = FALSE);
		
		// If file exists, return TRUE
		if (file_exists($itemTrunkPath))
			return TRUE;
		
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("map");
		$parser->append($root);
		fileManager::create($itemTrunkPath, "", TRUE);
		return $parser->save($itemTrunkPath, "", TRUE);
	}
	
	/**
	 * Gets the vcs repository item id.
	 * 
	 * @param	string	$seed
	 * 		The seed for the hash value.
	 * 
	 * @return	string
	 * 		The item id.
	 */
	private static function getItemID($seed)
	{
		return "m".md5("map_".$seed);
	}
	
	/**
	 * Gets all libraries in the map.
	 * 
	 * @param	string	$mapFilePath
	 * 		The map file path.
	 * 
	 * @return	array
	 * 		An array of library names by key and value.
	 */
	public static function getLibraryList($mapFilePath)
	{
		// Load Library File
		$parser = new DOMParser();
		$libArray = array();
		try
		{
			$parser->load($mapFilePath, FALSE);
		}
		catch (Exception $ex)
		{
			return $libArray;
		}
		
		$libraries = $parser->evaluate("//library");
		foreach ($libraries as $lib)
			$libArray[$parser->attr($lib, "name")] = $parser->attr($lib, "name");

		return $libArray;
	}
	
	/**
	 * Get all packages from the map file.
	 * 
	 * @param	string	$mapFilePath
	 * 		The map index file path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of package names by key and value.
	 */
	public static function getPackageList($mapFilePath, $libName)
	{
		$parser = new DOMParser();
		
		// Load Library File
		$filePath = $innerPath."/".$fileName;
		$parser->load($filePath, true);
		
		$packages = $parser->evaluate("//library[@name='".$libName."']/package");
		$pkgArray = array();
		foreach ($packages as $pkg)
			$pkgArray[$parser->attr($pkg, "name")] = $parser->attr($pkg, "name");

		return $pkgArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$mapFilePath
	 * 		{description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getNSList($mapFilePath, $libName, $packageName, $parentNs = "")
	{
		// Load Library File
		$parser = new DOMParser();
		$parser->load($mapFilePath, FALSE);
		
		// Get Package
		$package = $parser->evaluate("//library[@name='".$libName."']/package[@name='$packageName']")->item(0);
		if ($parentNs == "")
			$parent = $package;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $parentNs);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss, $package)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$packageName'.");
		}
		
		// Get Children namespaces
		$namespaces = $parser->evaluate("namespace", $parent);
		
		// Create array
		$nsArray = array();
		foreach ($namespaces as $ns)
		{
			$nsName = $parser->attr($ns, "name");
			$tempParent = ($parentNs == "" ? "" : $parentNs."::").$parser->attr($ns, "name");
			$nsArray[$nsName] = self::getNSList($mapFilePath, $libName, $packageName, $tempParent);
		}
		
		return $nsArray;
	}
	
	/**
	 * Get all objects in a library.
	 * 
	 * @param	string	$mapFilePath
	 * 		The map index file path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	array
	 * 		An array of objects.
	 * 		It contains an array for each object with the values: title, name, package, namespace.
	 */
	public static function getObjectList($mapFilePath, $libName, $packageName = "", $parentNs = "")
	{
		// Load Library index file
		$parser = new DOMParser();
		try
		{
			$parser->load($mapFilePath, FALSE);
		}
		catch (Exception $ex)
		{
			return NULL;
		}
		
		// Get Objects
		$libraryObjects = array();
		if (empty($packageName) && empty($parentNs))
			$objects = $parser->evaluate("//library[@name='".$libName."']/*/object");
		else if (empty($parentNs))
			$objects = $parser->evaluate("//library[@name='".$libName."']/package[@name='".$packageName."']/*/object");
		else
		{
			$nss = explode("::", $parentNs);
			$q_nss = "//library[@name='".$libName."']/package[@name='".$packageName."']/namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$packageName'.");
			$objects = $parser->evaluate("object", $parent);
		}
		
		// Get objects
		foreach ($objects as $obj)
		{
			// Build Object
			$info = array();
			$info['title'] = $parser->attr($obj, "title");
			$info['name'] = $parser->attr($obj, "name");
			
			// Set library
			$info['library'] = $libName;
			
			// Get Namespace
			$nsArray = array();
			$nsArray[] = $info['name'];
			$parentNode = $obj->parentNode;
			while ($parentNode->tagName == "namespace")
			{
				$nsArray[] = $parser->attr($parentNode, "name");
				$parentNode = $parentNode->parentNode;
			}
			
			// Set namespace
			$nsArray = array_reverse($nsArray);
			unset($nsArray[count($nsArray)-1]);
			$namespace = implode("::", $nsArray);
			$info['namespace'] = $namespace;
			
			// Get package (if any)
			if ($parentNode->tagName == "package")
				$info['package'] = $parser->attr($parentNode, "name");
			
			// Add info
			$libraryObjects[] = $info;
		}

		return $libraryObjects;
	}
}
//#section_end#
?>