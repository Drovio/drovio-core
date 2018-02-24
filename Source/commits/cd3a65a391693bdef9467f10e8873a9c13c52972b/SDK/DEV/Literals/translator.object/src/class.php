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

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Literals", "literalManager");
importer::import("DEV", "Profiler", "logger");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Geoloc\locale;
use \API\Security\account;
use \UI\Html\DOM;
use \DEV\Literals\literalManager;
use \DEV\Profiler\logger;

/**
 * Literal Translations Manager
 * 
 * System's literal translation engine.
 * 
 * @version	0.1-1
 * @created	July 21, 2014, 12:05 (EEST)
 * @revised	July 21, 2014, 12:05 (EEST)
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
	 * Gets the initial values for every literal and adds the translations for those that have a translation.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @return	array
	 * 		Returns an array of all translated and non translated literals as 'translated' and 'nonTranslated'.
	 */
	public static function get($projectID, $scope)
	{
		// Log activity
		logger::log("Getting translator literals from '".$scope."' scope ...", logger::INFO);
		
		// Get Translated and not translated literals
		$initials = parent::get($projectID, $scope);
		
		// Get translated literals
		$dbc = new dbConnection();
		$dbq = new dbQuery("16848246733218", "literals.translations");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
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
	 * 		The literal's translated value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function translate($projectID, $scope, $name, $value)
	{
		// Log activity
		logger::log("Translating literal [".$projectID."][".$scope."][".$name."] <= [".$value."] ...", logger::INFO);
		
		// Get dbQuery
		$dbq = new dbQuery("26706434986943", "literals.translations");
		$dbc = new dbConnection();
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
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
	 * 		It is NULL by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function vote($translation, $vote = NULL)
	{
		$dbc = new dbConnection();
		
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
	 * Lock a translated literal and remove all other translations.
	 * 
	 * @param	integer	$translation_id
	 * 		The translation id to lock to.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function lock($translation_id)
	{
		// Log activity
		logger::log("Lock literal translation [".$translation_id."] ...", logger::INFO);
		
		// Init
		$dbc = new dbConnection();
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
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name-id.
	 * 
	 * @param	string	$value
	 * 		The literal's value.
	 * 
	 * @return	DOMElement
	 * 		The wrapped literal.
	 */
	public static function wrap($projectID, $scope, $name, $value)
	{
		// Create translatable span
		$span = DOM::create("span", "", "", "transl");
		
		// Set translation arguments
		$args = array();
		$args['pid'] = $projectID;
		$args['sc'] = $scope;
		$args['nm'] = $name;
		DOM::data($span, "tr", $args);
		
		// Set Inner HTML
		DOM::innerHTML($span, $value);
		
		return $span;
	}
}
//#section_end#
?>