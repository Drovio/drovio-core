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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Literals", "literal");

use \DEV\Literals\literal;

/**
 * Application Literal Manager
 * 
 * Manages all literals for the application that is currently running.
 * 
 * It is used only by the applications.
 * It is application-protected, not to allow cross-application flow of information.
 * 
 * @version	0.1-1
 * @created	January 9, 2014, 20:21 (EET)
 * @revised	July 21, 2014, 13:31 (EEST)
 */
class appLiteral extends literal
{
	/**
	 * The inited application id.
	 * 
	 * @type	string
	 */
	private static $applicationID = NULL;
	
	/**
	 * Init the literal manager for the application that is currently running.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id currently running.
	 * 		
	 * 		NOTE: To application developers, this will be set only once the first time and cannot be changed after.
	 * 
	 * @return	void
	 */
	public static function init($applicationID)
	{
		// Set the application id if not set before
		if (!isset(self::$applicationID))
			self::$applicationID = $applicationID;
			
		// Return the current application id (project id)
		return self::$applicationID;
	}
	/**
	 * Get one or all literals from the application.
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
	 * 		The locale to get the literals from. If NULL, get the default application locale.
	 * 
	 * @return	mixed
	 * 		The literal's value as a string or DOMElement, depending on $wrapped parameter.
	 * 		Also returns an array of all literals in the default locale, if only the module's id is specified.
	 */
	public static function get($scope, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Return literal from the application project
		return parent::get(self::$applicationID, $scope, $name, $attributes, $wrapped, $locale);
	}
	
	/**
	 * Create a new application literal.
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
	public static function add($scope, $name, $value, $description = "")
	{
		return parent::add(self::$applicationID, $scope, $name, $value, $description);
	}
	
	/**
	 * Update an application's literal to the default application's locale.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
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
		return parent::update(self::$applicationID, $scope, $name, $value, $description);
	}
	
	/**
	 * Remove an application's literal.
	 * 
	 * @param	string	$name
	 * 		The literal's name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($name)
	{
		return parent::remove(self::$applicationID, $scope, $name);
	}
	
	/**
	 * Create a new application's literal scope.
	 * 
	 * @param	string	$scope
	 * 		The scope name to create.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createScope($scope)
	{
		return parent::createScope(self::$applicationID, $scope);
	}
}
//#section_end#
?>