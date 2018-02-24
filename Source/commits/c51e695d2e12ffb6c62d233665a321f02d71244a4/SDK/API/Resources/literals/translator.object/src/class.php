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

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Resources", "literals::literalManager");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "DOM");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\profiler\logger;
use \API\Model\units\sql\dbQuery;
use \API\Resources\geoloc\locale;
use \API\Resources\literals\literalManager;
use \API\Security\account;
use \UI\Html\DOM;

/**
 * Literal Translator
 * 
 * System's literal translation engine.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 14:01 (EEST)
 * @revised	July 2, 2013, 14:26 (EEST)
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
	 * Get the translated values of all literals of a given scope according to translating locale (defined by the user).
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function get($scope)
	{
		// Log activity
		logger::log("Getting translator literals from '".$scope."' scope ...");
		
		// Get Translated and not translated literals
		$initials = parent::get($scope);
		
		// Get translated literals
		$dbc = new interDbConnection();
		$dbq = new dbQuery("169173212", "resources.literals.translator");
		
		// Set Query Attributes
		$attr = array();
		$attr['scope'] = $scope;
		$requestedLocale = locale::get();
		$attr['locale'] = $requestedLocale;
		
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
	 * @return	mixed
	 * 		{description}
	 */
	public static function translate($scope, $name, $value)
	{
		// Log activity
		logger::log("Translating literal [".$scope."][".$name."] <= [".$value."] ...");
		
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
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
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
	 * @return	mixed
	 * 		{description}
	 */
	public static function vote($translation, $vote = NULL)
	{
		// Log activity
		logger::log("Voting literal [".$translation."] <= [".(is_null($vote) ? "Neutral" : ($vote ? "Positive" : "Negative"))."] ...");
		
		// Get User profile
		$dbc = new interDbConnection();
		
		if (is_null($vote))
		{
			// Remove vote
			$dbq = new dbQuery("1180496398", "resources.literals.translator");
			
			// Set Query Attributes
			$attr = array();
			$attr['translation_id'] = $translation;
			$attr['translator_id'] = account::getAccountID();
			
			// Execute Query
			return $dbc->execute($dbq, $attr);
		}
		
		// Get dbQuery
		$dbq = new dbQuery("373138291", "resources.literals.translator");
		
		// Set Query Attributes
		$attr = array();
		$attr['translation_id'] = $translation;
		$attr['translator_id'] = account::getAccountID();
		$attr['vote'] = ($vote === TRUE ? 1 : 0);
		
		// Execute Query
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Lock a translated literal and remove all other translations
	 * 
	 * @param	{type}	$translation_id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function lock($translation_id)
	{
		// Log activity
		logger::log("Lock literal translation [".$translation_id."] ...");
		
		// Get dbQuery
		$dbq = new dbQuery("1098964207", "resources.literals.translator");
		$dbc = new interDbConnection();
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $translation_id;
		
		// Execute Query
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Wrap a translatable literal in order to be translated on the fly.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope
	 * 
	 * @param	string	$id
	 * 		The literal's name-id
	 * 
	 * @param	string	$value
	 * 		The literal's value
	 * 
	 * @return	DOMElement
	 * 		{description}
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