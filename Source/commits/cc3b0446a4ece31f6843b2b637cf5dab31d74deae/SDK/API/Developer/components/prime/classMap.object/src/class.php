<?php
//#section#[header]
// Namespace
namespace API\Developer\components\prime;

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
 * @namespace	\components\prime
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Developer\misc\vcs;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Class object map index manager.
 * 
 * Creates a map of all libraries, packages, namespaces and objects of a software kit.
 * 
 * @version	{empty}
 * @created	January 13, 2014, 12:23 (EET)
 * @revised	April 1, 2014, 16:55 (EEST)
 * 
 * @deprecated	Use \DEV\Prototype\sourceMap instead.
 */
class classMap
{
	/**
	 * The default map file name.
	 * 
	 * @type	string
	 */
	const MAP_FILE = "map.xml";
	
	/**
	 * The vcs object with the repository loaded.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	/**
	 * The inner path of the repository.
	 * 
	 * @type	string
	 */
	private $innerPath;
	/**
	 * The map file name.
	 * 
	 * @type	string
	 */
	private $mapFile;
	
	/**
	 * Constructor method for object initialization.
	 * 
	 * @param	string	$repository
	 * 		The repository path.
	 * 
	 * @param	boolean	$includeRelease
	 * 		Indicates whether the repository includes the release folder.
	 * 
	 * @param	string	$innerPath
	 * 		The inner repository path.
	 * 
	 * @param	string	$mapFile
	 * 		The map file name. By default the MAP_FILE constant is used.
	 * 
	 * @return	void
	 */
	public function __construct($repository, $includeRelease = FALSE, $innerPath = "", $mapFile = self::MAP_FILE)
	{
		$this->vcs = new vcs($repository, $includeRelease);
		$this->vcs->createStructure();
		
		$this->innerPath = $innerPath;
		$this->mapFile = $mapFile;
		
		// Create map file
		self::createMapFile();
	}
	
	/**
	 * Create the map file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if the file already exists.
	 */
	private function createMapFile()
	{
		// Get item ID
		$itemID = $this->getItemID();
		$itemPath = "/".$this->innerPath."/";
		$itemName = $this->mapFile;
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
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
	 * Get the map file item id.
	 * 
	 * @return	string
	 * 		The hash item id.
	 */
	private function getItemID()
	{
		return "m".md5("map_".$this->innerPath."_".$this->mapFile);
	}
	
	/**
	 * Create a new library in the map.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createLibrary($libName)
	{
		// Update index
		$itemID = $this->getItemID();
		$filePath = $this->vcs->updateItem($itemID, TRUE);
		
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
	 * Create a new package in the given library in the map.
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
	public function createPackage($libName, $packageName)
	{
		// Update index
		$itemID = $this->getItemID();
		$filePath = $this->vcs->updateItem($itemID, TRUE);
		
		// Open Index File and create entry
		$parser = new DOMParser();
		$parser->load($filePath, FALSE);
		$base = $parser->evaluate("//library[@name='".$libName."']")->item(0);
		
		// Search for same package
		$package = $parser->evaluate("//library[@name='".$libName."']/package[@name='$packageName']")->item(0);
		if (!is_null($package))
			return FALSE;
		
		$package = $parser->create("package");
		$parser->attr($package, "name", $packageName);
		$parser->append($base, $package);
		return $parser->update();
	}
	
	/**
	 * Create a new namespace in the given package in the map.
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
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createNamespace($libName, $packageName, $nsName, $parentNs = "")
	{
		// Update index
		$itemID = $this->getItemID();
		$filePath = $this->vcs->updateItem($itemID, TRUE);
		
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
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The namespace (separated by "::", if any).
	 * 
	 * @param	string	$objectName
	 * 		The object name.
	 * 
	 * @param	string	$title
	 * 		The object title.
	 * 
	 * @return	void
	 */
	public function createObject($libName, $packageName, $nsName, $objectName, $title = "")
	{
		// Update index
		$itemID = $this->getItemID();
		$filePath = $this->vcs->updateItem($itemID, TRUE);
		
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
	 * Gets all libraries in the map file.
	 * 
	 * @return	array
	 * 		An array of all libraries by key and value.
	 */
	public function getLibraryList()
	{
		// Get map file path
		$itemID = $this->getItemID();
		$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Load Library File
		$parser = new DOMParser();
		$parser->load($mapFilePath, FALSE);
		
		$libArray = array();		
		$libraries = $parser->evaluate("//library");
		foreach ($libraries as $lib)
			$libArray[$parser->attr($lib, "name")] = $parser->attr($lib, "name");

		return $libArray;
	}
	
	/**
	 * Get all packages in the given library from the map file.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all packages in the library.
	 */
	public function getPackageList($libName)
	{
		// Get map file path
		$itemID = $this->getItemID();
		$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Load Library File
		$parser = new DOMParser();
		$parser->load($mapFilePath, FALSE);
		
		$packages = $parser->evaluate("//library[@name='".$libName."']/package");
		$pkgArray = array();
		foreach ($packages as $pkg)
			$pkgArray[$parser->attr($pkg, "name")] = $parser->attr($pkg, "name");

		return $pkgArray;
	}
	
	/**
	 * Get all namespaces in the given package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	array
	 * 		A nested array of all namespaces.
	 */
	public function getNSList($libName, $packageName, $parentNs = "")
	{
		// Get map file path
		$itemID = $this->getItemID();
		$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
		
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
			$nsArray[$nsName] = $this->getNSList($libName, $packageName, $tempParent);
		}
		
		return $nsArray;
	}
	
	/**
	 * Get all objects in the map, in the given library, package and namespace.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The namespace (separated by "::", if any).
	 * 		The default value is null, which will select all objects in the package at any depth. If is set to an empty string (""), it will select all objects as children of the package at depth 1.
	 * 
	 * @return	array
	 * 		An array of all items. An item is an array of object information, including title, name, library, package and namespace.
	 */
	public function getObjectList($libName, $packageName = "", $parentNs = NULL)
	{
		// Get map file path
		$itemID = $this->getItemID();
		$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Load Library index file
		$parser = new DOMParser();
		$parser->load($mapFilePath, FALSE);
		
		// Get Objects
		$libraryObjects = array();
		if (empty($packageName) && empty($parentNs))
			$objects = $parser->evaluate("//library[@name='".$libName."']/object | //library[@name='".$libName."']//object");
		else if (is_null($parentNs))
			$objects = $parser->evaluate("//library[@name='".$libName."']/package[@name='".$packageName."']//object");
		else if (empty($parentNs))
			$objects = $parser->evaluate("//library[@name='".$libName."']/package[@name='".$packageName."']/object");
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