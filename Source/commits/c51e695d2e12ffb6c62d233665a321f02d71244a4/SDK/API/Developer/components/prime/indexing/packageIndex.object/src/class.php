<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime\indexing;

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
 * @namespace	\components\prime\indexing
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Library package index manager.
 * 
 * Manages all package indexing.
 * 
 * @version	{empty}
 * @created	July 23, 2013, 13:55 (EEST)
 * @revised	July 23, 2013, 13:55 (EEST)
 */
class packageIndex
{
	/**
	 * The index filename.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	/**
	 * Creates the package index.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createMapIndex($path, $libName, $packageName)
	{
		// Library Path
		$libPath = $path."/".$libName.".xml";
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($libPath, TRUE);
		$base = $parser->evaluate("//Library")->item(0);
		
		// Search for same package
		$package = $parser->evaluate("//package[@id='$packageName']")->item(0);
		
		if (!is_null($package))
			return FALSE;
		
		$package = $parser->create("package", "", $packageName);
		$parser->append($base, $package);
		return $parser->save(systemRoot.$libPath, "", TRUE);
	}
	
	/**
	 * Creates the package release index.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createReleaseIndex($path, $libName, $packageName)
	{
		// Library Path
		$packagePath = $path."/".$libName."/".$packageName."/";
		
		// Create Library Index
		$parser = new DOMParser();
		$root = $parser->create("Package");
		$parser->attr($root, "name", $packageName);
		$parser->append($root);
		return $parser->save($packagePath, self::INDEX_FILE, TRUE);
	}
	
	/**
	 * Adds a package entry to the library index file.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function addLibraryEntry($path, $libName, $packageName)
	{
		// Library Path
		$indexFilePath = $path."/".$libName."/";
		
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
	 * Creates a namespace index.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The namespace.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (if any, separated by "::").
	 * 
	 * @return	void
	 */
	public static function createNSIndex($path, $libName, $packageName, $nsName, $parentNs = "")
	{
		// Library Path
		$libPath = $path."/".$libName.".xml";
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($libPath, TRUE);
		
		// Search for package
		$package = $parser->evaluate("//package[@id='$packageName']")->item(0);
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
		return $parser->save(systemRoot.$libPath, "", TRUE);
	}
	
	/**
	 * Returns all namespaces in a given package.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (if any, separated by "::").
	 * 
	 * @return	array
	 * 		A nested array of all namespaces.
	 */
	public static function getNSList($path, $libName, $packageName, $parentNs = "")
	{
		// Load Library File
		$parser = new DOMParser();
		$libPath = $path."/".$libName.".xml";
		$parser->load($libPath, true);
		
		// Get Package
		$package = $parser->evaluate("//package[@id='$packageName']")->item(0);
		
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
			$nsArray[$nsName] = self::getNSList($path, $libName, $packageName, $tempParent);
		}
		
		return $nsArray;
	}
	
	/**
	 * Gets all objects in a given package.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (if any, separated by "::").
	 * 
	 * @return	array
	 * 		Returns an array of objects (including their info [title, name, lib, pkg, ns]).
	 */
	public static function getPackageObjects($path, $libName, $packageName, $parentNs = "")
	{
		$parentNs = str_replace("_", "::", $parentNs);
		
		// Load Library File
		$parser = new DOMParser();
		$libPath = $path."/".$libName.".xml";
		$parser->load($libPath, true);
		
		// Set Query
		if ($parentNs == "")
			$xpathQuery = "//*[@id='$packageName']//object";
		else
		{
			$nss = explode("::", $parentNs);
			$xpathQuery = "namespace[@name='".implode("']/namespace[@name='", $nss)."']//object";
		}
		
		// Get Library Objects
		$objects = $parser->evaluate($xpathQuery);
		
		$info = array();
		foreach ($objects as $o)
		{
			// Build Object
			$oName = $parser->attr($o, "name");
			
			$opts = array();
			$opts['title'] = $parser->attr($o, "title");
			$opts['name'] = $oName;
			
			// Get Namespace
			$namespace = "";//$initName;
			$parent_namespace = $o->parentNode;
			while ($parent_namespace->tagName == "namespace")
			{
				$namespace = $parser->attr($parent_namespace, "name").($namespace == "" ? "" : "::".$namespace);
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
	 * Gets all objects in a given namespace.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (if any, separated by "::").
	 * 
	 * @return	array
	 * 		Returns an array of objects (including their info [title, name, import(namespace)]).
	 */
	public static function getNSObjects($path, $libName, $packageName, $parentNs = "")
	{
		$parentNs = str_replace("_", "::", $parentNs);
		
		// Load Library File
		$parser = new DOMParser();
		$libPath = $path."/".$libName.".xml";
		$parser->load($libPath, true);
		
		// Get Package
		$package = $parser->find($packageName);
		
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

		// Get Library Objects
		$objects = $parser->evaluate("object", $parent);

		$info = array();
		foreach ($objects as $o)
		{
			// Build Object
			$initName = $parser->attr($o, "name");
			
			$opts = array();
			$opts['title'] = $parser->attr($o, "title");
			$opts['name'] = $initName;
			
			// Get Namespace
			$namespace = $initName;
			$parent_namespace = $o->parentNode;
			while ($parent_namespace->tagName == "namespace")
			{
				$namespace = $parser->attr($parent_namespace, "name")."::".$namespace;
				$parent_namespace = $parent_namespace->parentNode;
			}
			// Set Package Namespace
			$opts['import'] = $namespace;
			
			$ns = explode("::", $namespace);
			unset($ns[count($ns)-1]);
			$namespace = implode("::", $ns);
			$opts['namespace'] = $namespace;
			
			// Set Object
			$info[$initName] = $opts;
		}

		return $info;
	}
	
	/**
	 * Gets all released objects in a given package.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	array
	 * 		Returns an array of object names.
	 */
	public static function getReleasePackageObjects($path, $libName, $packageName)
	{
		// Load Index file
		$oParser = new DOMParser();
		$indexFilePath = $path."/".$libName."/".$packageName."/";
		$oParser->load($indexFilePath.self::INDEX_FILE);
		$objects = $oParser->evaluate("//object/@name");
		
		$objectsArray = array();
		foreach ($objects as $o)
			$objectsArray[] = $o->nodeValue;

		return $objectsArray;
	}
	
	/**
	 * Adds an object entry to the release package index.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace.
	 * 
	 * @param	string	$objectName
	 * 		The parent namespace (if any, separated by "::").
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function addObjectReleaseEntry($path, $libName, $packageName, $namespace, $objectName)
	{
		// Package Path
		$indexFilePath = $path."/".$libName."/".$packageName."/";
		
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