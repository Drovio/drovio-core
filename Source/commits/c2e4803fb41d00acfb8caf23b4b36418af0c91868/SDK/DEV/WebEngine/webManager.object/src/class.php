<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine;

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
 * @package	WebEngine
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "WebEngine", "sdk::webLibrary");
importer::import("DEV", "WebEngine", "sdk::webPackage");

use \ESS\Protocol\client\BootLoader;
use \API\Resources\filesystem\fileManager;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\WebEngine\sdk\webLibrary;
use \DEV\WebEngine\sdk\webPackage;


/**
 * Web SDK Manager Class
 * 
 * Class is responsible for publishing / releasing the Web SDK and building the appropriate Packages Bundles for uploading the Web Core to a remote server
 * 
 * @version	0.1-1
 * @created	October 30, 2014, 21:21 (EET)
 * @revised	October 30, 2014, 21:21 (EET)
 */
class webManager
{
	/**
	 * The 'Web Project' code
	 * 
	 * @type	integer
	 */
	const PROJECT_CODE = 3;

	/**
	 * The Web SDK release path
	 * 
	 * @type	string
	 */
	const RELEASE_PATH = "/System/Library/Web/SDK/";
	
	/**
	 * The Web SDK documention path
	 * 
	 * @type	string
	 */
	const DOCS_PATH = "/System/Resources/Documentation/Web/";
 
	/**
	 * The Web SDK model path
	 * 
	 * @type	string
	 */
	const MODEL_PATH = "/System/Resources/Model/Web/";
	
	/**
	 * The Web SDK zip packages loacation path
	 * 
	 * @type	string
	 */
	const PACKAGES_PATH = "/System/Library/WebPackages/";
	
	/**
	 * Publishes all the SDK Libraries.
	 * 
	 * @param	string	$branchName
	 * 		The branch to publish.
	 * 
	 * @return	void
	 */
	public static function publish($branchName = vcs::MASTER_BRANCH)
	{
		// This function publishes the webSDK and not the complete core.
		// In later version where the Web Core will contain also other things (e.g sql queries)
		// this function needs to be revised.
	
		// Get repository
		$project = new project(self::PROJECT_CODE);
		$vcs = new vcs(self::PROJECT_CODE);	
		$releasePath = $vcs->getCurrentRelease($branchName)."/SDK/";
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot.self::RELEASE_PATH."map.xml", $contents, TRUE);
		fileManager::create(systemRoot.self::DOCS_PATH."map.xml", $contents, TRUE);
		fileManager::create(systemRoot.self::MODEL_PATH."map.xml", $contents, TRUE);
		
		// Deploy all SDK Libraries
		$sdkLib = new webLibrary();
		$libraries = $sdkLib->getList();
		foreach ($libraries as $libName)
		{
			$packages = $sdkLib->getPackageList($libName);
			foreach ($packages as $packageName)
			{
				$sdkp = new webPackage();
				$objects = $sdkp->getPackageObjects($libName, $packageName, NULL);
				
				// Initialize
				$cssContent = "";
				$jsContent = "";
				
				foreach ($objects as $objectInfo)
				{
					$ns = str_replace("::", "/", $objectInfo['namespace']);
					$releaseObjectPath = $releasePath."/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".object/";
					
					// Copy source
					$sourceFile = systemRoot.self::RELEASE_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php";
					$contents = fileManager::get($releaseObjectPath."/src/class.php");
					
					// If $contents is NULL there was an error
					//   On object creation
					//   Or on given path
					// Avoid to create an empty file
					if(!is_null($contents))
					{
						fileManager::create($sourceFile, $contents, TRUE);
					}
					else
					{
						// Break the current iteration 
						// TODO
						// Consider NOT breaking if the creation of .php
						// is not essential
						continue;
					}
					
					// Documentation and Manual file
					$docFile = systemRoot.self::DOCS_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.xml";
					$contents = fileManager::get($releaseObjectPath."/src/doc.xml");
					fileManager::copy($docFile, $contents, TRUE);
					$manFile = systemRoot.self::DOCS_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.man.xml";
					$contents = fileManager::get($releaseObjectPath."/manual.xml");
					fileManager::copy($manFile, $contents, TRUE);
					
					// Model file
					$modelFile = systemRoot.self::MODEL_PATH.$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".xml";
					$contents = fileManager::get($releaseObjectPath."/model/model.xml");
					fileManager::copy($modelFile, $contents, TRUE);
					
					// Object CSS
					$cssContent .= trim(fileManager::get($releaseObjectPath."/model/style.css"))."\n";
					
					// Object JS
					$jsContent .= trim(fileManager::get($releaseObjectPath."/script.js"))."\n";
				}
				
				// Replace resources vars in css
				$resourcePath = "/Library/Media/w";
				$cssContent = str_replace("%resources%", $resourcePath, $cssContent);
				
				// Export CSS Package
				$cssContent = cssParser::format($cssContent);
				BootLoader::exportCSS("Web", $libName, $packageName, $cssContent);
				
				// Export JS Package
				$jsContent = jsParser::format($jsContent);
				BootLoader::exportJS("Web", $libName, $packageName, $jsContent);
			}
		}
		
		// Publish Resources
		//$project->publishResources("/Library/Media/w/");
		
		
		// Create Web SDK Zip Packages
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private static function packCore()
	{
	/*
		// Create an archive with distro structure
		
		$archiveName = "/ebuilder_core.zip"; // Needs proper name...
		//$archive = systemRoot.self::ARCHIVES.$archiveName;
		$archive = systemRoot.tester::getTrunk()."/ebcore".$archiveName;
		
		// Create archive structure
		zipManager::createInnerDirectory($archive, ".web");
		
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
		*/
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$library
	 * 		{description}
	 * 
	 * @param	{type}	$package
	 * 		{description}
	 * 
	 * @return	void
	 */
	private static function packSource($library, $package = "") 
	{
	/*
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
*/	
}
	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private static function packStyles()
	{
	/*
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
		*/
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private static function packScripts()
	{
	/*
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
		*/
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$objFullName
	 * 		{description}
	 * 
	 * @return	void
	 */
	private static function identifyObject($objFullName)
	{
	/*
		$objInfo = array();
		$tmp = explode("::", $objFullName);
		// Object Name
		$objInfo[] = array_pop($tmp);
		// Package Name
		$objInfo[] = array_shift($tmp);
		// Namespace
		$objInfo[] = trim(implode("::", $tmp));
		
		return $objInfo;
		*/
	}
	
}
//#section_end#
?>