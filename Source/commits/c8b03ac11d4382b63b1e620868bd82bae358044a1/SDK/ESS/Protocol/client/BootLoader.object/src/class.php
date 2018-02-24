<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
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

use \ESS\Protocol\client\environment\Url;
use \API\Developer\components\units\modules\module;
use \API\Developer\components\sdk\sdkPackage;
use \API\Model\units\modules\Smodule;
use \API\Profile\tester;
use \API\Resources\filesystem\fileManager;

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
	public static function getCSSUrl($libName, $packageName, $tester = FALSE)
	{
		return self::getURL(self::URL_CSS_LOADER, $libName, $packageName, $tester);
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
	public static function getJSUrl($libName, $packageName, $tester = FALSE)
	{
		return self::getURL(self::URL_JS_LOADER, $libName, $packageName, $tester);
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
	public static function loadFriendCSS($category, $libName, $packageName)
	{
		$id = self::getFileName(self::$prefix[$category], $libName, $packageName);
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
	public static function loadFriendJS($category, $libName, $packageName)
	{
		$id = self::getFileName(self::$prefix[$category], $libName, $packageName);
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
	public static function exportCSS($category, $libName, $packageName, $content)
	{
		// Form exported filename
		$file = systemRoot."/System/Library/CSS/".$category."/".self::getFileName(self::$prefix[$category], $libName, $packageName).".css";

		// Create file
		if (!empty($content))
			return fileManager::create($file, $content, TRUE);
		
		return TRUE;
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
	public static function exportJS($category, $libName, $packageName, $content)
	{
		// Form exported filename
		$file = systemRoot."/System/Library/JS/".$category."/".self::getFileName(self::$prefix[$category], $libName, $packageName).".js";
		
		// Create file
		if (!empty($content))
			return fileManager::create($file, $content, TRUE);
		
		return TRUE;
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
			$attr['md'] = self::$moduleID;
		else
			$attr['hs'] = self::getFileName(self::$prefix["Modules"], "Modules", self::$moduleID);
		
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
	private static function getURL($url, $libName, $packageName, $tester = FALSE)
	{
		// Set URL Parameters
		$params = array();
		
		if ($tester)
			$params['md'] = $packageName;
		else
			$params['hs'] = self::getFileName(self::$prefix[$libName], $libName, $packageName);
		
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
	private static function getFileName($prefix, $libName, $packageName)
	{
		return $prefix.".".hash("md5", "rsrc_".$libName."_".$packageName);
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
	private static function getRsrcID($libName, $packageName)
	{
		return hash("md5", "rsrc_id_".$libName.".".$packageName);
	}
}
//#section_end#
?>