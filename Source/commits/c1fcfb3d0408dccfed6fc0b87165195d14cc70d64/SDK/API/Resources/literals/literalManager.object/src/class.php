<?php
//#section#[header]
// Namespace
namespace API\Resources\literals;

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
 * @package	Resources
 * @namespace	\literals
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
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:59 (EEST)
 * @revised	March 24, 2014, 10:04 (EET)
 * 
 * @deprecated	Use \API\Literals\literalManager instead.
 */
class literalManager
{
	/**
	 * Get all locked literals from the given scope
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
		while ($row = $dbc->fetch($defaultResult))
			$defaultArray[$row['name']] = $row['value'];
		
		// Get Literals in the requested locale
		$requestedLocale = (empty($locale) ? locale::get() : $locale);
		$attr['locale'] = $requestedLocale;
		$currentResult = $dbc->execute($dbq, $attr);
		
		$currentArray = array();
		while ($row = $dbc->fetch($currentResult))
			$currentArray[$row['name']] = $row['value'];
		
		// Get the array union
		//return $currentArray + $defaultArray;
		
		$literalsArray = array();
		$literalsArray['translated'] = $currentArray;
		$literalsArray['nonTranslated'] = $defaultArray;
		
		return $literalsArray;
	}
	
	/**
	 * Add a new literal to the default locale
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
	 * @param	boolean	$static
	 * 		Defines whether the literal is static and is translated along with all other static literals in the beginning of a new locale.
	 * 
	 * @return	void
	 */
	protected static function add($scope, $name, $value, $description = "", $static = FALSE)
	{
		// Log activity
		logger::log("Adding literal [".$scope."][".$name."] <= [".$value."][".$description."] ...");
		
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
		//_____ default system locale
		$attr['locale'] = locale::getDefault();
		$attr['value'] = $value;
		
		// Add
		$result = $dbc->execute($dbq, $attr);

		return $result;
	}
	
	/**
	 * Update literal's values to the default locale
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
	 * @return	mixed
	 * 		{description}
	 */
	protected static function update($scope, $name, $value, $description = "")
	{
		// Log activity
		logger::log("Updating literal [".$scope."][".$name."] <= [".$value."][".$description."] ...");
		
		// Get dbQuery
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1307209869", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['desc'] = $description;
		// Literal Value
		//_____ default system locale
		$attr['locale'] = locale::getDefault();
		$attr['value'] = $value;

		// Update
		$result = $dbc->execute($dbq, $attr);

		return $result;
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
	 * @return	mixed
	 * 		{description}
	 */
	protected static function remove($scope, $name)
	{
		// Log activity
		logger::log("Removing literal [".$scope."][".$name."] ...");
		
		// Get dbQuery
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1684473069", "resources.literals");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		
		// Remove
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Wrap a simple span literal.
	 * 
	 * @param	string	$value
	 * 		The literal's value.
	 * 
	 * @return	DOMElement
	 * 		{description}
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