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

importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Resources", "geoloc::locale");
importer::import("UI", "Html", "DOM");

use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\profiler\logger;
use \API\Resources\geoloc\locale;
use \UI\Html\DOM;

/**
 * Literal Manager
 * 
 * System's literal manager.
 * Manages the get and the set of all literals.
 * 
 * @version	{empty}
 * @created	March 21, 2014, 17:52 (EET)
 * @revised	March 21, 2014, 17:52 (EET)
 */
class literalManager
{
	/**
	 * Get all literals from the given scope
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$locale
	 * 		The locale to get the literals from.
	 * 		If NULL, get the current system locale.
	 * 
	 * @return	array
	 * 		An array of all literals in the requested locale.
	 * 		They are separated in 'translated' and 'nonTranslated' groups nested in the array.
	 */
	public static function get($scope, $locale = NULL)
	{
		// Log activity
		logger::log("Loading literals from '".$scope."' scope ...", logger::INFO);
		
		$dbq = new dbQuery("1169740592", "resources.literals");
		$dbc = new interDbConnection();
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		
		// Get Literals in the default locale
		$attr['locale'] = locale::getDefault();
		$defaultResult = $dbc->execute($dbq, $attr);
		
		$defaultArray = array();
		$defaultArray = $dbc->toArray($defaultResult, "name", "value");
		
		// Get Literals in the requested locale
		$attr['locale'] = (empty($locale) ? locale::get() : $locale);
		$currentResult = $dbc->execute($dbq, $attr);
		
		$currentArray = array();
		$currentArray = $dbc->toArray($currentResult, "name", "value");
		
		$literalsArray = array();
		$literalsArray['translated'] = $currentArray;
		$literalsArray['nonTranslated'] = $defaultArray;
		
		return $literalsArray;
	}
	
	/**
	 * Add a new literal to the default locale.
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
	 * @param	boolean	$static
	 * 		Defines whether the literal is static and is translated along with all other static literals in the beginning of a new locale.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	protected static function add($scope, $name, $value, $description = "", $static = FALSE)
	{
		// Get dbQuery
		$dbc = new interDbConnection();
		$dbq = new dbQuery("431155169", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['desc'] = $description;
		$attr['static'] = ($static ? 1 : 0);
		
		// Literal Value
		$attr['locale'] = locale::getDefault();
		$attr['value'] = $value;
		
		// Add
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Update literal's value to the default locale.
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
	protected static function update($scope, $name, $value, $description = "")
	{
		// Get dbQuery
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1307209869", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
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
	 * Remove a literal from the system
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	protected static function remove($scope, $name)
	{
		// Get dbQuery
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1684473069", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		
		// Remove
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Wrap the literal into a simple span.
	 * 
	 * @param	string	$value
	 * 		The literal's value.
	 * 
	 * @return	DOMElement
	 * 		The literal span.
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