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

importer::import("API", "Profile", "translator");
importer::import("DEV", "Literals", "literalManager");
importer::import("DEV", "Literals", "translator");

use \API\Profile\translator;
use \DEV\Literals\literalManager;
use \DEV\Literals\translator as literalTranslator;

/**
 * Project Literal Controller
 * 
 * This is a controller class. Chooses where to get the literals from.
 * If the account is in the translator group and the translations are active, use the translator engine.
 * 
 * @version	0.1-1
 * @created	July 21, 2014, 11:06 (EEST)
 * @revised	July 21, 2014, 11:06 (EEST)
 */
class literalController extends literalManager
{
	/**
	 * Get all literals of a given project's scope.
	 * If the account is in the translator group and the translator is active, get the translator's values.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from.
	 * 		If NULL, get the current system locale.
	 * 		NULL by default.
	 * 
	 * @return	array
	 * 		An array of all literals as defined in the literalManager or in the translator class.
	 */
	public static function get($projectID, $scope, $locale = NULL)
	{
		// Check if user is a translator
		if (translator::status())
			return literalTranslator::get($projectID, $scope);
		else
			return parent::get($projectID, $scope, $locale);
	}
	
	/**
	 * Wrap a literal to span.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
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
	public static function wrap($projectID, $scope, $name, $value, $translationLock = FALSE)
	{
		// Check if user is a translator
		if (translator::status() && !$translationLock)
			return literalTranslator::wrap($projectID, $scope, $name, $value);
		else
			return parent::wrap($value);
	}
}
//#section_end#
?>