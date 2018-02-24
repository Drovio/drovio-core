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

importer::import("API", "Profile", "translator");
importer::import("API", "Literals", "literalManager");

use \API\Profile\translator;
use \API\Literals\literalManager;

/**
 * Literal Controller
 * 
 * A controller class. Chooses where to get the literals from.
 * If a user is translator, use the translator engine.
 * 
 * @version	{empty}
 * @created	March 21, 2014, 17:56 (EET)
 * @revised	March 21, 2014, 17:56 (EET)
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
	 * 		The locale to get the literals from.
	 * 		If NULL, get the current system locale.
	 * 
	 * @return	array
	 * 		An array of all literals as defined in the literalManager or in the translator class.
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
	 * 		The literal span.
	 * 		With translation attributes in case of translator.
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