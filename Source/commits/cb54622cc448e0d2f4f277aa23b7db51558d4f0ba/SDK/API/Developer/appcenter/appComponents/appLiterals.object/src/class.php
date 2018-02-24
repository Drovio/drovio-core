<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter\appComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\appcenter\appComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Resources", "DOMParser");

use \API\Developer\appcenter\application;
use \API\Resources\filesystem\fileManager;
use \API\Resources\geoloc\locale;
use \API\Resources\DOMParser;

/**
 * Application Literals
 * 
 * Application literal manager.
 * 
 * @version	{empty}
 * @created	October 30, 2013, 20:56 (EET)
 * @revised	April 6, 2014, 21:07 (EEST)
 * 
 * @deprecated	Use \DEV\Apps\components\appLiteral instead.
 */
class appLiterals
{
	/**
	 * The literal file type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "xml";
	
	/**
	 * The application vcs manager.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	/**
	 * The application settings manager.
	 * 
	 * @type	appSettings
	 */
	private $appSettings;
	/**
	 * The application default locale.
	 * 
	 * @type	string
	 */
	private $defaultLocale;
	
	/**
	 * Initializes the object and creates the literal file.
	 * 
	 * @param	vcs	$vcs
	 * 		The application vcs manager object.
	 * 
	 * @param	appSettings	$appSettings
	 * 		The application settings manager object.
	 * 
	 * @return	void
	 */
	public function __construct($vcs, $appSettings)
	{
		// Initialize vcs object
		$this->vcs = $vcs;
		$this->appSettings = $appSettings;
		
		// Set default locale
		$locale = $this->appSettings->get("DEFAULT_LOCALE");
		$locale = (empty($locale) ? locale::getDefault() : $locale);
		$this->defaultLocale = $locale;
		
		// Create file
		$this->create($this->defaultLocale);
	}
	
	/**
	 * Gets the application's default locale.
	 * 
	 * @return	string
	 * 		The locale value.
	 */
	public function getDefaultLocale()
	{
		return $this->defaultLocale;
	}
	
	/**
	 * Creates the literal file.
	 * 
	 * @param	string	$locale
	 * 		The literal locale.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($locale = "")
	{
		// Literals folder
		$locale = (empty($locale) ? $this->defaultLocale : $locale);
		$literalFolder = application::CONTENT_FOLDER."/".$locale;
		$literalFileName = "literals";
		
		// Create vcs item
		$itemID = $this->getItemID($locale);
		$literalsFilePath = $this->vcs->createItem($itemID, $literalFolder, $literalFileName.".".self::FILE_TYPE, $isFolder = FALSE);
		
		// If the file already exists, return FALSE
		if (!$literalsFilePath)
			return FALSE;
		
		// Create file with no contents
		fileManager::create($literalsFilePath, "", TRUE);
		
		// Create contents
		$parser = new DOMParser();
		$root = $parser->create("Content");
		$parser->attr($root, "locale", $locale);
		$parser->append($root);
		return $parser->save($literalsFilePath, "", TRUE);
	}
	
	/**
	 * Gets literal value with the given name and in the given locale.
	 * 
	 * @param	string	$name
	 * 		The literal name.
	 * 
	 * @param	string	$locale
	 * 		The preferred locale.
	 * 
	 * @return	mixed
	 * 		Returns the literal value.
	 * 		If the name is not set, it returns an array of all literals by name => value.
	 */
	public function get($name = "", $locale = "")
	{
		// Get itemID
		$locale = (empty($locale) ? $this->defaultLocale : $locale);
		$itemID = $this->getItemID($locale);
		$litFilePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Load xml
		$parser = new DOMParser();
		$parser->load($litFilePath, FALSE);
		
		// Get literals
		$literals = $parser->evaluate("//literal");
		
		// Form result
		$result = array();
		foreach ($literals as $lit)
			$result[$parser->attr($lit, "name")] = $parser->attr($lit, "value");
		
		// Return the given key's value or all settings
		if (empty($name))
			return $result;
		else
			return $result[$name];
	}
	
	/**
	 * Sets a literal's value in the given locale.
	 * 
	 * @param	string	$name
	 * 		The literal name.
	 * 
	 * @param	string	$value
	 * 		The literal value. If empty, the literal will be deleted.
	 * 
	 * @param	string	$locale
	 * 		The literal locale.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($name, $value = "", $locale = "")
	{
		// Get itemID
		$locale = (empty($locale) ? $this->defaultLocale : $locale);
		$itemID = $this->getItemID($locale);
		$litFilePath = $this->vcs->updateItem($itemID);
		
		// Load xml
		$parser = new DOMParser();
		$parser->load($litFilePath, FALSE);
		
		// Get root
		$root = $parser->evaluate("//Content")->item(0);

		// Get property
		$literal = $parser->evaluate("literal[@name='".$name."']")->item(0);
		if (is_null($literal) && !empty($value))
		{
			$literal = $parser->create("literal");
			$parser->attr($literal, "name", $name);
			$parser->attr($literal, "value", $value);
			$parser->append($root, $literal);
		}
		
		// Delete property if value is null
		if (!is_null($literal) && empty($value))
		{
			$parser->replace($literal, NULL);
			$property = NULL;
		}
		
		// Update property value
		if (!is_null($literal))
			$parser->attr($literal, "value", $value);

		// Update file
		return $parser->update();
	}
	
	/**
	 * Gets the content xml.
	 * 
	 * @param	string	$locale
	 * 		The literal locale. If empty, get the default locale of the application.
	 * 
	 * @return	string
	 * 		The xml file content.
	 */
	public function getXML($locale = "")
	{
		// Get itemID
		$locale = (empty($locale) ? $this->defaultLocale : $locale);
		$itemID = $this->getItemID($locale);
		$litFilePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Load xml
		$parser = new DOMParser();
		$parser->load($litFilePath, FALSE);
		
		// Return content
		return $parser->getXML();
	}
	
	/**
	 * Gets the item's id.
	 * 
	 * @param	string	$locale
	 * 		The current locale.
	 * 
	 * @return	string
	 * 		The item hash id.
	 */
	private function getItemID($locale)
	{
		return "lt".md5("literals_".$locale);
	}
}
//#section_end#
?>