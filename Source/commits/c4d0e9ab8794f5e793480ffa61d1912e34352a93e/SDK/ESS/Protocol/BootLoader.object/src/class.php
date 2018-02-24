<?php
//#section#[header]
// Namespace
namespace ESS\Protocol;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Core", "test::ajaxTester");

use \ESS\Environment\url;
use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \UI\Html\DOM;
use \DEV\Core\test\ajaxTester;

/**
 * Resource Boot Loader
 * 
 * This is the manager class for loading and general handling the system's resources (javascript and css).
 * 
 * @version	2.0-6
 * @created	July 29, 2014, 21:54 (EEST)
 * @revised	December 17, 2014, 16:05 (EET)
 */
class BootLoader
{
	/**
	 * The css package loader file in testing mode.
	 * 
	 * @type	string
	 */
	const URL_CSS_LOADER = "/ajax/resources/explicit/css.php";
	/**
	 * The js package loader file in testing mode.
	 * 
	 * @type	string
	 */
	const URL_JS_LOADER = "/ajax/resources/explicit/js.php";
	
	/**
	 * List of startup sdk packages for the module loaded
	 * 
	 * @type	array
	 */
	private static $startupPackages = array();
	
	/**
	 * A list of prefixes for css and hs.
	 * 
	 * @type	array
	 */
	private static $prefix = array('Packages' => 'p', 'Modules' => 'm', 'Web' => 'w');
	
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
			$url = url::get($url, $params);
			$url = ajaxTester::resolve($url);
		}
		else
		{
			$url = "/Library/Resources/".$type."/".self::$prefix[$category]."/".self::getFileName(self::$prefix[$category], $library, $package, $type).".".$type;
			if (!file_exists(systemRoot.$url))
				return NULL;
		}
		
		// Return url
		return url::resolve("www", $url, $https = FALSE);
	}
	
	/**
	 * Get a bootloader's resource ready array.
	 * 
	 * @param	string	$category
	 * 		The resource category.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	mixed	$css
	 * 		The css attribute of the given resource.
	 * 		It can be true or false in case of a tester resource, or the resource path.
	 * 
	 * @param	mixed	$js
	 * 		The js attribute of the given resource.
	 * 		It can be true or false in case of a tester resource, or the resource path.
	 * 
	 * @param	boolean	$tester
	 * 		Whether this is a tester mode resource.
	 * 
	 * @return	array
	 * 		A bootloader resource array.
	 */
	public static function getResourceArray($category, $library, $package, $css, $js, $tester = FALSE)
	{
		// Init resource array
		$resourceArr = array();
		$resourceArr['id'] = self::getRsrcID($category, $package);
		$resourceArr['header_type'] = "rsrc";
		$resourceArr['type'] = "rsrc";
		
		// Set tester attributes
		$resourceArr['tester'] = $tester;
		
		// Set package attributes
		$resourceArr['attributes']['category'] = $category;
		$resourceArr['attributes']['library'] = $library;
		$resourceArr['attributes']['package'] = $package;
		
		// Set css and js attributes
		if (!empty($css))
			$resourceArr['css'] = $css;
		if (!empty($js))
			$resourceArr['js'] = $js;
		
		// Get resource only of there is js or css
		if (!empty($resourceArr['css']) || !empty($resourceArr['js']))
			return $resourceArr;

		// Otherwise, return empty resource
		return array();
	}
	
	/**
	 * Get the prefix of a given resource category.
	 * 
	 * @param	string	$name
	 * 		The category name.
	 * 
	 * @return	string
	 * 		The prefix value.
	 */
	public static function getPrefix($name)
	{
		return self::$prefix[$name];
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
	 * @param	string	$tester
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
	 * 		True on success, false on failure.
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
		$hashtag = "h".md5("hashtag_".$fileName);
		
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function exportJS($category, $library, $package, $content)
	{
		// Form exported filename
		$fileName = self::getFileName(self::$prefix[$category], $library, $package, "js");
		$file = systemRoot."/Library/Resources/js/".self::$prefix[$category]."/".$fileName.".js";

		// Remove file if empty
		if (empty($content))
			return fileManager::remove($file);

		// Get hashtag
		$hashtag = "h".md5("hashtag_".$fileName);
		
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
	 * @param	string	$type
	 * 		The url resource type (Modules or Packages).
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
		$url = url::resolve("www", $url, $https = FALSE);
		$url = url::get($url, $params);
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
	public static function getFileName($prefix, $library, $package, $type = "")
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
	public static function getRsrcID($library, $package)
	{
		return hash("md5", "rsrcID_".$library."_".$package);
	}
	
	/**
	 * Set BootLoader's resource attributes.
	 * 
	 * @param	string	$type
	 * 		The resource type.
	 * 		"css" or "js".
	 * 
	 * @param	DOMelement	$resource
	 * 		The resource element from the html document.
	 * 
	 * @param	string	$category
	 * 		The resource category.
	 * 
	 * @param	string	$rsrcID
	 * 		The resource id.
	 * 
	 * @param	boolean	$static
	 * 		Whether this is a static resource or a dynamic and can be reloaded (for css only).
	 * 
	 * @return	void
	 */
	public static function setResourceAttributes($type, $resource, $category, $rsrcID, $static = FALSE)
	{
		if ($type == "css")
		{
			DOM::attr($resource, "data-id", $rsrcID);
			DOM::attr($resource, "data-static", $static);
		}
		else if ($type = "js")
			DOM::attr($resource, "data-id", ($static ? "static:" : "").$rsrcID);
		
		// Set category
		DOM::attr($resource, "data-category", $category);
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