<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ebuilder;

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
 * @namespace	\components\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::ebuilder::ebLibrary");
importer::import("API", "Developer", "components::ebuilder::ebPackage");
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");

use \API\Developer\components\ebuilder\ebLibrary;
use \API\Developer\components\ebuilder\ebPackage;
use \API\Developer\profiler\tester;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;
 
/**
 * Ebuilder Manager
 * 
 * Manages ebuilder's libraries, scripts, and resources.
 * 
 * @version	{empty}
 * @created	July 29, 2013, 12:58 (EEST)
 * @revised	July 29, 2013, 13:06 (EEST)
 */
class ebManager
{
	/**
	 * Ebuilder archive directory
	 * 
	 * @type	string
	 */
	const ARCHIVES = "/System/Resources/ebuilder";
	
	/**
	 * Packs eBuilder's core into an archive
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public static function packCore()
	{
		// Create an archive with distro structure
		
		$archiveName = "/ebuilder_core.zip"; // Needs proper name...
		//$archive = systemRoot.self::ARCHIVES.$archiveName;
		$archive = systemRoot.tester::getTrunk()."/ebcore".$archiveName;
		
		// Create archive structure
		zipManager::createInnerDirectory($archive, ".ebuilder");
		
		// Pack Libraries
		$lib = new ebLibrary();
		$libs = $lib->getList();
		foreach ($libs as $libName)
		{
			// Create inner archive folder
			zipManager::createInnerDirectory($archive, $libName, ".ebuilder");
			
			$lib = new ebLibrary($libName);
			//$lib->pack($libName, systemRoot.self::ARCHIVES, $archiveName, ".ebuilder/".$libName);
			$lib->pack($libName, systemRoot.tester::getTrunk()."/ebcore", $archiveName, ".ebuilder/".$libName);
		}
		
		// Pack Resources
		zipManager::createInnerDirectory($archive, "Resources", ".ebuilder");
		// ...
		
		// Pack Styles
		zipManager::createInnerDirectory($archive, "Styles", ".ebuilder");
		$styleContents = self::packStyles();
		zipManager::createInnerFile($archive, $styleContents, "styles.css", ".ebuilder/Styles");
		
		// Pack Scripts
		zipManager::createInnerDirectory($archive, "Scripts", ".ebuilder");
		$scriptContents = self::packScripts();
		zipManager::createInnerFile($archive, $scriptContents, "scripts.js", ".ebuilder/Scripts");
		
		return TRUE;
	}
	
	/**
	 * Packs a (library's) package(s) into its respective archive
	 * 
	 * @param	string	$library
	 * 		Name of the library
	 * 
	 * @param	string	$package
	 * 		Name of a specific package
	 * 
	 * @return	void
	 */
	public static function packSource($library, $package = "") 
	{
		$lib = new ebLibrary($library);
		
		// Get list of library packages, or specific package
		$pkgs = array( $package );
		if (empty($pkgs))
			$pkgs = $lib->getPackageList();
		
		foreach ($pkgs as $p)
		{
			$pkg = new ebPackage();
			$pkg->load($library, $p);
			
			// Pack package objects
			//$pkg->pack($library, $p, systemRoot.self::ARCHIVES);
			$pkg->pack($library, $p, systemRoot.tester::getTrunk()."/ebcore/");
		}
	}
	
	/**
	 * Collects ebuilder's styles
	 * 
	 * @return	string
	 * 		The collection of the styles
	 */
	private static function packStyles()
	{
		// Get all libraries and their objects
		$lib = new ebLibrary();
		$libs = $lib->getList();
		
		$cssCode = "";
		foreach ($libs as $libName)
		{
			$lib = new ebLibrary($libName);
			
			// Get all objects' CSS and merge in a file{?????} ...
			$objects = $lib->getReleaseLibraryObjects();
			foreach ($objects as $o)
			{
				list ($objName, $pkgName, $ns) = self::identifyObject($o);
				$obj = new ebObject($libName, $pkgName, $ns, $objName);
				$cssCode .= $obj->getCSSCode()."\n";
			}
		}
		
		//return or save cssCode in a file...?
		return $cssCode;
	}
	
	/**
	 * Collects ebuilder's scripts
	 * 
	 * @return	string
	 * 		The collection of scripts
	 */
	private static function packScripts()
	{
		// Get all libraries and their objects
		$lib = new ebLibrary();
		$libs = $lib->getList();
		
		$jsCode = "";
		foreach ($libs as $libName)
		{
			$lib = new ebLibrary($libName);
			
			// Get all objects' JS and merge in a file{?????} ...
			$objects = $lib->getReleaseLibraryObjects();
			foreach ($objects as $o)
			{
				list ($objName, $pkgName, $ns) = self::identifyObject($o);
				$obj = new ebObject($libName, $pkgName, $ns, $objName);
				$jsCode .= $obj->getJSCode()."\n";
			}
		}
		
		//return or save jsCode in a file...?
		return $jsCode;
	}
	
	/**
	 * Packs ebuilder's resources in an archive
	 * 
	 * @return	void
	 */
	public static function packResources() {}
	
	/**
	 * Receives an object's full name, and breaks it into its respective parts
	 * 
	 * @param	string	$objFullName
	 * 		The object's full name
	 * 
	 * @return	array
	 * 		An array that holds the object's name, the object package's name, and the object's namespace.
	 */
	private static function identifyObject($objFullName)
	{
		$objInfo = array();
		$tmp = explode("::", $objFullName);
		// Object Name
		$objInfo[] = array_pop($tmp);
		// Package Name
		$objInfo[] = array_shift($tmp);
		// Namespace
		$objInfo[] = trim(implode("::", $tmp));
		
		return $objInfo;
	}
}
//#section_end#
?>