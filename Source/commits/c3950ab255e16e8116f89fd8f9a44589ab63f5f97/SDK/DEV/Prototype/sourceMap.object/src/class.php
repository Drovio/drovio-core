<?php
//#section#[header]
// Namespace
namespace DEV\Prototype;

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
 * @package	Prototype
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Security", "accessControl");
importer::import("DEV", "Version", "vcs");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Security\accessControl;
use \DEV\Version\vcs;

/**
 * Source object map index manager.
 * 
 * Creates a map for the project's source including libraries, packages, namespaces and the classes.
 * 
 * @version	{empty}
 * @created	March 31, 2014, 12:47 (EEST)
 * @revised	May 27, 2014, 9:44 (EEST)
 */
class sourceMap
{
	/**
	 * The default map file name.
	 * 
	 * @type	string
	 */
	const MAP_FILE = "map.xml";
	
	/**
	 * The folder path of the file.
	 * 
	 * @type	string
	 */
	private $folderPath;
	
	/**
	 * The map file name.
	 * 
	 * @type	string
	 */
	private $mapFile;
	
	/**
	 * Constructor method for object initialization.
	 * 
	 * @param	string	$folderPath
	 * 		The folder path for the map file.
	 * 
	 * @param	string	$mapFile
	 * 		The map file name. By default the MAP_FILE constant is used.
	 * 
	 * @return	void
	 */
	public function __construct($folderPath, $mapFile = self::MAP_FILE)
	{
		$this->folderPath = $folderPath;
		$this->mapFile = $mapFile;
	}
	
	/**
	 * Create the map file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createMapFile()
	{
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// If file exists, return TRUE
		if (file_exists($filePath))
			return TRUE;
		
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("map");
		$parser->append($root);
		fileManager::create($filePath, "", TRUE);
		return $parser->save($filePath, "", TRUE);
	}
	
	/**
	 * Create a new library in the map.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createLibrary($library)
	{
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		$root = $parser->evaluate("//map")->item(0);
		
		// Add library to index
		$libraryEntry = $parser->create("library");
		$parser->attr($libraryEntry, "name", $library);
		$parser->append($root, $libraryEntry);
		return $parser->update();
	}
	
	/**
	 * Delete an empty library entry from the source map file.
	 * Only internal calls allowed!
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false if library is not empty.
	 * 
	 * @throws	Exception
	 */
	public function deleteLibrary($library)
	{
		// Check api functionality
		if (!accessControl::internalCall())
			throw new Exception("Only internal calls are allowed to delete a library!");
			
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		$libraryEntry = $parser->evaluate("//library[@name='".$library."']")->item(0);
		if (is_null($libraryEntry))
			throw new Exception("Library '$library' doesn't exist in '$filePath'!");
			
		// Check if library has children and return FALSE
		if ($libraryEntry->childNodes->length > 0)
			return FALSE;
		
		// Remove library from index
		$parser->replace($libraryEntry, NULL);
		return $parser->update();
	}
	
	/**
	 * Create a new package in the given library in the map.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createPackage($library, $package)
	{
		// Update index
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		$base = $parser->evaluate("//library[@name='".$library."']")->item(0);
		
		// Search for same package
		$packageEntry = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']")->item(0);
		if (!is_null($packageEntry))
			return FALSE;
		
		$packageEntry = $parser->create("package");
		$parser->attr($packageEntry, "name", $package);
		$parser->append($base, $packageEntry);
		return $parser->update();
	}
	
	/**
	 * Deletes an empty package from a given library in the source map file.
	 * Only internal calls allowed!
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name to delete.
	 * 
	 * @return	boolean
	 * 		True on success, false if package is not empty.
	 * 
	 * @throws	Exception
	 */
	public function deletePackage($library, $package)
	{
		// Check api functionality
		if (!accessControl::internalCall())
			throw new Exception("Only internal calls are allowed to delete a library!");
			
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		$packageEntry = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']")->item(0);
		if (is_null($packageEntry))
			throw new Exception("Package '$package' doesn't exist in Library '$library'!");
			
		// Check if package has children and return FALSE
		if ($packageEntry->childNodes->length > 0)
			return FALSE;
		
		// Remove package from index
		$parser->replace($packageEntry, NULL);
		return $parser->update();
	}
	
	/**
	 * Create a new namespace in the given package in the map.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createNamespace($library, $package, $namespace, $parentNs = "")
	{
		// Update index
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Search for package
		$packageEntry = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']")->item(0);
		if (is_null($packageEntry))
			throw new Exception("Package '$package' doesn't exist inside Library '$library'.");
		
		// Get parent namespace
		if ($parentNs == "")
			$parent = $packageEntry;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $parentNs);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss, $packageEntry)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$package'.");
		}
		
		// Search for same namespace
		$namespaceEntry = $parser->evaluate("namespace[@name='".$namespace."']", $parent)->item(0);
		if (!is_null($namespaceEntry))
			return FALSE;
			
		$namespaceEntry = $parser->create("namespace");
		$parser->attr($namespaceEntry, "name", $namespace);
		$parser->append($parent, $namespaceEntry);
		return $parser->update();
	}
	
	/**
	 * Deletes an empty namespace from a given package in the source map file.
	 * Only internal calls allowed!
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace (separated by "::", if any).
	 * 
	 * @return	boolean
	 * 		True on success, false if namespace is not empty.
	 * 
	 * @throws	Exception
	 */
	public function deleteNamespace($library, $package, $namespace)
	{
		// Check api functionality
		if (!accessControl::internalCall())
			throw new Exception("Only internal calls are allowed to delete a library!");
			
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Get namespace entry
		$nss = explode("::", $namespace);
		$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
		$nsEntry = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']/".$q_nss)->item(0);
		if (is_null($nsEntry))
			throw new Exception("Namespace '$namespace' doesn't exist in Package '$library -> $package'!");
			
		// Check if namespace has children and return FALSE
		if ($nsEntry->childNodes->length > 0)
			return FALSE;
		
		// Remove namespace from index
		$parser->replace($nsEntry, NULL);
		return $parser->update();
	}
	
	/**
	 * Create a new object in the map.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace (separated by "::", if any).
	 * 
	 * @param	string	$name
	 * 		The object name.
	 * 
	 * @param	string	$title
	 * 		The object title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createObject($library, $package, $namespace, $name, $title = "")
	{
		// Update index
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Search for package
		$packageEntry = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']")->item(0);
		if (is_null($packageEntry))
			return FALSE;
		
		// If not namespace given, get package as parent
		if (empty($namespace))
			$parent = $packageEntry;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $namespace);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss, $packageEntry)->item(0);
			if (is_null($parent))
				return FALSE;
		}
			
		// Search for same object
		$object = $parser->evaluate("object[@name='".$name."']", $parent)->item(0);
		
		if (!is_null($object))
			return FALSE;

		$object = $parser->create("object");
		$parser->attr($object, "name", $name);
		$parser->attr($object, "title", $title);
		$parser->append($parent, $object);
		return $parser->update();
	}
	
	/**
	 * Deletes an object from a given package in the source map file.
	 * Only internal calls allowed!
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$object
	 * 		The object (separated by "::" from the namespace, if any).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @throws	Exception
	 */
	public function deleteObject($library, $package, $object)
	{
		// Check api functionality
		if (!accessControl::internalCall())
			throw new Exception("Only internal calls are allowed to delete a library!");
			
		// Get index path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Get namespace entry
		$objs = explode("::", $object);
		$q = "//library[@name='".$library."']/package[@name='".$package."']/";
		if (count($objs) > 1)
		{
			$nss = array_slice($objs, 0, count($objs)-1);
			$objectName = $objs[count($objs)-1];
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$q .= $q_nss."/object[@name='".$objectName."']";
		}
		else
		{
			$objectName = $objs[count($objs)-1];
			$q .= "object[@name='".$objectName."']";
		}

		$objectEntry = $parser->evaluate($q)->item(0);
		if (is_null($objectEntry))
			throw new Exception("Object '$object' doesn't exist in Package '$library -> $package'!");
		
		// Remove object from index
		$parser->replace($objectEntry, NULL);
		return $parser->update();
	}
	
	/**
	 * Gets all libraries in the map file.
	 * 
	 * @return	array
	 * 		An array of all libraries by key and value.
	 */
	public function getLibraryList()
	{
		// Get map file path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load Library File
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		$libArray = array();		
		$libraries = $parser->evaluate("//library");
		foreach ($libraries as $lib)
			$libArray[$parser->attr($lib, "name")] = $parser->attr($lib, "name");

		return $libArray;
	}
	
	/**
	 * Get all packages in the given library from the map file.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all packages in the library.
	 */
	public function getPackageList($library)
	{
		// Get map file path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load Library File
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		$packages = $parser->evaluate("//library[@name='".$library."']/package");
		$pkgArray = array();
		foreach ($packages as $pkg)
			$pkgArray[$parser->attr($pkg, "name")] = $parser->attr($pkg, "name");

		return $pkgArray;
	}
	
	/**
	 * Get all namespaces in the given package.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	array
	 * 		A nested array of all namespaces.
	 */
	public function getNSList($library, $package, $parentNs = "")
	{
		// Get map file path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load Library File
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Get Package
		$packageEntry = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']")->item(0);
		if (empty($parentNs))
			$parent = $packageEntry;
		else
		{
			// If namespace given, search for namespace
			$nss = explode("::", $parentNs);
			$q_nss = "namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss, $packageEntry)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$package'.");
		}
		
		// Get Children namespaces
		$namespaces = $parser->evaluate("namespace", $parent);
		
		// Create array
		$nsArray = array();
		foreach ($namespaces as $ns)
		{
			$nsName = $parser->attr($ns, "name");
			if (empty($nsName))
				continue;
			$tempParent = ($parentNs == "" ? "" : $parentNs."::").$nsName;
			$nsArray[$nsName] = $this->getNSList($library, $package, $tempParent);
		}
		
		return $nsArray;
	}
	
	/**
	 * Get all objects in the map, in the given library, package and namespace.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The namespace (separated by "::", if any).
	 * 		The default value is null, which will select all objects in the package at any depth. If is set to an empty string (""), it will select all objects as children of the package at depth 1.
	 * 
	 * @return	array
	 * 		An array of all items. An item is an array of object information, including title, name, library, package and namespace.
	 */
	public function getObjectList($library, $package = "", $parentNs = NULL)
	{
		// Get map file path
		$filePath = $this->folderPath."/".$this->mapFile;
		
		// Load Library index file
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		
		// Get Objects
		$libraryObjects = array();
		if (empty($package) && empty($parentNs))
			$objects = $parser->evaluate("//library[@name='".$library."']/object | //library[@name='".$library."']//object");
		else if (is_null($parentNs))
			$objects = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']//object");
		else if (empty($parentNs))
			$objects = $parser->evaluate("//library[@name='".$library."']/package[@name='".$package."']/object");
		else
		{
			$nss = explode("::", $parentNs);
			$q_nss = "//library[@name='".$library."']/package[@name='".$package."']/namespace[@name='".implode("']/namespace[@name='", $nss)."']";
			$parent = $parser->evaluate($q_nss)->item(0);
			if (is_null($parent))
				throw new Exception("Namespace '$parentNs' doesn't exist inside package '$package'.");
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
			$info['library'] = $library;
			
			// Get Namespace
			$nsArray = array();
			$parentNode = $obj->parentNode;
			while ($parentNode->tagName == "namespace")
			{
				$nsArray[] = $parser->attr($parentNode, "name");
				$parentNode = $parentNode->parentNode;
			}
			
			// Set namespace
			$nsArray = array_reverse($nsArray);
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