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

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Developer", "components::sdk::sdkPackage");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Profile", "tester");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "DOMParser");

use \ESS\Protocol\client\environment\Url;
use \API\Developer\components\units\modules\module;
use \API\Developer\components\sdk\sdkPackage;
use \API\Model\units\modules\Smodule;
use \API\Profile\tester;
use \API\Resources\filesystem\fileManager;
use \API\Resources\DOMParser;

/**
 * Resource Boot Loader
 * 
 * The manager class for loading and general handling the system's resources (javascript and css).
 * 
 * @version	{empty}
 * @created	March 27, 2013, 11:53 (EET)
 * @revised	November 6, 2013, 15:36 (EET)
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
	private static $prefix = array('Packages' => 'p', 'Modules' => 'm', 'appCenter' => 'acl', 'eBuilder' => 'eb');
	/**
	 * The reverse array of prefix.
	 * 
	 * @type	array
	 */
	private static $revPrefix = array('p' => 'Packages', 'm' => 'Modules', 'acl' => 'appCenter', 'eb' => 'eBuilder', 'jq' => 'jQ');
	
	/**
	 * Returns the url for the css package loader.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	boolean	$tester
	 * 		Indicator that specifies whether the resource will be loaded from the exported resources or from the repositories.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getCSSUrl($library, $package, $tester = FALSE)
	{
		return self::getURL(self::URL_CSS_LOADER, $library, $package, $tester);
	}
	
	/**
	 * Returns the url for the javascript package loader.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	boolean	$tester
	 * 		Indicator that specifies whether the resource will be loaded from the exported resources or from the repositories.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getJSUrl($library, $package, $tester = FALSE)
	{
		return self::getURL(self::URL_JS_LOADER, $library, $package, $tester);
	}
	
	/**
	 * Loads a css package file from the exported resources (by id).
	 * 
	 * @param	string	$id
	 * 		The hashname of the file.
	 * 
	 * @return	void
	 */
	public static function loadCSS($id)
	{
		// Get Category
		$parts = explode(".", $id);
		$cat = self::$revPrefix[$parts[0]];
		
		// Get URL
		$url = "/System/Library/CSS/".$cat."/".$id.".css";
		
		// Import CSS
		importer::incl($url, TRUE, TRUE);
	}
	
	/**
	 * Loads a css package file from the exported resources (by library and package).
	 * 
	 * @param	string	$category
	 * 		The category package (subfolder).
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	void
	 */
	public static function loadFriendCSS($category, $library, $package)
	{
		$id = self::getFileName(self::$prefix[$category], $library, $package);
		self::loadCSS($id);
	}
	
	/**
	 * Loads a javascript package file from the exported resources (by id).
	 * 
	 * @param	string	$id
	 * 		The hashname of the file.
	 * 
	 * @return	void
	 */
	public static function loadJS($id)
	{
		// Get Category
		$parts = explode(".", $id);
		$cat = self::$revPrefix[$parts[0]];
		
		// Get URL
		$url = "/System/Library/JS/".$cat."/".$id.".js";

		// Import CSS
		importer::incl($url, TRUE, TRUE);
	}
	
	/**
	 * Loads a js package file from the exported resources (by library and package).
	 * 
	 * @param	string	$category
	 * 		The category package (subfolder).
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	void
	 */
	public static function loadFriendJS($category, $library, $package)
	{
		$id = self::getFileName(self::$prefix[$category], $library, $package);
		self::loadJS($id);
	}
	
	/**
	 * Export a css package to the resource library folder.
	 * 
	 * @param	string	$category
	 * 		The category package (subfolder).
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
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
		$fileName = self::getFileName(self::$prefix[$category], $library, $package);
		$file = systemRoot."/System/Library/CSS/".$category."/".$fileName.".css";

		// Remove file if empty
		if (empty($content))
			return fileManager::remove($file);
		
		// Get hashtag
		$hashtag = md5("package_tag_".$fileName);
		
		// Add index entry
		$parser = new DOMParser();
		try
		{
			$parser->load("/System/Library/CSS/".$category."/index.xml");
		}
		catch (Exception $ex)
		{
			// Create index and reload
			self::createIndex(systemRoot."/System/Library/CSS/".$category."/", $category);
			$parser->load("/System/Library/CSS/".$category."/index.xml");
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
		$header = "/* ".$hashtag." */\n";
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
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
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
		$fileName = self::getFileName(self::$prefix[$category], $library, $package);
		$file = systemRoot."/System/Library/JS/".$category."/".$fileName.".js";
		
		// Remove file if empty
		if (empty($content))
			return fileManager::remove($file);
			
		// Get	 hashtag
		$hashtag = md5("package_tag_".$fileName);
		
		// Add index entry
		$parser = new DOMParser();
		try
		{
			$parser->load("/System/Library/JS/".$category."/index.xml");
		}
		catch (Exception $ex)
		{
			// Create index and reload
			self::createIndex(systemRoot."/System/Library/JS/".$category."/", $category);
			$parser->load("/System/Library/JS/".$category."/index.xml");
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
		$header = "/* ".$hashtag." */\n";
		$content = $header.$content;
		
		// Create file
		return fileManager::create($file, $content, TRUE);
	}
	
	/**
	 * Adds a startup sdk package index for the module loading.
	 * 
	 * @param	string	$libName
	 * 		The package's library.
	 * 
	 * @param	string	$packageName
	 * 		The package's name.
	 * 
	 * @return	void
	 * 
	 * @deprecated	This function is deprecated.
	 */
	public static function addStartupPackage($libName, $packageName){}
	
	/**
	 * Returns the startup packages as a list of attributes.
	 * Depending on the package's tester status, it returns array of attributes for loading the resources from BootLoader.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	This function is deprecated.
	 */
	public static function getStartupPackages(){}
	
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
			$attr['attributes']['md'] = self::$moduleID;
		else
			$attr['attributes']['hs'] = self::getFileName(self::$prefix["Modules"], "Modules", self::$moduleID);
		
		// Check if module has js or css
		if (tester::testerModule(self::$moduleID))
		{
			$moduleObject = new module(self::$moduleID);
			$attr['css'] = $moduleObject->hasCSS();
			$attr['js'] = $moduleObject->hasJS();
		}
		else
		{
			$attr['css'] = Smodule::hasCSS(self::$moduleID);
			$attr['js'] = Smodule::hasJS(self::$moduleID);
		}
		
		// Add resource only of there is js or css
		if ($attr['css'] || $attr['js'])
			return $attr;
		
		return array();
	}
	
	/**
	 * Forms the url according to parameters given.
	 * 
	 * @param	string	$url
	 * 		The base url.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	boolean	$tester
	 * 		Indicator that specifies whether the resource will be loaded from the exported resources or from the repositories.
	 * 		In this function, defines whether the url will be hashed or not.
	 * 
	 * @return	string
	 * 		{description}
	 */
	private static function getURL($url, $library, $package, $tester = FALSE)
	{
		// Set URL Parameters
		$params = array();
		
		if ($tester)
			$params['md'] = $package;
		else
			$params['hs'] = self::getFileName(self::$prefix[$library], $library, $package);
		
		// Resolve URL
		$url = Url::get($url, $params);
		return Url::resolve("www", $url, $https = FALSE);
	}	
	
	/**
	 * Returns the hashed file name of the resource package.
	 * 
	 * @param	string	$prefix
	 * 		The file prefix.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	string
	 * 		{description}
	 */
	private static function getFileName($prefix, $library, $package)
	{
		return $prefix.".".hash("md5", "rsrc_".$library."_".$package);
	}
	
	/**
	 * Get the resource hash id.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	string
	 * 		{description}
	 */
	private static function getRsrcID($library, $package)
	{
		return hash("md5", "rsrc_id_".$library.".".$package);
	}
	
	private static function createIndex($folder, $title = "")
	{
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("hashes", "", $title);
		$parser->append($root);
		
		// Save file
		$parser->save($folder."/index.xml");
	}
}
//#section_end#
?>