<?php
//#section#[header]
// Namespace
namespace AEL\Literals;

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
 * @package	Literals
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("API", "Model", "literals/literal");
importer::import("DEV", "Core", "coreProject");

use \AEL\Platform\application;
use \API\Model\literals\literal;
use \DEV\Core\coreProject;

/**
 * Application Literal Manager
 * 
 * {description}
 * 
 * @version	2.0-4
 * @created	August 23, 2014, 14:06 (BST)
 * @updated	November 1, 2015, 16:01 (GMT)
 */
class appLiteral extends literal
{
	/**
	 * Get a dictionary literal.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @param	boolean	$wrapped
	 * 		Whether the literal will be wrapped inside a span.
	 * 
	 * @return	mixed
	 * 		The literal span if wrap is requested or the literal value.
	 */
	public static function dictionary($name = "", $wrapped = TRUE)
	{
		return parent::get(coreProject::PROJECT_ID, "global.dictionary", $name, array(), $wrapped);
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
	 * 		Whether the literal will be wrapped inside a span for translation.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from. If NULL, get the default application/system locale.
	 * 
	 * @return	mixed
	 * 		The literal's value as a string or DOMElement, depending on $wrapped parameter.
	 * 		Also returns an array of all literals in the default locale, if only the module's id is specified.
	 */
	public static function get($scope, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Return literal from the application project
		$appID = application::init();
		$appVersion = application::getApplicationVersion();
		return parent::get($appID, $scope, $name, $attributes, $wrapped, $locale, $appVersion);
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
		$appID = application::init();
		return parent::add($appID, $scope, $name, $value, $description);
	}
	
	/**
	 * Update an application's literal to the default application's/system's locale.
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
		$appID = application::init();
		return parent::update($appID, $scope, $name, $value, $description);
	}
	
	/**
	 * Remove an application's literal.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($scope, $name)
	{
		$appID = application::init();
		return parent::remove($appID, $scope, $name);
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
		$appID = application::init();
		return parent::createScope($appID, $scope);
	}
	
	/**
	 * Get all application literal scopes.
	 * 
	 * @return	array
	 * 		An array of all scopes.
	 */
	public static function getScopes()
	{
		$appID = application::init();
		return parent::getScopes($appID);
	}
	
	/**
	 * Remove an application literal scope.
	 * 
	 * @param	string	$scope
	 * 		The literal scope to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function removeScope($scope)
	{
		$appID = application::init();
		return parent::removeScope($appID, $scope);
	}
	
	/**
	 * Get information about an application literal.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @return	array
	 * 		Literal information like scope, name and description.
	 */
	public static function info($scope, $name)
	{
		$appID = application::init();
		return parent::info($appID, $scope, $name);
	}
}
//#section_end#
?>