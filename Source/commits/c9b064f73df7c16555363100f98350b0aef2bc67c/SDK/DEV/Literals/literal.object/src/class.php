<?php
//#section#[header]
// Namespace
namespace DEV\Literals;

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
 * @package	Literals
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Geoloc", "locale");
importer::import("DEV", "Literals", "literalController");

use \API\Geoloc\locale;
use \DEV\Literals\literalController;

/**
 * Literal Handler
 * 
 * Handles all translated and non-translated literals.
 * 
 * @version	0.1-6
 * @created	July 21, 2014, 11:23 (EEST)
 * @updated	July 6, 2015, 10:15 (EEST)
 */
class literal extends literalController
{
	/**
	 * Stores the translated literals in the language requested
	 * 
	 * @type	array
	 */
	protected static $translatedLiterals;
	/**
	 * Stores the non translated literals in the language requested
	 * 
	 * @type	array
	 */
	protected static $nonTranslatedLiterals;
	
	/**
	 * Get a literal value.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @param	array	$attributes
	 * 		An array of attributes to pass to the literal.
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from.
	 * 		If NULL, get the current system locale.
	 * 		NULL by default.
	 * 
	 * @return	mixed
	 * 		The literal span if wrap is requested or the literal value.
	 * 		It will return NULL if there is no literal with given name in the given scope.
	 */
	public static function get($projectID, $scope, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Normalize scope name and locale
		$scope = str_replace("::", ".", $scope);
		$locale = (empty($locale) ? locale::get() : $locale);
		
		// Check if literals are already loaded
		if (!isset(self::$nonTranslatedLiterals[$projectID][$scope][$locale]))
		{
			// Get Literals
			$literals = parent::get($projectID, $scope, $locale);
			
			// Update Lists
			self::$translatedLiterals[$projectID][$scope][$locale] = $literals['translated'];
			self::$nonTranslatedLiterals[$projectID][$scope][$locale] = $literals['nonTranslated'];
		}
		
		// If name not given, return the translated union the nonTranslatedLiterals of the given scope
		if ($name == "")
		{
			// Get literals separate
			$translated = self::$translatedLiterals[$projectID][$scope][$locale];
			$nonTranslated = self::$nonTranslatedLiterals[$projectID][$scope][$locale];
			
			// Wrap if requested
			if ($wrapped)
			{
				foreach ($translated as $pID => $values)
					foreach ($values as $name => $value)
						$translated[$name] = parent::wrap($projectID, $scope, $name, $value, TRUE);
				
				foreach ($nonTranslated as $pID => $values)
					foreach ($values as $name => $value)
						$nonTranslated[$name] = parent::wrap($projectID, $scope, $name, $value, FALSE);
			}
			
			// Return union
			return $translated + $nonTranslated;
		}
		
		// Set Translation Lock flag
		$translationLock = TRUE;
		
		// Check again
		if (isset(self::$translatedLiterals[$projectID][$scope][$locale][$name]))
		{
			// If it is a tanslated literal, get value and lock translation
			$value = self::$translatedLiterals[$projectID][$scope][$locale][$name];
			$translationLock = TRUE;
		}
		else if (isset(self::$nonTranslatedLiterals[$projectID][$scope][$locale][$name]))
		{
			// If it is a not tanslated literal, get value and unlock translation
			$value = self::$nonTranslatedLiterals[$projectID][$scope][$locale][$name];
			$translationLock = FALSE;
		}
		else
		{
			// Literal doesn't exist, return empty string and lock translation
			$value = "";
			$translationLock = TRUE;
		}
		
		// Check if literal value is empty
		if (empty($value))
			return NULL;
		
		// Pass attributes values to literal
		foreach ($attributes as $key => $attrValue)
		{
			$value = str_replace("%{".$key."}", $attrValue, $value);
			$value = str_replace("{".$key."}", $attrValue, $value);
		}

		if ($wrapped)
			return parent::wrap($projectID, $scope, $name, $value, $translationLock);
		else
			return $value;
			
	}
	
	/**
	 * Add a new literal to the default locale.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @param	string	$value
	 * 		The literal's value.
	 * 
	 * @param	string	$description
	 * 		The literal's description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function add($projectID, $scope, $name, $value, $description = "")
	{
		// Add the literal
		$status = parent::add($projectID, $scope, $name, $value, $description);
		
		// Add the literal to static list
		if ($status)
			self::$translatedLiterals[$projectID][$scope][$name] = $value;
		
		return $status;
	}
	
	/**
	 * Update a literal to the default locale.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @param	string	$value
	 * 		The literal's new value.
	 * 
	 * @param	string	$description
	 * 		The literal's new description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($projectID, $scope, $name, $value, $description = "")
	{
		$status = parent::update($projectID, $scope, $name, $value, $description);
		// Add the literal to static list
		if ($status)
			self::$translatedLiterals[$projectID][$scope][$name] = $value;
		
		return $status;
	}
	
	/**
	 * Remove a literal.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($projectID, $scope, $name)
	{
		// Remove the literal
		$status = parent::remove($projectID, $scope, $name);
		
		// Remove from static list
		if ($status)
		{
			unset(self::$translatedLiterals[$projectID][$scope][$name]);
			unset(self::$nonTranslatedLiterals[$projectID][$scope][$name]);
		}
		
		return $status;
	}
}
//#section_end#
?>