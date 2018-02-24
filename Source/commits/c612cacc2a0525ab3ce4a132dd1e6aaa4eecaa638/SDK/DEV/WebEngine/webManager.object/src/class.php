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

importer::import("DEV", "Temp", "vcs2");
use \DEV\Temp\vcs2;


class webManager
{
	const RELEASE_PATH = "/System/Library/Web/SDK/";
	
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
		// Get repository
		$project = new project(3);
		$vcs = new vcs2(3);
		$releasePath = $vcs->getCurrentRelease($branchName);
		
		// Copy map file
		$contents = fileManager::get($releasePath."/map.xml");
		fileManager::create(systemRoot.self::RELEASE_PATH."map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Documentation/Web/map.xml", $contents, TRUE);
		fileManager::create(systemRoot."/System/Resources/Model/Web/map.xml", $contents, TRUE);
		
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
					fileManager::create($sourceFile, $contents, TRUE);
					
					// Documentation and Manual file
					$docFile = systemRoot."/System/Resources/Documentation/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.xml";
					$contents = fileManager::get($releaseObjectPath."/src/doc.xml");
					fileManager::copy($docFile, $contents, TRUE);
					$manFile = systemRoot."/System/Resources/Documentation/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".php.man.xml";
					$contents = fileManager::get($releaseObjectPath."/manual.xml");
					fileManager::copy($manFile, $contents, TRUE);
					
					// Model file
					$modelFile = systemRoot."/System/Resources/Model/".$libName."/".$packageName."/".$ns."/".$objectInfo['name'].".xml";
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
		$project->publishResources("/Library/Media/w/");
		
		
		// Create Web SDK Zip Packages
	}
	
	
	private static function packCore()
	{
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
	}
	
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