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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Tools", "codeParser");

use \DEV\Tools\codeParser;

/**
 * PHP Parser
 * 
 * Php parser helper for handling the language features.
 * 
 * @version	{empty}
 * @created	March 24, 2014, 14:51 (EET)
 * @revised	March 24, 2014, 14:52 (EET)
 */
class phpParser extends codeParser
{
 	/**
 	 * Php code start delimiters.
 	 * 
 	 * @type	array
 	 */
 	protected static $start_delimiters = array('<?php', '<?');

	/**
	 * Php code end delimiters.
	 * 
	 * @type	array
	 */
	protected static $end_delimiters = array('?>');
	
	/**
	 * One line comment prefix.
	 * 
	 * @type	string
	 */
	protected static $comment_prefix = "//";
	
	/**
	 * Multi line comment prefix.
	 * 
	 * @type	string
	 */
	protected static $ml_comment_start = "/*";
	
	/**
	 * Multi line comment endfix.
	 * 
	 * @type	string
	 */
	protected static $ml_comment_end = "*/";
	
	/**
	 * Php code variable prefix.
	 * 
	 * @type	string
	 */
	protected static $variable_prefix = "$";
	
	/**
	 * Wraps code to php delimiters.
	 * 
	 * @param	string	$content
	 * 		The code to be wrapped.
	 * 
	 * @return	string
	 * 		The wrapped code.
	 */
	public static function wrap($content)
	{
		return self::$start_delimiters[0]."\n".trim($content)."\n".self::$end_delimiters[0];
	}
	
	/**
	 * Unwraps code from any tags and delimiters of php.
	 * 
	 * @param	string	$content
	 * 		The code to be unwrapped.
	 * 
	 * @return	string
	 * 		The unwrapped code.
	 */
	public static function unwrap($content)
	{
		$quoted_start_delimiters = self::$start_delimiters;
		// Escape Starting Delimiters
		foreach ($quoted_start_delimiters as $key => $dlm)
			$quoted_start_delimiters[$key] = preg_quote($dlm, '/');
			
		$quoted_end_delimiters = self::$end_delimiters;
		// Escape Ending Delimiters
		foreach ($quoted_end_delimiters as $key => $dlm)
			$quoted_end_delimiters[$key] = preg_quote($dlm, '/');
			
		// Start Expression
		$expression = '/';
		
		// Add Starting Delimiters
		foreach ($quoted_start_delimiters as $dlm)
			$expression .= '^('.$dlm.'[ ]*\n|'.$dlm.")|";
		
		// Add Ending Delimiters
		foreach ($quoted_end_delimiters as $dlm)
			$expression .= '(\n[ ]*'.$dlm.'|'.$dlm.")$|";
		
		// Remove ending '|'
		$expression = preg_replace('/\|$/', '', $expression);
		$expression .= '/';

		// Apply Expression to content and return result
		return trim(preg_replace($expression, '', $content));
	}
	
	/**
	 * Creates a php comment.
	 * 
	 * @param	string	$content
	 * 		The comment's content.
	 * 
	 * @param	boolean	$multi
	 * 		If true, create a multiline comment.
	 * 
	 * @return	string
	 * 		The comment created.
	 */
	public static function comment($content, $multi = FALSE)
	{
		if ($multi)
			return self::$ml_comment_start."\n".$content."\n".self::$ml_comment_end."\n";
		else
			return self::$comment_prefix." ".$content;
	}
	
	/**
	 * Creates and returns a php-like variable name.
	 * 
	 * @param	string	$name
	 * 		The variable's name.
	 * 
	 * @return	string
	 * 		The variable string.
	 */
	public static function variable($name)
	{
		return self::$variable_prefix.$name;
	}
	
	/**
	 * Creates and returns an empty php class with the constructor function.
	 * 
	 * @param	string	$className
	 * 		The class' name.
	 * 
	 * @return	string
	 * 		The class initial code.
	 */
	public static function getClassCode($className)
	{
		$classCode = "";
		$classCode .= "class $className"."\n";
		$classCode .= "{"."\n";
		
		$classCode .= "\t"."// Constructor Method"."\n";
		$classCode .= "\t"."public function __construct()"."\n";
		$classCode .= "\t"."{"."\n";
		$classCode .= "\t\t"."// Put your constructor method code here."."\n";
		$classCode .= "\t"."}"."\n";
		
		$classCode .= "}"."\n";
		
		return $classCode;
	}
	
	/**
	 * Checks the syntax structure of a php file. Returns TRUE if syntax is ok, the error otherwise.
	 * 
	 * @param	string	$file
	 * 		The php file path.
	 * 
	 * @return	mixed
	 * 		True if there is no error, otherwise it returns the error.
	 */
	public static function syntax($file)
	{
		$output = shell_exec('php -l '.$file);

		// Check the output for errors
		if (strpos($output, "No syntax errors detected") === 0)
			return TRUE;
		else
			return $output;
	}
}
//#section_end#
?>