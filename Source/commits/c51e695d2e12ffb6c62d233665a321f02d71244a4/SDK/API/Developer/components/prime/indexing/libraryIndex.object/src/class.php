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
 * Library index manager.
 * 
 * Manages all library indexing.
 * 
 * @version	{empty}
 * @created	July 23, 2013, 14:05 (EEST)
 * @revised	November 7, 2013, 10:35 (EET)
 */
class libraryIndex
{
	/**
	 * The index filename.
	 * 
	 * @type	string
	 */
	const INDEX_FILE = "index.xml";
	
	/**
	 * Creates the library map index.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$domain
	 * 		The library domain.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createMapIndex($path, $domain, $libName)
	{
		// Library Mapping Path
		$libPath = $path."/".self::INDEX_FILE;
		$parser = new DOMParser();
		try
		{
			$parser->load($libPath, TRUE);
		}
		catch (Exception $ex)
		{
			$root = $parser->create($domain);
			$parser->append($root);
			$content = $parser->getXML();
			fileManager::create(systemRoot."/".$libPath, $content, TRUE);
		}
		
		$library = $parser->find($libName);
		if (!is_null($library))
			return FALSE;
			
		// Create index
		$root = $parser->evaluate("//$domain")->item(0);
		$libraryElement = $parser->create("library", "", $libName);
		$parser->append($root, $libraryElement);
		$parser->update(TRUE);

		// Create library index file
		return self::createLibraryIndex($path, $libName);
	}
	
	/**
	 * Creates a library index file.
	 * 
	 * @param	string	$path
	 * 		The index folder path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createLibraryIndex($path, $libName)
	{
		// Library Path
		$libPath = $path."/".$libName.".xml";
		if (file_exists(systemRoot.$libPath))
			return FALSE;
			
		// Create Library Index
		$parser = new DOMParser();
		$root = $parser->create("Library");
		$parser->attr($root, "name", $libName);
		$parser->append($root);
		return $parser->save(systemRoot."/".$libPath, "", TRUE);
	}	
	
	/**
	 * Creates a library release index.
	 * 
	 * @param	string	$folder
	 * 		The folder path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createReleaseIndex($folder, $libName)
	{
		// Library Path
		$libPath = systemRoot.$folder."/".$libName."/";
		
		// Create Library Index
		$parser = new DOMParser();
		$root = $parser->create("Library");
		$parser->attr($root, "name", $libName);
		$parser->append($root);
		return $parser->save($libPath, self::INDEX_FILE, TRUE);
	}
	
	/**
	 * Gets all libraries from the index.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @return	array
	 * 		An array of library names.
	 */
	public static function getList($path)
	{
		// Load Library File
		$parser = new DOMParser();
		$libPath = $path."/".self::INDEX_FILE;
		
		$libArray = array();
		try
		{
			$parser->load($libPath);
		}
		catch (Exception $ex)
		{
			return $libArray;
		}
		
		$libraries = $parser->evaluate("//library");
		foreach ($libraries as $lib)
			$libArray[$parser->attr($lib, "id")] = $parser->attr($lib, "id");

		return $libArray;
	}
	
	/**
	 * Gets all objects in a given library.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of objects with the object values [title, name, import (namespace)].
	 */
	public static function getLibraryObjects($path, $libName)
	{
		// Library Path
		$libPath = $path."/".$libName.".xml";
		
		// Load Library index file
		$parser = new DOMParser();
		$info = array();
		try
		{
			$parser->load($libPath, TRUE);
		}
		catch (Exception $ex)
		{
			return $info;
		}
		
		// Get Library
		$libraryBase = $parser->evaluate("//library[@name='$libName']")->item(0);

		// Get Library Objects
		$objects = $parser->evaluate("//object");

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
			$namespace = $parser->attr($parent_namespace, "id")."::".$namespace;
			$opts['import'] = $namespace;
			
			$ns = explode("::", $namespace);
			unset($ns[count($ns)-1]);
			$namespace = implode("::", $ns);
			$opts['namespace'] = $namespace;
			
			// Set Object
			$info[] = $opts;
		}

		return $info;
	}
	
	/**
	 * Gets all release objects in a given library.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of objects with the full name (including namespace).
	 */
	public function getReleaseLibraryObjects($path, $libName)
	{
		// Library Path
		$indexFilePath = $path."/".$libName."/";
		
		// Get Library Root Index
		$parser = new DOMParser();
		$parser->load($indexFilePath.self::INDEX_FILE);
		$packages = $parser->evaluate("//package/@name");
		
		// Parse packages
		$objectsArray = array();
		$oParser = new DOMParser();
		foreach ($packages as $p)
		{
			// Get Package Name
			$packageName = $p->nodeValue;
			
			// Load Index file
			$indexFilePath = $path."/".$libName."/".$packageName."/";
			$oParser->load($indexFilePath.self::INDEX_FILE);
			$objects = $oParser->evaluate("//object/@name");
			
			foreach ($objects as $o)
				$objectsArray[] = $packageName."::".$o->nodeValue;
		}
		
		return $objectsArray;
	}
	
	/**
	 * Gets all developer packages in a given library.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	boolean	$fullNames
	 * 		Indicates whether the package names will include the library name or not.
	 * 
	 * @return	array
	 * 		An array of package names.
	 */
	public static function getPackageList($path, $libName = "", $fullNames = TRUE)
	{
		$parser = new DOMParser();
		
		if ($libName == "")
		{
			$libs = self::getList($path);
			$pkgArray = array();
			foreach ($libs as $libName)
			{
				$filePath = $path."/".$libName.".xml";
				$parser->load($filePath, true);
				if (!$fullNames)
					$pkgArray[$libName] = array();
					
				$packages = $parser->evaluate("//package");
				foreach ($packages as $pkg)
				{
					if ($fullNames)
						$pkgArray[$libName."::".$parser->attr($pkg, "id")] = $libName." -> ".$parser->attr($pkg, "id");
					else
						$pkgArray[$libName][$parser->attr($pkg, "id")] = $parser->attr($pkg, "id");
				}
			}
			return $pkgArray;
		}
		
		
		// Load Library File
		$filePath = $path."/".$libName.".xml";
		$parser->load($filePath, true);
		
		$packages = $parser->evaluate("//package");
		$pkgArray = array();
		foreach ($packages as $pkg)
			$pkgArray[$parser->attr($pkg, "id")] = $parser->attr($pkg, "id");

		return $pkgArray;
	}
	
	/**
	 * Gets all released packages in a given library.
	 * 
	 * @param	string	$path
	 * 		The mapping path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	boolean	$fullNames
	 * 		Indicates whether the package names will include the library name or not.
	 * 
	 * @return	array
	 * 		An array of package names.
	 */
	public static function getReleasePackageList($path, $libName = "", $fullNames = TRUE)
	{
		$parser = new DOMParser();
		
		if ($libName == "")
		{
			$libs = self::getList($path);
			$pkgArray = array();
			foreach ($libs as $libName)
			{
				$filePath = $path."/".$libName."/index.xml";
				$parser->load($filePath, true);
				if (!$fullNames)
					$pkgArray[$libName] = array();
					
				$packages = $parser->evaluate("//package");
				foreach ($packages as $pkg)
				{
					if ($fullNames)
						$pkgArray[$libName."::".$parser->attr($pkg, "name")] = $libName." -> ".$parser->attr($pkg, "name");
					else
						$pkgArray[$libName][$parser->attr($pkg, "name")] = $parser->attr($pkg, "name");
				}
			}
			return $pkgArray;
		}
		
		
		// Load Library File
		$filePath = $path."/".$libName."/index.xml";
		$parser->load($filePath, true);
		
		$packages = $parser->evaluate("//package");
		$pkgArray = array();
		foreach ($packages as $pkg)
			$pkgArray[$parser->attr($pkg, "name")] = $parser->attr($pkg, "name");

		return $pkgArray;
	}
}
//#section_end#
?>