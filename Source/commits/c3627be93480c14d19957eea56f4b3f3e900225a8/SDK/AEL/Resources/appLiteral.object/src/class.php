<?php
//#section#[header]
// Namespace
namespace AEL\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Resources
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Developer", "appcenter::appPlayer");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "geoloc::locale");

use \API\Developer\appcenter\application;
use \API\Developer\appcenter\appPlayer;
use \API\Resources\DOMParser;
use \API\Resources\geoloc\locale;

/**
 * Application Literal
 * 
 * Application Literal Getter
 * 
 * @version	{empty}
 * @created	January 9, 2014, 20:21 (EET)
 * @revised	January 9, 2014, 20:21 (EET)
 */
class appLiteral
{
	/**
	 * The application literals.
	 * 
	 * @type	array
	 */
	private static $literals = "";
	
	/**
	 * Get one or all literals from the application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$literalName
	 * 		The literal name.
	 * 		If empty, all literals will be returned.
	 * 
	 * @param	string	$locale
	 * 		The desired locale.
	 * 		If empty, get the default application locale.
	 * 
	 * @return	mixed
	 * 		Array of literal values or the literal value.
	 */
	public static function get($appID, $literalName = "", $locale = "")
	{
		// Load literals
		if (empty(self::$literals))
			self::loadLiterals($appID, $locale);

		// Return the given key's value or all settings
		if (empty($literalName))
			return self::$literals;
		else
		{
			if (!isset(self::$literals[$literalName]))
			{
				$appSettings = appPlayer::getAppSettings($appID);
				$defaultLocale = $appSettings->get("DEFAULT_LOCALE");
				return self::get($appID, $literalName, $defaultLocale);
			}
			
			return self::$literals[$literalName];
		}
	}
	
	/**
	 * Load the literals from the application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$locale
	 * 		The desired locale.
	 * 		If empty, get the default application locale.
	 * 
	 * @return	void
	 */
	private static function loadLiterals($appID, $locale = "")
	{
		// Get application path
		$locale = (empty($locale) ? locale::get() : $locale);
		$litFilePath = appPlayer::getApplicationRunPath($appID)."/content/".$locale."/literals.xml";
		
		// Load xml
		$parser = new DOMParser();
		self::$literals = array();
		try
		{
			$parser->load($litFilePath);
		}
		catch (Exception $ex)
		{
			return;
		}
		
		// Get literals
		$literals = $parser->evaluate("//literal");
		
		// Form result
		foreach ($literals as $lit)
			self::$literals[$parser->attr($lit, "name")] = $parser->attr($lit, "value");
	}
}
//#section_end#
?>