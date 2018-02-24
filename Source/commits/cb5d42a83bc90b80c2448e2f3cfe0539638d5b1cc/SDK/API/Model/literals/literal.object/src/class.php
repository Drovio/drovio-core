<?php
//#section#[header]
// Namespace
namespace API\Model\literals;

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
 * @package	Model
 * @namespace	\literals
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Profile", "translator");
importer::import("DEV", "Literals", "literal");
importer::import("DEV", "Projects", "projectLibrary");

importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Modules", "test/moduleTester");

use \ESS\Environment\url;
use \API\Geoloc\locale;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \UI\Html\DOM;
use \DEV\Profile\translator;
use \DEV\Literals\literal as DEVLiteral;
use \DEV\Projects\projectLibrary;

use \DEV\Core\coreProject;
use \DEV\Modules\modulesProject;
use \DEV\Modules\test\moduleTester;

/**
 * Project literal getter
 * 
 * This class is responsible for getting project literals from production.
 * If translator is active, it will get literals from development.
 * 
 * @version	0.2-1
 * @created	July 6, 2015, 11:02 (EEST)
 * @updated	September 13, 2015, 1:31 (EEST)
 */
class literal extends DEVLiteral
{
	/**
	 * Cached production literals.
	 * 
	 * @type	array
	 */
	private static $production_literals;
	
	/**
	 * Get a literal value.
	 * If translator is active or we are on development environment, the literal will be fetched from development.
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
		// Check if user is a translator or it is a dev environment
		if (translator::status() || self::onDEV($projectID))
			return parent::get($projectID, $scope, $name, $attributes, $wrapped, $locale);
		else
			return self::getLiteral($projectID, $scope, $name, $attributes, $wrapped, $locale);
	}
	
	/**
	 * Get a literal value from production library.
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
	 * @return	void
	 */
	private static function getLiteral($projectID, $scope, $name = "", $attributes = array(), $wrapped = TRUE, $locale = NULL)
	{
		// Normalize locale
		$locale = (empty($locale) ? locale::get() : $locale);
		
		// Check cache
		if (isset(self::$production_literals[$projectID][$scope][$locale]))
		{
			// Check literal name
			if (empty($name))
				return self::$production_literals[$projectID][$scope][$locale];
			else
				$value = self::$production_literals[$projectID][$scope][$locale][$name];
		}
		else
		{
			// Get project publish path
			$version = projectLibrary::getLastProjectVersion($projectID, $live = FALSE);
			$publishFolder = projectLibrary::getPublishedPath($projectID, $version);
			$literalsFolder = $publishFolder."/".projectLibrary::LT_FOLDER;
			
			// COMPATIBILITY, for projects not published literals yet
			if (!file_exists(systemRoot.$literalsFolder."/".$locale."/".$scope.".json"))
				return parent::get($projectID, $scope, $name, $attributes, $wrapped, $locale);
			
			// Get literals json from file
			$literals_json = fileManager::get(systemRoot.$literalsFolder."/".$locale."/".$scope.".json");
			
			// Decode and update cache
			self::$production_literals[$projectID][$scope][$locale] = json_decode($literals_json, TRUE);
			if (empty($name))
				return self::$production_literals[$projectID][$scope][$locale];
			else
				$value = self::$production_literals[$projectID][$scope][$locale][$name];
		}
		
		// Pass attributes values to literal
		foreach ($attributes as $key => $attrValue)
		{
			$value = str_replace("%{".$key."}", $attrValue, $value);
			$value = str_replace("{".$key."}", $attrValue, $value);
		}
		
		if ($wrapped)
			return self::wrap($value);
		else
			return $value;
	}
	
	/**
	 * Wrap the literal into a simple span.
	 * 
	 * @param	string	$value
	 * 		The literal's value.
	 * 
	 * @return	DOMElement
	 * 		The literal span element.
	 */
	public static function wrap($value)
	{
		$span = DOM::create("span");
		DOM::innerHTML($span, $value);
		
		return $span;
	}
	
	/**
	 * Check whether we are on the Development Environment.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to check if tester as additional dev check.
	 * 
	 * @return	boolean
	 * 		True if we are on DEV, false otherwise.
	 */
	private static function onDEV($projectID)
	{
		// Get subdomain where the application is running
		$subdomain = url::getSubDomain();
		
		// Check for project testing
		$tester = FALSE;
		switch ($projectID)
		{
			case coreProject::PROJECT_ID:
				$tester = FALSE;
				break;
			case modulesProject::PROJECT_ID:
				$tester = moduleTester::status();
				break;
		}
		
		// Check if it is on Development Environment
		return ($subdomain == "developers" || $tester);
	}
}
//#section_end#
?>