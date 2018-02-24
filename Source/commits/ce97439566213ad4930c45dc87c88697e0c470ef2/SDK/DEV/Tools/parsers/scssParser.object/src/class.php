<?php
//#section#[header]
// Namespace
namespace DEV\Tools\parsers;

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
 * @package	Tools
 * @namespace	\parsers
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("DEV", "Tools", "parsers/cssParser");

use \DEV\Tools\parsers\cssParser;

/**
 * SCSS parser
 * 
 * Converts scss code to css.
 * Supports mixins, nesting and variables.
 * 
 * @version	1.3-1
 * @created	May 9, 2015, 11:31 (EEST)
 * @updated	July 27, 2015, 14:42 (EEST)
 */
class scssParser extends cssParser
{
	/**
	 * Convert a given scss code to css.
	 * 
	 * @param	string	$scss
	 * 		The scss code.
	 * 
	 * @return	string
	 * 		The converted css code.
	 */
	public static function toCSS($scss)
	{
		// Handles scss mixins
		$result = self::handleMixins($scss, $m);
		
		// Handles scss nesting
		$result = self::handleNesting($result);
		
		// Handles scss inheritance, disabled for the moment
		//$result = self::handleInheritance($result);
		
		// Handles scss variables
		$result = self::handleVariables($result, $v);
		
		// Return converted css
		return trim($result);
	}
	
	/**
	 * Handles the scss and inheritance of a given scss and returns the generated css.
	 * 
	 * @param	string	$scssInput
	 * 		The scss input code.
	 * 
	 * @return	string
	 * 		The generated css code.
	 */
	private static function handleInheritance($scssInput)
	{
		return $scssInput;
	}
	
	/**
	 * Handles the nesting feature of a given scss, removes it and flattens the scss into normal css.
	 * 
	 * @param	string	$scssInput
	 * 		The scss input code.
	 * 
	 * @return	string
	 * 		The generated css code.
	 */
	private static function handleNesting($scssInput) 
	{
		$tmp = $scssInput;
		$tmp = self::removeNesting_R($tmp, "");

		// Clean redundant lines
		$tmp = self::clean($tmp);
		
		return $tmp; 
	}
	/**
	 * Identify blocks. Uses recursive normal expression, checks if they have more blocks in them and strips everything.
	 * Works for single selector atm.
	 * It is a recursive function.
	 * 
	 * @param	string	$input
	 * 		.The current block to remove nests
	 * 		Initially is the whole scss.
	 * 
	 * @param	string	$prefix
	 * 		It is the "wrapping" accumulated selector.
	 * 
	 * @return	string
	 * 		The css after removing the nesting.
	 */
	private static function removeNesting_R($input, $prefix) 
	{
		// Identify blocks. Uses recursive normal expression
		// It identifies blocks, checks if they have more blocks in them, and strips everything.
		// Each time it replaces the identified block with something
		// matches[0] holds the whole block: preceding code + selectors + outmost {} block
		// matches[1] holds preceding code + selectors
		// matches[2] holds outmost {} block
		// if matches[3] exists, it means there is no nesting and it holds the body of the block
		$input = preg_replace_callback("/^([^{]*)({((?>[^{}]+)|(?2))*})/m", 
			function ($matches) use ($prefix)
			{
				// If the block has no nesting, return it as is
				if (trim($matches[3]) != "" && $prefix == "")
					return $matches[0];
				
				// Nesting
				// Split preceding code [if any] from selectors
				$arr = explode(";", $matches[1]);
				// selectors of the block
				$selector = array_pop($arr);
				// preceding code
				$pre = implode(";", $arr);
				// block's body
				$sub = substr($matches[2], 1, count($matches[2])-2);
				
				// If it's a "duck" block, apply nesting inside
				if (strrpos(trim($selector), "@", -strlen(trim($selector))) !== FALSE && $prefix == "")
					return $pre.$selector."{".self::removeNesting_R($sub)."}\n";
				
				// combined prefix selector
				$combined = self::combineMultipleSelectors($prefix, $selector);
				
				// No further nested blocks
				if (trim($matches[3]) != "")
					return $pre.$combined." ".$matches[2];
				
				// Identify nested blocks, remove them, and store them in an array
				$deeper = array();
				// Identifies outmost nested blocks. Uses recursive normal expression
				$contents = preg_replace_callback("/^([^{]*)({((?>[^{}]+)|(?2))*})/m",
					function ($m) use (&$deeper) {
						// Identify preceding code
						$arr = explode(";", $m[1]);
						$s = array_pop($arr);
						$p = implode(";", $arr);
						// Store block and return only the preceding code.
						// The stored blocks will later be checked for nesting and "pasted" after the wrapping block
						$deeper[] = $s.$m[2];
						return $p;
					},
					$sub);
				
				// Return preceding code + selector + contents without the nests
				if ($prefix == "")
					$result = $pre.$selector." {\n".$contents."}\n";
				else
					$result = $pre.$combined." {\n".$contents."}\n";
				
				// Append inner nests to result
				foreach ((array)$deeper as $n) 
				{
					// First strip them
					$temp = self::removeNesting_R($n, $combined);
					
					// Reduce indent by one level
					$temp = preg_replace("/^([\\t]|[ ]{2,4})/m", "", $temp);
					
					// Append prefix + selector + body stripped from nesting
					$result = $result.trim($temp)."\n\n";
				}
				
				return $result;
			},
			$input);
		
		return $input;
	}
	
	/**
	 * Combines multiple selectors and replaces & with parent selector.
	 * 
	 * @param	string	$parentString
	 * 		The parent selector string.
	 * 
	 * @param	string	$childString
	 * 		The child selector string.
	 * 
	 * @return	string
	 * 		The combined selector.
	 */
	private static function combineMultipleSelectors($parentString, $childString)
	{
		// Separate multiple selectors
		$pre = explode(",", $parentString);
		$sel = explode(",", $childString);
		
		// Create result array
		$result = array();
		foreach ((array)$pre as $p)
			foreach ((array)$sel as $s)
				if (strpos($s, "&") === FALSE)
					$result[] = trim(trim($p)." ".trim($s));
				else
					$result[] = trim(str_replace("&", trim($p), trim($s)));
		
		// Implode result and return
		return implode(", ", $result);
	}
	
	/**
	 * Handles scss mixins and returns the css with the mixins resolved.
	 * 
	 * @param	string	$scssInput
	 * 		The scss input code.
	 * 
	 * @param	array	$mixins
	 * 		Pre-loaded mixins.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The generated css code.
	 */
	private static function handleMixins($scssInput, &$mixins = array()) 
	{
		$tmp = $scssInput;
		
		// Identify and replace mixin definitions. 
		// Populate mixins array. Uses recursive normal expression.
		$tmp = preg_replace_callback( "/[\\@]mixin([^\\(]+)\\(([^\\)]*)\\)[^{]*({((?>[^{}]+)|(?3))*})/" , 
			function ($matches) use (&$mixins) {
				// Get body of mixin. matches[3] holds {} block
				$sub = substr($matches[3], 1, count($matches[3])-2);
				// Get mixin's arguments as an array. matches[2] holds comma separate list of arguments
				$args = explode(",", $matches[2]);
				// Get mixin's name
				$name = trim($matches[1]);
				
				// Mixin's array has the form:
				// mixins[name][n] = n'th argument's name
				// mixins[name][body] = mixin's body
				foreach ($args as $a)
					$mixins[$name][] = trim($a);
				$mixins[$name]['body'] = trim($sub);
				
				return "";
			}
			, $tmp);
		
		return trim(self::applyMixins($tmp, $mixins));
	}
	
	/**
	 * Uses mixins array to apply on input.
	 * It works like search and replace. Searches for @include of mixins in array, replaces arguments with values and replaces include with mixin's body.
	 * 
	 * @param	string	$scssInput
	 * 		The scss input code.
	 * 
	 * @param	array	$mixins
	 * 		An array of all mixins content body to apply.
	 * 
	 * @return	string
	 * 		Returns text with given mixins applied.
	 */
	private static function applyMixins($scssInput, $mixins)
	{
		$tmp = $scssInput;
		
		foreach ((array)$mixins as $name => $info)
		{
			// Find include #mixinname# and replace it with #mixinname#'s body
			$tmp = preg_replace_callback( "/[\\@]include[\\s]+".preg_quote($name)."[\\s]*\\(([^\\)]*)\\)[\\s]*;/" , 
				function ($matches) use ($info)
				{
					// Get parameters array from include
					// [Bug]. It will fail if parameters use commas.
					$params = explode(",", $matches[1]);
					// Remove body element from mixin array
					$body = array_pop($info);
					// Apply params to arguments in body
					foreach ((array)$info as $key => $arg)
						$body = str_replace(trim($arg), trim($params[$key]), $body);
					// return body
					return $body;
				}
				, $tmp);
		}
		
		return $tmp;
	}
	
	/**
	 * Replaces a number of variables in the given scss and returns a clean css code.
	 * 
	 * @param	string	$scssInput
	 * 		The scss input code.
	 * 
	 * @param	array	$vars
	 * 		An array of variables as context.
	 * 
	 * @return	string
	 * 		The css code with the variables applied.
	 */
	private static function handleVariables($scssInput, &$vars = array()) 
	{
		$tmp = $scssInput;
		
		// Identify global variables and remove their definitions
		$tmp = preg_replace_callback( "/[\\n\\r]{0,2}[\\t ]*([$][a-zA-Z-_0-9]+)[\\s\\t ]*[:][\\s\\t ]*([^!;]+)([!]global[\\s\\t ]*)?[;]/" , 
			function ($matches) use (&$vars)
			{
				// vars[name] = value
				$vars[$matches[1]] = $matches[2];
				return "";
			}
			, $tmp);
			
		return trim(self::applyVariables($tmp, $vars));
	}
	
	/**
	 * Search and replace variables based on vars array.
	 * 
	 * @param	string	$scssInput
	 * 		The scss input code.
	 * 
	 * @param	array	$vars
	 * 		All the scss variables.
	 * 
	 * @return	string
	 * 		The css code with the variables applied.
	 */
	private static function applyVariables($scssInput, $vars)
	{
		$tmp = $scssInput;
		
		// Apply Global vars
		foreach ((array)$vars as $name => $val)
			$tmp = str_replace(trim($name), trim($val), $tmp);
		
		return $tmp;
	}
	
	/**
	 * Clean the given code from redundant lines.
	 * 
	 * @param	string	$input
	 * 		The input code.
	 * 
	 * @return	string
	 * 		The cleaned code.
	 */
	private static function clean($input)
	{
		return preg_replace("/[^{};]*\\{[\\t\\s]*\\}|^[\\s]*[\\n\\r](?=[^{}]*})|^[\\s]*$/m", "", $input);
	}
}
//#section_end#
?>