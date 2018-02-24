<?php
//#section#[header]
// Namespace
namespace API\Geoloc\lang;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	{empty}
 * @package	{empty}
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "literals::literal");
importer::import("API", "Content", "literals::moduleLiteral");

importer::import("API", "Geoloc", "lang::literalManager");
importer::import("API", "Geoloc", "lang::literal");
importer::import("API", "Platform", "DOM::DOM");
importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Geoloc", "locale");

use \API\Content\literals\literal as cliteral;
use \API\Content\literals\moduleLiteral;

use \API\Geoloc\lang\literalManager;
use \API\Geoloc\lang\literal;
use \API\Platform\DOM\DOM;
use \API\Platform\DOM\DOMParser;
use \API\Geoloc\locale;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	{empty}
 * @revised	{empty}
 * 
 * @deprecated	Use \API\Resources\literals\literal and \API\Resources\literals\moduleLiteral instead.
 */
class mlgContent extends literalManager
{
	/**
	 * {description}
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$wrapped
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use \API\Resources\literals\literal::get() instead.
	 */
	public static function get_literal($type, $id, $wrapped = TRUE)
	{
		// Get Literal
		return cliteral::get($type, $id, $wrapped);
	}
	
	/**
	 * _____ Get the value of a given Literal_id from the given type of Literals (Dynamic) (and the given locale - if not set, get current) _____//
	 * 
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$wrapped
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use \API\Resources\literals\moduleLiteral::get() instead.
	 */
	public static function get_moduleLiteral($moduleID, $id, $wrapped = TRUE)
	{
		return moduleLiteral::get($moduleID, $id, $wrapped);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use \API\Resources\literals\moduleLiteral::get() instead.
	 */
	public static function get_moduleLiterals($moduleID)
	{
		// Get all literals as an array
		$literals = moduleLiteral::get($moduleID);
		return $literals;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$module_id
	 * 		{description}
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @param	{type}	$reset
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use \API\Resources\literals\moduleLiteral::set() instead.
	 */
	public static function set_moduleLiterals($module_id, $content, $locale = "", $reset = FALSE)
	{
	return;
		if (trim($locale) == "")
			$locale = defaultLocale;

		$literals = array();
		foreach ($content as $id => $value)
		{
			$dom_parser = new DOMParser();
			$lt = new literal($dom_parser, $locale);
			$lt->set_id($id);
			$lt->set_value($value);
			$literals[] = $lt;
		}
		
		parent::set_moduleLiterals($module_id, $locale, $literals, $reset);
	}
	
	/**
	 * Retrives the message from the defined directory
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$content_value
	 * 		{description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use \API\Resources\literals\literal::wrap() instead.
	 */
	private function _wrap($type, $id, $content_value, $locale = "")
	{
		// If requested lang is "", get current locale
		if ($locale == "")
			$locale = locale::get_locale();
		
		// If translator is not enabled, return just a span
		if (FALSE)
		{
			$holder = DOM::create('span');
			DOM::innerHTML($holder, trim($content_value));
			return $holder;
		}
		
		// If translator is enabled, return dressed span
		//_____ Message Holder
		$holder = DOM::create("span", "", "", "uiMlgCnt");
		
		//_____ Insert Info
		$mlgType = array();
		$mlgType['id'] = $id;
		$mlgType['rsrc'] = $type;
		$mlgType['locale'] = $locale;
		DOM::data($holder, 'mlg', $mlgType);
		
		DOM::innerHTML($holder, trim($content_value));

		return $holder;
	}
}
//#section_end#
?>