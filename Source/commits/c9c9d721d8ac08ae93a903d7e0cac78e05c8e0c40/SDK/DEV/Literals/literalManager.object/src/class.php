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
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Geoloc", "locale");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Profiler", "logger");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Geoloc\locale;
use \UI\Html\DOM;
use \DEV\Profiler\logger;

/**
 * Project's Literal Manager
 * 
 * This class is the base responsible class for handling project literals.
 * It provides interface for getting, adding, removing and updating literals and scopes.
 * 
 * @version	4.0-2
 * @created	July 21, 2014, 10:57 (EEST)
 * @revised	January 2, 2015, 10:21 (EET)
 */
class literalManager
{
	/**
	 * Get all literals from the given scope.
	 * It includes all scope's literals, translated (in the current system locale) and not (in the default system locale).
	 * 
	 * @param	integer	$projectID
	 * 		The project id to get the literals from.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope to get the literals from.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from.
	 * 		If NULL, get the current system locale.
	 * 		It is NULL by default.
	 * 
	 * @return	array
	 * 		An array of all literals in the requested locale.
	 * 		They are separated in 'translated' and 'nonTranslated' groups nested in the array.
	 */
	public static function get($projectID, $scope, $locale = NULL)
	{
		// Log activity
		logger::log("Loading literals from '".$scope."' scope ...", logger::INFO);
		
		$dbq = new dbQuery("32730791617727", "literals");
		$dbc = new dbConnection();
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		
		// Get Literals in the default locale
		$attr['locale'] = locale::getDefault();
		$defaultResult = $dbc->execute($dbq, $attr);
		
		$defaultArray = array();
		$defaultArray = $dbc->toArray($defaultResult, "name", "value");
		
		// Get Literals in the requested locale
		$locale = (empty($locale) ? locale::get() : $locale);
		$attr['locale'] = $locale;
		$currentResult = $dbc->execute($dbq, $attr);
		
		$currentArray = array();
		$currentArray = $dbc->toArray($currentResult, "name", "value");
		
		$literalsArray = array();
		$literalsArray['translated'] = $currentArray;
		$literalsArray['nonTranslated'] = $defaultArray;
		
		return $literalsArray;
	}
	
	/**
	 * Create a new literal scope in the project.
	 * 
	 * @param	integer	$projectID
	 * 		The scope's project id.
	 * 
	 * @param	string	$scope
	 * 		The scope name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createScope($projectID, $scope)
	{
		// Get dbQuery
		$dbc = new dbConnection();
		$dbq = new dbQuery("29548350020937", "literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get all project's literal scopes.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to get the scopes for.
	 * 
	 * @return	array
	 * 		An array of all scopes.
	 */
	public static function getScopes($projectID)
	{
		// Get dbQuery
		$dbc = new dbConnection();
		$dbq = new dbQuery("32769441955897", "literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Remove a literal scope from the project.
	 * 
	 * @param	integer	$projectID
	 * 		The scope's project id.
	 * 
	 * @param	string	$scope
	 * 		The scope name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function removeScope($projectID, $scope)
	{
		// Get dbQuery
		$dbc = new dbConnection();
		$dbq = new dbQuery("14605698629585", "literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Update the name of a literal scope.
	 * 
	 * @param	integer	$projectID
	 * 		The scope's project id.
	 * 
	 * @param	string	$scope
	 * 		The scope name to update.
	 * 
	 * @param	string	$newScope
	 * 		The new scope name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateScope($projectID, $scope, $newScope)
	{
		// Get dbQuery
		$dbc = new dbConnection();
		$dbq = new dbQuery("33209892482754", "literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['newScope'] = $newScope;
		return $dbc->execute($dbq, $attr);
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
	protected static function add($projectID, $scope, $name, $value, $description = "")
	{
		// Get dbQuery
		$dbc = new dbConnection();
		$dbq = new dbQuery("32313828833505", "literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['desc'] = $description;
		
		// Literal Value
		$attr['locale'] = locale::getDefault();
		$attr['value'] = $value;
		
		// Add
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Update literal's value to the default locale.
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
	 * 		The literal's new value
	 * 
	 * @param	string	$description
	 * 		The literal's new description
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	protected static function update($projectID, $scope, $name, $value, $description = "")
	{
		// Get dbQuery
		$dbc = new dbConnection();
		$dbq = new dbQuery("17153772644024", "literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['desc'] = $description;
		
		// Literal Value
		$attr['locale'] = locale::getDefault();
		$attr['value'] = $value;

		// Update
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Remove a literal from the system.
	 * It removes the literal and all its translations.
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
	protected static function remove($projectID, $scope, $name)
	{
		// Get dbQuery
		$dbc = new dbConnection();
		$dbq = new dbQuery("19979312464863", "literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		
		// Remove
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get information about a given literal.
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
	 * @return	array
	 * 		The literal information in array.
	 */
	public static function info($projectID, $scope, $name)
	{
		// Get dbQuery
		$dbq = new dbQuery("32730791617727", "literals");
		$dbc = new dbConnection();
		
		// Get literals in the default locale
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['locale'] = locale::getDefault();
		$result = $dbc->execute($dbq, $attr);
		
		// Return requested literal
		while ($row = $dbc->fetch($result))
			if ($row['name'] == $name)
				return $row;
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
	protected static function wrap($value)
	{
		$span = DOM::create("span");
		DOM::innerHTML($span, $value);
		
		return $span;
	}
}
//#section_end#
?>