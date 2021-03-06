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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Core", "test/ajaxTester");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;
use \UI\Html\DOM;
use \DEV\Core\test\ajaxTester;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;

/**
 * Project Resource Boot Loader
 * 
 * This is the manager class for loading and general handling project's resources (javascript and css).
 * 
 * @version	0.1-3
 * @created	January 6, 2015, 18:33 (EET)
 * @updated	January 7, 2015, 12:48 (EET)
 */
class BootLoader2
{
	/**
	 * The css resource type.
	 * 
	 * @type	string
	 */
	const RSRC_CSS = "css";
	
	/**
	 * The js resource type.
	 * 
	 * @type	string
	 */
	const RSRC_JS = "js";
	
	/**
	 * Export a css package to the project's last version folder.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to publish the resource for.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$content
	 * 		The resource content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function exportCSS($projectID, $library, $package, $content)
	{
		// Export project css resource
		$resourceType = self::RSRC_CSS;
		return self::exportResource($resourceType, $projectID, $library, $package, $content);
	}
	
	/**
	 * Export a js package to the project's last version folder.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to publish the resource for.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$content
	 * 		The resource content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function exportJS($projectID, $library, $package, $content)
	{
		// Export project js resource
		$resourceType = self::RSRC_JS;
		return self::exportResource($resourceType, $projectID, $library, $package, $content);
	}
	
	/**
	 * Export a resource package to the project's last version folder.
	 * 
	 * @param	string	$resourceType
	 * 		The resource type.
	 * 		See class constants.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to export the resource to.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$content
	 * 		The resource content.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private static function exportResource($resourceType, $projectID, $library, $package, $content)
	{
		// Trim/clear content
		$content = trim($content);
		
		// Form exported filename
		$projectLastVersion = projectLibrary::getLastProjectVersion($projectID);
		$projectLibraryFolder = projectLibrary::getPublishedPath($projectID, $projectLastVersion);
		$fileName = self::getFileName($resourceType, $library, $package, $resourceType);
		$file = systemRoot.$projectLibraryFolder."/".$resourceType."/".$fileName.".".$resourceType;
		
		// Remove file if empty
		if (empty($content))
			return;

		// Get hashtag
		$hashtag = "h".md5("hashtag_".$fileName);
		
		// Add index entry
		$parser = new DOMParser();
		try
		{
			$parser->load($projectLibraryFolder."/".$resourceType."/index.xml");
		}
		catch (Exception $ex)
		{
			// Create index and reload
			self::createIndex(systemRoot.$projectLibraryFolder."/".$resourceType."/");
			$parser->load($projectLibraryFolder."/".$resourceType."/index.xml");
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
	 * Get a project's resource url from the library domain from the given version.
	 * 
	 * @param	string	$resourceType
	 * 		The resource type.
	 * 		See class constants.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to get the resource for.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$version
	 * 		The project version.
	 * 		If empty, get the latest version.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The resolved resource url.
	 */
	public static function getResourceUrl($resourceType, $projectID, $library, $package, $version = "")
	{
		// Check version and if empty get last version
		if (empty($version))
			$version = projectLibrary::getLastProjectVersion($projectID);
		
		// Check version again
		if (empty($version))
			return NULL;
		
		// Get project's library folder
		$projectLibraryFolder = projectLibrary::getPublishedPath($projectID, $version);
		if (empty($projectLibraryFolder))
			return NULL;
		
		// Get resource file
		$fileName = self::getFileName($resourceType, $library, $package, $resourceType);
		$resourcePath = $projectLibraryFolder."/".$resourceType."/".$fileName.".".$resourceType;
		
		// Check file if exists
		if (!file_exists(systemRoot.$resourcePath))
			return NULL;
		
		// Set resource to url
		$resourcePath = str_replace(paths::getPublishedPath(), "", $resourcePath);
		$resourceUrl = url::resolve("lib", $resourcePath);
		
		// Return resource url
		return $resourceUrl;
	}
	
	/**
	 * Get a project's tester resource url from the system.
	 * 
	 * @param	string	$url
	 * 		The project specific url resource loader.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @return	string
	 * 		The project's tester resource url.
	 */
	public static function getTesterResourceUrl($url, $library, $package)
	{
		// Set URL Parameters
		$params = array();
		$params['library'] = $library;
		$params['package'] = $package;
		
		// Set ajax tester, if any
		if (ajaxTester::status())
			$url = ajaxTester::resolve($url);
		
		// Resolve URL
		$url = url::get($url, $params);
		$url = url::resolve("www", $url);
		
		// Return resolved url
		return $url;
	}
	
	/**
	 * Get a ready resource array for the server report.
	 * 
	 * @param	integer	$projectType
	 * 		The project type.
	 * 		It is used to distinguish resources from each other.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$css
	 * 		The resource css url, if any.
	 * 
	 * @param	string	$js
	 * 		The resource js url, if any.
	 * 
	 * @param	boolean	$tester
	 * 		Whether this is a tester resource url.
	 * 
	 * @return	array
	 * 		A bootloader resource array.
	 */
	public static function getResourceArray($projectType, $library, $package, $css, $js, $tester = FALSE)
	{
		// Init resource array
		$resourceArr = array();
		$resourceArr['id'] = self::getRsrcID($projectType, $library."_".$package);
		$resourceArr['header_type'] = "rsrc";
		$resourceArr['type'] = "rsrc";
		
		// Set tester attributes
		$resourceArr['tester'] = $tester;
		
		// Set package attributes
		$resourceArr['attributes']['category'] = $projectType;
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
	 * Returns the hashed file name of the resource package.
	 * 
	 * @param	string	$prefix
	 * 		The file prefix.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$type
	 * 		The resource type.
	 * 		See class constants.
	 * 
	 * @return	string
	 * 		The resource file name.
	 */
	private static function getFileName($prefix, $library, $package, $type)
	{
		return $prefix.hash("md5", "rsrc_".$library."_".$package."_".$type);
	}
	
	/**
	 * Get the resource hash id.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @return	string
	 * 		The resource hash id.
	 */
	public static function getRsrcID($library, $package)
	{
		return hash("md5", "rsrcID_".$library."_".$package);
	}
	
	/**
	 * Set BootLoader's resource attributes to a given dom element resource.
	 * 
	 * @param	string	$type
	 * 		The resource type.
	 * 		See class constants.
	 * 
	 * @param	DOMElement	$resource
	 * 		The resource element from the html document.
	 * 
	 * @param	integer	$projectType
	 * 		The project type to be resource category.
	 * 
	 * @param	string	$rsrcID
	 * 		The resource unique id.
	 * 
	 * @param	boolean	$static
	 * 		Whether this is a static resource or a dynamic and can be reloaded (for css only).
	 * 
	 * @return	void
	 */
	public static function setResourceAttributes($type, $resource, $projectType, $rsrcID, $static = FALSE)
	{
		if ($type == self::RSRC_CSS)
		{
			DOM::attr($resource, "data-id", $rsrcID);
			DOM::attr($resource, "data-static", $static);
		}
		else if ($type = self::RSRC_JS)
			DOM::attr($resource, "data-id", ($static ? "static:" : "").$rsrcID);
		
		// Set category
		DOM::attr($resource, "data-category", $projectType);
	}
	
	/**
	 * Creates the resource index in the exported folder.
	 * 
	 * @param	string	$folder
	 * 		The exported folder.
	 * 
	 * @return	void
	 */
	private static function createIndex($folder)
	{
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("hashes");
		$parser->append($root);
		
		// Save file
		fileManager::create($folder."/index.xml", "", TRUE);
		$parser->save($folder."/index.xml");
	}
}
//#section_end#
?>