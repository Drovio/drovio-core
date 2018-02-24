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

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Literals", "literalManager");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "DOM");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\profiler\logger;
use \API\Model\units\sql\dbQuery;
use \API\Resources\geoloc\locale;
use \API\Literals\literalManager;
use \API\Security\account;
use \UI\Html\DOM;

/**
 * Literal Translator
 * 
 * System's literal translation engine.
 * 
 * @version	{empty}
 * @created	March 24, 2014, 9:26 (EET)
 * @revised	March 24, 2014, 9:26 (EET)
 */
class translator extends literalManager
{
	/**
	 * The translator's locale
	 * 
	 * @type	string
	 */
	private static $locale;
	/**
	 * The translator status
	 * 
	 * @type	boolean
	 */
	private static $status;
	
	/**
	 * Gets the initial values for every literal and adds the translations for those that have a translation.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @return	array
	 * 		Returns an array of all translated and non translated literals as 'translated' and 'nonTranslated'.
	 */
	public static function get($scope)
	{
		// Log activity
		logger::log("Getting translator literals from '".$scope."' scope ...", logger::INFO);
		
		// Get Translated and not translated literals
		$initials = parent::get($scope);
		
		// Get translated literals
		$dbc = new interDbConnection();
		$dbq = new dbQuery("169173212", "resources.literals.translator");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['locale'] = locale::get();
		
		// Execute Query
		$result = $dbc->execute($dbq, $attr);
		
		// Translations Array
		$translations = array();
		if ($result)
			while ($row = $dbc->fetch($result))
				$translations[$row['name']] = $row['value'];
		
		// Merge default values with under translation values (those literals need translation)
		$initials['nonTranslated'] = $initials['nonTranslated'] + $translations;
		
		return $initials;
	}
	
	/**
	 * Create a translation of a literal to the translator's locale.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$name
	 * 		The literal's name
	 * 
	 * @param	string	$value
	 * 		The literal's translated value
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function translate($scope, $name, $value)
	{
		// Log activity
		logger::log("Translating literal [".$scope."][".$name."] <= [".$value."] ...", logger::INFO);
		
		// Get dbQuery
		$dbq = new dbQuery("785558523", "resources.literals.translator");
		$dbc = new interDbConnection();
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['translator_id'] = account::getAccountID();
		$attr['value'] = $value;

		// Execute Query
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Vote a literal's translation
	 * 
	 * @param	integer	$translation
	 * 		The translation id
	 * 
	 * @param	mixed	$vote
	 * 		The vote value.
	 * 		For positive vote, this is set as TRUE.
	 * 		For negative vote, this is set as FALSE.
	 * 		For removing the vote, this is set as NULL.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function vote($translation, $vote = NULL)
	{
		$dbc = new interDbConnection();
		
		// Set Query Attributes
		$attr = array();
		$attr['translation_id'] = $translation;
		$attr['translator_id'] = account::getAccountID();
		
		if (is_null($vote))
		{
			// Remove vote
			$dbq = new dbQuery("1180496398", "resources.literals.translator");
			return $dbc->execute($dbq, $attr);
		}
		
		// Get dbQuery
		$dbq = new dbQuery("373138291", "resources.literals.translator");
		
		// Set attributes and execute
		$attr['vote'] = ($vote === TRUE ? 1 : 0);
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Lock a translated literal and remove all other translations
	 * 
	 * @param	integer	$translation_id
	 * 		The translation id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function lock($translation_id)
	{
		// Log activity
		logger::log("Lock literal translation [".$translation_id."] ...", logger::INFO);
		
		// Init
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1098964207", "resources.literals.translator");
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $translation_id;
		
		// Execute Query
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Wrap a translatable literal in order to be translated at runtime.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$id
	 * 		The literal's name-id.
	 * 
	 * @param	string	$value
	 * 		The literal's value.
	 * 
	 * @return	DOMElement
	 * 		The wrapped literal.
	 */
	public static function wrap($scope, $id, $value)
	{
		// Create translatable span
		$span = DOM::create("span", "", "", "translatable");
		
		// Set translation arguments
		$args = array();
		$args['scope'] = $scope;
		$args['id'] = $id;
		DOM::data($span, "tr", $args);
		
		// Set Inner HTML
		DOM::innerHTML($span, $value);
		
		return $span;
	}
}
//#section_end#
?>