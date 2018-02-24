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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Literals", "literal");

use \DEV\Literals\literal as DEVLiteral;

/**
 * Literal Handler
 * 
 * Handles all translated and non-translated literals.
 * 
 * @version	2.0-1
 * @created	March 21, 2014, 18:03 (EET)
 * @revised	July 21, 2014, 13:08 (EEST)
 */
class literal extends DEVLiteral
{
	/**
	 * Get a dictionary literal.
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @return	mixed
	 * 		The literal span if wrap is requested or the literal value.
	 */
	public static function dictionary($name = "", $wrapped = TRUE)
	{
		$projectID = 1;
		return parent::get($projectID, "global.dictionary", $name, array(), $wrapped);
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
		$projectID = 1;
		return parent::get($projectID, $scope, $name, $attributes, $wrapped, $locale);
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function add($scope, $name, $value, $description = "")
	{
		// Add the literal
		$projectID = 1;
		return parent::add($projectID, $scope, $name, $value, $description);
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
		$projectID = 1;
		return parent::update($projectID, $scope, $name, $value, $description);
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
		$projectID = 1;
		return parent::remove($projectID, $scope, $name);
	}
}
//#section_end#
?>