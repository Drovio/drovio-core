<?php
//#section#[header]
// Namespace
namespace API\Resources\literals;

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
 * @package	Resources
 * @namespace	\literals
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "literals::literalController");

use \API\Resources\literals\literalController;

/**
 * Literal Handler
 * 
 * Handles all translated and non-translated literals.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 14:00 (EEST)
 * @revised	September 23, 2013, 12:05 (EEST)
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
	 * Stores all the literals
	 * 
	 * @type	array
	 */
	protected static $literals;
	
	/**
	 * Returns a literal from the dictionary scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @return	mixed
	 * 		An array of literals or the literal value.
	 */
	public static function dictionary($name = "", $wrapped = TRUE)
	{
		return self::get("global.dictionary", $name, $wrapped);
	}
	
	/**
	 * Get a system's literal.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from. If NULL, get the current system locale.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	public static function get($scope, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Normalize scope name
		$scope = str_replace("::", ".", $scope);
		
		// Compatibility
		if (is_bool($attributes))
		{
			$locale = $wrapped;
			$wrapped = $attributes;
			$attributes = array();
		}
		
		// Check if literals are already loaded
		if (!isset(self::$nonTranslatedLiterals[$scope]))
		{
			// Get Literals
			$literals = parent::get($scope, $locale);
			
			// Update Lists
			self::$translatedLiterals[$scope] = $literals['translated'];
			self::$nonTranslatedLiterals[$scope] = $literals['nonTranslated'];
		}
		
		// If name not given, return the translated union the nonTranslatedLiterals of the given scope
		if ($name == "")
		{
			// Get literals separate
			$translated = self::$translatedLiterals[$scope];
			$nonTranslated = self::$nonTranslatedLiterals[$scope];
			
			// Wrap if requested
			if ($wrapped)
			{
				foreach ($translated as $key => $value)
					$translated[$key] = parent::wrap($scope, $key, $value, TRUE);
				
				foreach ($nonTranslated as $key => $value)
					$nonTranslated[$key] = parent::wrap($scope, $key, $value, FALSE);
			}
			
			// Return union
			return $translated + $nonTranslated;
		}
		
		// Set Translation Lock flag
		$translationLock = TRUE;
		
		// Check again
		if (isset(self::$translatedLiterals[$scope][$name]))
		{
			// If it is a tanslated literal, get value and lock translation
			$value = self::$translatedLiterals[$scope][$name];
			$translationLock = TRUE;
		}
		else if (isset(self::$nonTranslatedLiterals[$scope][$name]))
		{
			// If it is a not tanslated literal, get value and unlock translation
			$value = self::$nonTranslatedLiterals[$scope][$name];
			$translationLock = FALSE;
		}
		else
		{
			// Literal doesn't exist, return "N/A" value and lock translation
			$value = "N/A";
			$translationLock = TRUE;
		}
		
		// Pass attributes values to literal
		foreach ($attributes as $key => $attrValue)
			$value = str_replace("{".$key."}", $attrValue, $value);

		if ($wrapped)
			return parent::wrap($scope, $name, $value, $translationLock);
		else
			return $value;
			
	}
	
	/**
	 * Add a new system literal to the default locale.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	string	$value
	 * 		The literal's value
	 * 
	 * @param	string	$description
	 * 		The literal's description
	 * 
	 * @param	boolean	$static
	 * 		Defines whether the literal is static and is translated along with all other static literals in the beginning of a new locale.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function add($scope, $name, $value, $description = "", $static = FALSE)
	{
		// Add the literal
		$status = parent::add($scope, $name, $value, $description, $static);
		
		// Add the literal to static list
		if ($status)
			self::$nonTranslatedLiterals[$scope][$name] = $value;
		
		return $status;
	}
	
	/**
	 * Update a system's literal to the default locale
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	string	$value
	 * 		The literal's new value
	 * 
	 * @param	string	$description
	 * 		The literal's new description
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function update($scope, $name, $value, $description = "")
	{
		$status = parent::update($scope, $name, $value, $description);
		// Add the literal to static list
		if ($status)
			self::$nonTranslatedLiterals[$scope][$name] = $value;
		
		return $status;
	}
	
	/**
	 * Remove a system's literal
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function remove($scope, $name)
	{
		// Remove the literal
		$status = parent::remove($scope, $name);
		
		// Remove from static list
		if ($status)
			unset(self::$nonTranslatedLiterals[$scope][$name]);
		
		return $status;
	}
}
//#section_end#
?>