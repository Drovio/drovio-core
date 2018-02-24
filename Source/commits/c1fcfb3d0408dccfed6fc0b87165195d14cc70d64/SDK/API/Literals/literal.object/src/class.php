<?php
//#section#[header]
// Namespace
namespace API\Literals;

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
 * @package	Literals
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Literals", "literalController");

use \API\Literals\literalController;

/**
 * Literal Handler
 * 
 * Handles all translated and non-translated literals.
 * 
 * @version	{empty}
 * @created	March 21, 2014, 18:03 (EET)
 * @revised	March 21, 2014, 18:03 (EET)
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
	 * Gets the dictionary literals
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
	 * Get a literal.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	array	$attributes
	 * 		An array of attributes to pass to the literal.
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from. If NULL, get the current system locale.
	 * 
	 * @return	mixed
	 * 		The literal span if wrap is requested or the literal value.
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
			// Literal doesn't exist, return empty string and lock translation
			$value = "";
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
	 * Add a new literal to the default locale.
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
	 * 		True on success, false on failure.
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
	 * Update a literal to the default locale
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
	 * 		True on success, false on failure.
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
	 * Remove a literal.
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