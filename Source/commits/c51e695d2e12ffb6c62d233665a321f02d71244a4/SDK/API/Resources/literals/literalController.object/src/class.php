<?php
//#section#[header]
// Namespace
namespace API\Resources\literals;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\literals
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "translator");
importer::import("API", "Resources", "literals::literalManager");

use \API\Profile\translator;
use \API\Resources\literals\literalManager;

/**
 * Literal Controller
 * 
 * A controller class. Chooses where to get the literals from.
 * If a user is translator, use the translator engine.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 14:00 (EEST)
 * @revised	September 16, 2013, 19:36 (EEST)
 */
class literalController extends literalManager
{
	/**
	 * Get all literals of a given scope.
	 * If the user is translator, get the translator's values.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from. If NULL, get the current system locale.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function get($scope, $locale = NULL)
	{
		// Check if user is a translator
		if (translator::status())
			return translator::get($scope);
		else
			return parent::get($scope, $locale);
	}
	
	/**
	 * Wrap a literal to span.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$id
	 * 		The literal's name
	 * 
	 * @param	string	$value
	 * 		The literal's value
	 * 
	 * @param	boolean	$translationLock
	 * 		Defines manually whether a literal is going to be wrapped for translation or not.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public static function wrap($scope, $id, $value, $translationLock = FALSE)
	{
		// Check if user is a translator
		if (translator::status() && !$translationLock)
			return translator::wrap($scope, $id, $value);
		else
			return parent::wrap($value);
	}
}
//#section_end#
?>