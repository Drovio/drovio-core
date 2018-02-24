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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Literals", "literalController");

use \DEV\Literals\literalController;

/**
 * Literal Handler
 * 
 * Handles all translated and non-translated literals.
 * 
 * @version	0.1-3
 * @created	July 21, 2014, 11:23 (EEST)
 * @revised	July 23, 2014, 15:23 (EEST)
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
	 */
	public static function get($projectID, $scope, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Normalize scope name
		$scope = str_replace("::", ".", $scope);
		
		// Check if literals are already loaded
		if (!isset(self::$nonTranslatedLiterals[$projectID][$scope]))
		{
			// Get Literals
			$literals = parent::get($projectID, $scope, $locale);
			
			// Update Lists
			self::$translatedLiterals[$projectID][$scope] = $literals['translated'];
			self::$nonTranslatedLiterals[$projectID][$scope] = $literals['nonTranslated'];
		}
		
		// If name not given, return the translated union the nonTranslatedLiterals of the given scope
		if ($name == "")
		{
			// Get literals separate
			$translated = self::$translatedLiterals[$projectID][$scope];
			$nonTranslated = self::$nonTranslatedLiterals[$projectID][$scope];
			
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
		if (isset(self::$translatedLiterals[$projectID][$scope][$name]))
		{
			// If it is a tanslated literal, get value and lock translation
			$value = self::$translatedLiterals[$projectID][$scope][$name];
			$translationLock = TRUE;
		}
		else if (isset(self::$nonTranslatedLiterals[$projectID][$scope][$name]))
		{
			// If it is a not tanslated literal, get value and unlock translation
			$value = self::$nonTranslatedLiterals[$projectID][$scope][$name];
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