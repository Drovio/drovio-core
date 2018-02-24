<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("API", "Model", "modules::module");
importer::import("API", "Profile", "tester");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Profiler", "test::ajaxTester");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Core", "sdk::sdkPackage");

use \ESS\Protocol\client\environment\Url;
use \API\Model\modules\module;
use \API\Profile\tester;
use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \DEV\Profiler\test\ajaxTester;
use \DEV\Modules\module as DEVModule;
use \DEV\Core\sdk\sdkPackage;

/**
 * Resource Boot Loader
 * 
 * The manager class for loading and general handling the system's resources (javascript and css).
 * 
 * @version	0.1-1
 * @created	March 27, 2013, 11:53 (EET)
 * @revised	July 18, 2014, 18:22 (EEST)
 */
class BootLoader
{
	/**
	 * List of startup sdk packages for the module loaded
	 * 
	 * @type	array
	 */
	private static $startupPackages = array();
	/**
	 * The module loaded.
	 * 
	 * @type	integer
	 */
	private static $moduleID;
	
	/**
	 * The css package loader file.
	 * 
	 * @type	string
	 */
	const URL_CSS_LOADER = "/ajax/resources/explicit/css.php";
	/**
	 * The js package loader file.
	 * 
	 * @type	string
	 */
	const URL_JS_LOADER = "/ajax/resources/explicit/js.php";
	
	/**
	 * A list of prefixes for css and hs.
	 * 
	 * @type	array
	 */
	private static $prefix = array('Packages' => 'p', 'Modules' => 'm', 'Web' => 'w');
	
	/**
	 * The reverse array of prefix.
	 * 
	 * @type	array
	 */
	private static $revPrefix = array('p' => 'Packages', 'm' => 'Modules', 'w' => 'Web');
	
	/**
	 * Get a system resource url.
	 * 
	 * @param	string	$type
	 * 		The attribute type, "css" or "js".
	 * 
	 * @param	string	$category
	 * 		The resource category, "Modules" or "Packages".
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	boolean	$tester
	 * 		The package tester status.
	 * 
	 * @return	string
	 * 		The resource url with any attributes it may has.
	 */
	public static function getResource($type, $category, $library, $package, $tester = FALSE)
	{
		// If tester, get url from testing resources
		if ($tester)
		{
			$params = array();
			$params['category'] = $category;
			$params['library'] = $library;
			$params['package'] = $package;
			$url = ($type == "css" ? self::URL_CSS_LOADER : self::URL_JS_LOADER);
			$url = Url::get($url, $params);
			$url = ajaxTester::resolve($url);
		}
		else
		{
			$url = "/Library/Resources/".$type."/".self::$prefix[$category]."/".self::getFileName(self::$prefix[$category], $library, $package, $type).".".$type;
			if (!file_exists(systemRoot.$url))
				return NULL;
		}
		
		// Return url
		return Url::resolve("www", $url, $https = FALSE);
	}
	
	
	/**
	 * Returns the url for the css package loader.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	boolean	$tester
	 * 		Indicator that specifies whether the resource will be loaded from the exported resources or from the repositories.
	 * 
	 * @return	string
	 * 		The url to be loaded.
	 */
	public static function getCSSUrl($library, $package, $tester = FALSE)
	{
		return self::getURL(self::URL_CSS_LOADER, $library, $package, $tester, "css");
	}
	
	/**
	 * Returns the url for the javascript package loader.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	boolean	$tester
	 * 		Indicator that specifies whether the resource will be loaded from the exported resources or from the repositories.
	 * 
	 * @return	string
	 * 		The url to be loaded.
	 */
	public static function getJSUrl($library, $package, $tester = FALSE)
	{
		return self::getURL(self::URL_JS_LOADER, $library, $package, $tester, "js");
	}
	
	/**
	 * Export a css package to the resource library folder.
	 * 
	 * @param	string	$category
	 * 		The category package (subfolder).
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$content
	 * 		The resource content.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function exportCSS($category, $library, $package, $content)
	{
		// Form exported filename
		$fileName = self::getFileName(self::$prefix[$category], $library, $package, "css");
		$file = systemRoot."/Library/Resources/css/".self::$prefix[$category]."/".$fileName.".css";
		
		// Remove file if empty
		if (empty($content))
			return fileManager::remove($file);
			
		// Get hashtag
		$hashtag = md5("hashtag_".$fileName);
		
		// Add index entry
		$parser = new DOMParser();
		try
		{
			$parser->load("/Library/Resources/css/".self::$prefix[$category]."/index.xml");
		}
		catch (Exception $ex)
		{
			// Create index and reload
			self::createIndex(systemRoot."/Library/Resources/css/".self::$prefix[$category]."/", $category);
			$parser->load("/Library/Resources/css/".self::$prefix[$category]."/index.xml");
		}
		
		$entry = $parser->find($hashtag);
		if (is_null($entry))
		{
			$root = $parser->evaluate("/hashes")->item(0);
			$entry = $parser->create("package", $library."_".$package, $hashtag);
			$parser->attr($entry, "filename", $fileName);
			$parser->append($root, $entry);
			$parser->update();
		}
		
		// Create headers
		$header = "/* #".$hashtag." */\n";
		$content = $header.$content;
		
		// Save file
		return fileManager::create($file, $content, TRUE);
	}
	
	/**
	 * Export a javascript package to the resource library folder.
	 * 
	 * @param	string	$category
	 * 		The category package (subfolder).
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$content
	 * 		The resource content.
	 * 
	 * @return	void
	 */
	public static function exportJS($category, $library, $package, $content)
	{
		// Form exported filename
		$fileName = self::getFileName(self::$prefix[$category], $library, $package, "js");
		$file = systemRoot."/Library/Resources/js/".self::$prefix[$category]."/".$fileName.".js";

		// Remove file if empty
		if (empty($content))
			return fileManager::remove($file);

		// Get	 hashtag
		$hashtag = md5("hashtag_".$fileName);
		
		// Add index entry
		$parser = new DOMParser();
		try
		{
			$parser->load("/Library/Resources/js/".self::$prefix[$category]."/index.xml");
		}
		catch (Exception $ex)
		{
			// Create index and reload
			self::createIndex(systemRoot."/Library/Resources/js/".self::$prefix[$category]."/", $category);
			$parser->load("/Library/Resources/js/".self::$prefix[$category]."/index.xml");
		}
		
		$entry = $parser->find($hashtag);
		if (is_null($entry))
		{
			$root = $parser->evaluate("/hashes")->item(0);
			$entry = $parser->create("package", $library."_".$package, $hashtag);
			$parser->attr($entry, "filename", $fileName);
			$parser->append($root, $entry);
			$parser->update();
		}
		
		// Create headers
		$header = "/* #".$hashtag." */\n";
		$content = $header.$content;
		
		// Create file
		return fileManager::create($file, $content, TRUE);
	}
	
	/**
	 * Sets the loaded module.
	 * 
	 * @param	integer	$moduleID
	 * 		The loaded module's id.
	 * 
	 * @return	void
	 */
	public static function setModuleResource($moduleID)
	{
		self::$moduleID = $moduleID;
	}
	
	/**
	 * Returns the module resource as an array.
	 * Depending on the module's tester status, it returns the id in the proper format.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use getModuleResources() instead.
	 */
	public static function getModuleResource()
	{
		// Check module existance
		if (empty(self::$moduleID))
			return array();
		
		$attr = array();
		// Set unique resource id
		$attr['id'] = self::getRsrcID("mdl", self::$moduleID);
		// Check if user is tester for this module
		if (tester::testerModule(self::$moduleID))
		{
			// Set tester attributes
			$attr['tester'] = TRUE;
			
			// Set module attributes
			$attr['attributes']['category'] = "Modules";
			$attr['attributes']['package'] = self::$moduleID;
			
			$moduleObject = new DEVModule(self::$moduleID);
			$attr['css'] = $moduleObject->hasCSS();
			$attr['js'] = $moduleObject->hasJS();
		}
		else
		{
			if (module::hasCSS(self::$moduleID))
				$attr['css'] = self::$prefix["Modules"]."/".self::getFileName(self::$prefix["Modules"], "Modules", self::$moduleID, "css");
			if (module::hasJS(self::$moduleID))
				$attr['js'] = self::$prefix["Modules"]."/".self::getFileName(self::$prefix["Modules"], "Modules", self::$moduleID, "js");
		}
		
		// Add resource only of there is js or css
		if ($attr['css'] || $attr['js'])
			return $attr;
		
		return array();
	}
	
	/**
	 * Get all module resources that are related to the BootLoader's loaded module.
	 * 
	 * @return	array
	 * 		An array of resource id as key and as value an array of resource css and js data.
	 */
	public static function getModuleResources()
	{
		// Check module existance
		if (empty(self::$moduleID))
			return array();
		
		// Init resources array
		$resources = array();
		
		$modules = array();
		$modules[] = self::$moduleID;
		// Get inner modules for the host module
		$innerModules = module::getInnerModules(self::$moduleID);
		$modules = array_merge($modules, $innerModules);
		
		// Get all resources for modules
		foreach ($modules as $moduleID)
		{
			$rsrc = self::getModuleRsrcArray($moduleID);
			$rsrc['header_type'] = "rsrc";
			$rsrc['type'] = "rsrc";
			if (!empty($rsrc))
				$resources[$rsrc['id']] = $rsrc;
		}
		
		// Return all resources
		return $resources;
	}
	
	/**
	 * Get the given module's resource array including the inner modules.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to get the resource from.
	 * 
	 * @return	array
	 * 		An array of resources by id.
	 * 		The array has a key of the resource id and a value of the resource attributes.
	 * 		For testers, it includes as extra information the module id and the tester mode.
	 */
	private static function getModuleRsrcArray($moduleID)
	{
		// Init resource
		$resource = array();
		
		// Set unique resource id
		$resource['id'] = self::getRsrcID("mdl", $moduleID);
		
		// Check if user is tester for this module
		if (tester::testerModule($moduleID))
		{
			// Set tester attributes
			$resource['tester'] = TRUE;
			
			// Set module attributes
			$resource['attributes']['category'] = "Modules";
			$resource['attributes']['package'] = $moduleID;
			
			$moduleObject = new DEVModule($moduleID);
			$resource['css'] = $moduleObject->hasCSS();
			$resource['js'] = $moduleObject->hasJS();
		}
		else
		{
			try
			{
				if (module::hasCSS($moduleID))
					$resource['css'] = self::$prefix["Modules"]."/".self::getFileName(self::$prefix["Modules"], "Modules", $moduleID, "css");
			}
			catch (Exception $ex)
			{
				$resource['css'] = FALSE;
			}
			
			try
			{
				if (module::hasJS($moduleID))
					$resource['js'] = self::$prefix["Modules"]."/".self::getFileName(self::$prefix["Modules"], "Modules", $moduleID, "js");
			}
			catch (Exception $ex)
			{
				$resource['js'] = FALSE;
			}
		}
		
		// Add resource only of there is js or css
		if ($resource['css'] || $resource['js'])
			return $resource;
		
		return array();
	}
	
	/**
	 * Forms the url according to parameters given.
	 * 
	 * @param	string	$url
	 * 		The base url.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	boolean	$tester
	 * 		Indicator that specifies whether the resource will be loaded from the exported resources or from the repositories.
	 * 		In this function, defines whether the url will be hashed or not.
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	string
	 * 		The resource url.
	 */
	private static function getURL($url, $library, $package, $tester = FALSE, $type = "")
	{
		// Set URL Parameters
		$params = array();
		
		if ($tester)
		{
			$params['md'] = $package;
			$params['library'] = $library;
			$params['package'] = $package;
		}
		else
		{
			$url = "/Library/Resources/".$type."/".self::$prefix[$library]."/".self::getFileName(self::$prefix[$library], $library, $package, $type).".".$type;
		}
		
		// Resolve URL
		$url = Url::get($url, $params);
		$url = Url::resolve("www", $url, $https = FALSE);
		if ($tester && ajaxTester::status())
			return ajaxTester::resolve($url);
		
		return $url;
	}	
	
	/**
	 * Returns the hashed file name of the resource package.
	 * 
	 * @param	string	$prefix
	 * 		The file prefix.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$type
	 * 		The resource type, "css" or "js".
	 * 
	 * @return	string
	 * 		The resource file name.
	 */
	private static function getFileName($prefix, $library, $package, $type = "")
	{
		return hash("md5", "rsrc_".$library."_".$package."_".$type);
	}
	
	/**
	 * Get the resource hash id.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	string
	 * 		The resource hash id.
	 */
	private static function getRsrcID($library, $package)
	{
		return hash("md5", "rsrcID_".$library."_".$package);
	}
	
	/**
	 * Creates the resource index in the exported folder.
	 * 
	 * @param	string	$folder
	 * 		The exported folder.
	 * 
	 * @param	string	$title
	 * 		The index title.
	 * 
	 * @return	void
	 */
	private static function createIndex($folder, $title = "")
	{
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("hashes", "", $title);
		$parser->append($root);
		
		// Save file
		fileManager::create($folder."/index.xml", "", TRUE);
		$parser->save($folder."/index.xml");
	}
}
//#section_end#
?>