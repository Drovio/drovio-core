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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Profile", "account");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Literals", "literalManager");
importer::import("DEV", "Profile", "translator");
importer::import("DEV", "Profiler", "logger");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Geoloc\locale;
use \API\Profile\account;
use \UI\Html\DOM;
use \DEV\Literals\literalManager;
use \DEV\Profile\translator as pTranslator;
use \DEV\Profiler\logger;

/**
 * Literal Translations Manager
 * 
 * System's literal translation engine.
 * 
 * @version	2.1-1
 * @created	July 21, 2014, 12:05 (EEST)
 * @updated	July 15, 2015, 9:57 (EEST)
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
	 * @param	string	$locale
	 * 		The locale to get the literals from.
	 * 		If NULL, get the current system locale.
	 * 		It is NULL by default.
	 * 
	 * @return	array
	 * 		Returns an array of all translated and non translated literals as 'translated' and 'nonTranslated'.
	 */
	public static function get($projectID, $scope, $locale = NULL)
	{
		// Normalize locale
		$locale = (empty($locale) ? locale::get() : $locale);
		
		// Log activity
		logger::log("Getting translator literals from '".$scope."' scope ...", logger::INFO);
		
		// Get Translated and not translated literals
		$initials = parent::get($projectID, $scope, locale::getDefault());
		
		// Get translated literals
		$dbc = new dbConnection();
		$dbq = new dbQuery("16848246733218", "literals.translations");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['locale'] = $locale;
		
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
	 * Get all translations of a given literal.
	 * 
	 * @param	integer	$projectID
	 * 		The project id
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal's name.
	 * 
	 * @param	string	$translationLocale
	 * 		The translation locale to get the translations from.
	 * 
	 * @return	array
	 * 		An array of all translations.
	 */
	public static function getTranslations($projectID, $scope, $name, $translationLocale = NULL)
	{
		// Log activity
		logger::log("Getting literal translations from '".$scope."' scope ...", logger::INFO);
		
		// Get translator profile data
		$translatorProfile = pTranslator::profile();
		$profileLocale = $translatorProfile['translation_locale'];
		$translationLocale = (empty($translationLocale) ? $profileLocale : $translationLocale);
		
		// Get translations
		$dbc = new dbConnection();
		$dbq = new dbQuery("26894475679241", "literals.translations");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['locale'] = $translationLocale;
		
		// Execute Query
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Create a translation of a literal to the translator's locale.
	 * If null, get the translator's locale.
	 * It is null by default.
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
	 * @param	string	$translationLocale
	 * 		The translation locale to translate the literal to.
	 * 		If null, get the translator's locale.
	 * 		It is null by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function translate($projectID, $scope, $name, $value, $translationLocale = NULL)
	{
		// Log activity
		logger::log("Translating literal [".$projectID."][".$scope."][".$name."] <= [".$value."] ...", logger::INFO);
		
		// Get translator profile data
		$translatorProfile = pTranslator::profile();
		$profileLocale = $translatorProfile['translation_locale'];
		$translationLocale = (empty($translationLocale) ? $profileLocale : $translationLocale);
		
		// Get dbQuery
		$dbq = new dbQuery("26706434986943", "literals.translations");
		$dbc = new dbConnection();
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['translator_id'] = account::getAccountID();
		$attr['locale'] = $translationLocale;
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
			$dbq = new dbQuery("18081226766072", "literals.translations");
			return $dbc->execute($dbq, $attr);
		}
		
		// Get dbQuery
		$dbq = new dbQuery("22601203479597", "literals.translations");
		
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
		$dbq = new dbQuery("18549203792594", "literals.translations");
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $translation_id;
		
		// Execute Query
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Clear all translations for a given literal.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$scope
	 * 		The literal's scope.
	 * 
	 * @param	string	$name
	 * 		The literal name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function clear($projectID, $scope, $name)
	{
		// Log activity
		logger::log("Clearing literal translations from [".$projectID."][".$scope."][".$name."] <= [".$value."] ...", logger::INFO);
		
		// Init
		$dbc = new dbConnection();
		$dbq = new dbQuery("25333204757433", "literals.translations");
		
		// Set Query Attributes
		$attr = array();
		$attr['project_id'] = $projectID;
		$attr['scope'] = $scope;
		$attr['name'] = $name;
		$attr['locale'] = locale::getDefault();
		
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