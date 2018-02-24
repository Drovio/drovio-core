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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
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
 * Resource Boot Loader
 * 
 * This is the manager class for loading and general handling the system's resources (javascript and css).
 * 
 * @version	6.0-3
 * @created	July 29, 2014, 19:54 (BST)
 * @updated	November 30, 2015, 11:07 (GMT)
 */
class BootLoader
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
	 * The resource header key.
	 * 
	 * @type	string
	 */
	const RSRC_HD_KEY = "bt_rsrc";
	
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
	 * @param	string	$version
	 * 		The project's version to export the css to.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function exportCSS($projectID, $library, $package, $content, $version = "")
	{
		return self::exportResource($resourceType = self::RSRC_CSS, $projectID, $library, $package, $content, $version);
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
	 * @param	string	$version
	 * 		The project's version to export the js to.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function exportJS($projectID, $library, $package, $content, $version = "")
	{
		return self::exportResource($resourceType = self::RSRC_JS, $projectID, $library, $package, $content, $version);
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
	 * @param	string	$version
	 * 		The project's version to export the resource to.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private static function exportResource($resourceType, $projectID, $library, $package, $content, $version = "")
	{
		// Trim/clear content
		$content = trim($content);
		
		// Form exported filename
		if (empty($version))
			$version = projectLibrary::getLastProjectVersion($projectID);
		$projectLibraryFolder = projectLibrary::getPublishedPath($projectID, $version);
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
	 * Load a css resource from the production.
	 * 
	 * @param	integer	$projectID
	 * 		The project id of the resource.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$version
	 * 		The project's version.
	 * 
	 * @return	string
	 * 		The css resource content.
	 */
	public static function loadResourceCSS($projectID, $library, $package, $version = "")
	{
		return self::loadResource($resourceType = self::RSRC_CSS, $projectID, $library, $package, $version);
	}
	
	/**
	 * Load a js resource from the production.
	 * 
	 * @param	integer	$projectID
	 * 		The project id of the resource.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$version
	 * 		The project's version.
	 * 
	 * @return	string
	 * 		The js resource content.
	 */
	public static function loadResourceJS($projectID, $library, $package, $version = "")
	{
		return self::loadResource($resourceType = self::RSRC_JS, $projectID, $library, $package, $version);
	}
	
	/**
	 * Load a resource from the production.
	 * 
	 * @param	string	$resourceType
	 * 		The resource type.
	 * 		See class constants.
	 * 
	 * @param	integer	$projectID
	 * 		The project id of the resource.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$version
	 * 		The project's version to export the resource to.
	 * 
	 * @return	string
	 * 		The resource content.
	 */
	private static function loadResource($resourceType, $projectID, $library, $package, $version = "")
	{
		// Form exported filename
		if (empty($version))
			$version = projectLibrary::getLastProjectVersion($projectID);
		$projectLibraryFolder = projectLibrary::getPublishedPath($projectID, $version);
		$fileName = self::getFileName($resourceType, $library, $package, $resourceType);
		$file = systemRoot.$projectLibraryFolder."/".$resourceType."/".$fileName.".".$resourceType;
		
		// Get file contents
		return fileManager::get($file);
	}
	
	/**
	 * Resolve all urls in a given content.
	 * Resolves all global and project-relative urls.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$content
	 * 		The content to be resolved.
	 * 
	 * @param	string	$version
	 * 		The project version for the project-relative urls.
	 * 
	 * @param	string	$protocol
	 * 		The url protocol to resolve to.
	 * 		If empty, the protocol will be taken from the current protocol under which the code is running.
	 * 
	 * @return	string
	 * 		The content with resolved urls.
	 */
	public static function resolveURLs($projectID, $content, $version, $protocol = NULL)
	{
		// Resolve project-specific urls
		if (!empty($projectID))
		{
			// Get project folder
			$publishFolder = projectLibrary::getPublishedPath($projectID, $version);
			
			// Resolve project-relative urls
			$resourcePath = $publishFolder.projectLibrary::RSRC_FOLDER;
			$resourcePath = str_replace(paths::getPublishedPath(), "", $resourcePath);
			$resourceUrl = url::resolve("lib", $resourcePath, $protocol);
			
			$content = str_replace("%resources%", $resourceUrl, $content);
			$content = str_replace("%{resources}", $resourceUrl, $content);
			$content = str_replace("%media%", $resourceUrl, $content);
			$content = str_replace("%{media}", $resourceUrl, $content);
		}
		
		// Resolve cdn urls
		$cdnUrl = url::resolve("cdn", "", $protocol);
		$content = str_replace("%cdn%", $cdnUrl, $content);
		$content = str_replace("%{cdn}", $cdnUrl, $content);
		
		// Clean protocol (if empty)
		if (empty($protocol))
		{
			$content = str_replace("http:", "", $content);
			$content = str_replace("https:", "", $content);
		}
		
		// Return content
		return $content;
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
			$version = projectLibrary::getLastProjectVersion($projectID, $live = FALSE);

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
		$url = url::get($url, $params);
		
		// Resolve for ajax testing (if active)
		$url = ajaxTester::resolve($url);
		
		// Global URL resolution
		$url = url::resolve("www", $url);
		
		// Return resolved url
		return $url;
	}
	
	/**
	 * Get a ready resource array for the server report.
	 * 
	 * @param	string	$projectType
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
		// Check if it makes sense to create the resource array
		if (empty($css) && empty($js))
			return array();
		
		// Init resource array
		$resourceArray = array();
		$resourceArray['id'] = self::getRsrcID($projectType, $library."_".$package);
		
		// Set tester attributes
		$resourceArray['tester'] = $tester;
		
		// Set package attributes
		$resourceArray['attributes']['category'] = $projectType;
		$resourceArray['attributes']['library'] = $library;
		$resourceArray['attributes']['package'] = $package;
		
		// Set css and js attributes
		if (!empty($css))
			$resourceArray['css'] = $css;
		if (!empty($js))
			$resourceArray['js'] = $js;
		
		// Return resource array
		return $resourceArray;
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
	 * Set BootLoader's resource attributes to a given DOMElement resource.
	 * 
	 * @param	string	$type
	 * 		The resource type.
	 * 		See class constants.
	 * 
	 * @param	DOMelement	$resource
	 * 		The resource element from the html document.
	 * 
	 * @param	string	$projectType
	 * 		The project type to be resource category.
	 * 
	 * @param	string	$rsrcID
	 * 		The resource id.
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