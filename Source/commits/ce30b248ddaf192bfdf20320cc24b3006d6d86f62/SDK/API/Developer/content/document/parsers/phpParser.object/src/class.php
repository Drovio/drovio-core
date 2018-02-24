<?php
//#section#[header]
// Namespace
namespace API\Developer\content\document\parsers;

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
 * @package	Developer
 * @namespace	\content\document\parsers
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "content::document::parsers::codeParser");

use \API\Developer\content\document\parsers\codeParser;

/**
 * PHP Parser
 * 
 * Php parser helper for handling the language features.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 11:46 (EET)
 * @revised	March 24, 2014, 14:51 (EET)
 * 
 * @deprecated	Use \DEV\Tools\parsers\phpParser instead. Not all functions are implemented in the new class!
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
	 * All system commands.
	 * 
	 * @type	array
	 */
	private static $systemCommands = array(
		"escapeshellarg", "escapeshellcmd",
		"exec",
		"passthru",
		"proc_close", "proc_get_status", "proc_nice", "proc_open", "proc_terminate", 
		"shell_exec","system"
	);
	
	/**
	 * All filesystem commands.
	 * 
	 * @type	array
	 */
	private static $filesystemCommands = array(
		"chgrp", "chmod", "chown", 
		"copy", "delete",
		"fgetc", "fgetcsv", "fgets", "fgetss",
		"file_get_contents", "file_put_contents", "file", 
		"fopen", "fputs", "fread", "fwrite",
		"glob", "link", "mkdir", "move_uploaded_file", 
		"parse_ini_file", "parse_ini_string", 
		"popen", "readfile", "readlink", "rename", "rmdir",
		"set_file_buffer", "stat", "tempnam", "tmpfile", "touch",
		"umask", "unlink"
	);
	
	/**
	 * All directory commands.
	 * 
	 * @type	array
	 */
	private static $directoryCommands = array(
		"chdir", "chroot", "closedir", "dir", "getcwd", "opendir", "readdir", "rewinddir", "scandir"
	);
	
	/**
	 * All require commands.
	 * 
	 * @type	array
	 */
	private static $requireCommands = array(
		"require", "require_once", "include", "include_once"
	);
	
	/**
	 * Wraps code to php delimiters.
	 * Returns the wrapped code.
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
	 * Creates and returns a php comment.
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
	 * Creates and returns a specific comment for documentation usage.
	 * 
	 * @param	string	$content
	 * 		The comment's content.
	 * 
	 * @return	string
	 * 		The documentation comment created.
	 */
	public static function documentationComment($content)
	{
		$content = " * ".preg_replace("/[\n\r]/", "$0 * ", $content);
		return self::$ml_comment_start."*\n".$content."\n ".self::$ml_comment_end."\n";
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
	 * 		The class code.
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
	
	/**
	 * Clears the php code from dangerous commands for the system.
	 * This is used for the php code in the application center.
	 * 
	 * @param	string	$code
	 * 		The code to check.
	 * 
	 * @return	string
	 * 		The code with commented all the dangerous commands.
	 */
	public static function safe($code)
	{
		$unsafeCommands = array();
		$unsafeCommands[] = implode("|", self::$systemCommands);
		$unsafeCommands[] = implode("|", self::$filesystemCommands);
		$unsafeCommands[] = implode("|", self::$directoryCommands);
		$unsafeCommands[] = implode("|", self::$requireCommands);
		
		$unsafe = implode("|", $unsafeCommands);
		$pattern = "/([^;{}\n\r]*?[^:>])(\b(".$unsafe.")\b[^;{}]*?[;{}])|([^;{}\n\r]*?[^:>])(\b(".$unsafe.")\b[^;{}]*?)$/m";
		$replacement = "$1//$2";


		return preg_replace($pattern, $replacement, $code);
	}
	
	/**
	 * Creates a php comment
	 * 
	 * @param	string	$content
	 * 		The comment content
	 * 
	 * @param	string	$multi
	 * 		Indicator whether the comment will be multiline
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use comment() instead.
	 */
	public static function get_comment($content, $multi = FALSE)
	{
		return self::comment($content, $multi);
	}
	
	/**
	 * Creates a specific comment for documentation usage.
	 * 
	 * @param	{type}	$content
	 * 		{description}
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use documentationComment() instead.
	 */
	public static function get_documentationComment($content)
	{
		return self::documentationComment($content);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use variable() instead.
	 */
	public static function get_variable($name)
	{
		return self::variable($name);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$className
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getClassCode() instead.
	 */
	public static function get_classCode($className)
	{
		return self::getClassCode($className);
	}
	
	/**
	 * Check php syntax on a given file
	 * 
	 * @param	string	$file
	 * 		The file path
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use syntax() instead.
	 */
	public static function check_syntax($file)
	{
		return self::syntax($file);
	}
	
	/**
	 * Clears the code from html entities and from other non-printable characters that cause parsing problems.
	 * Returns the cleared code.
	 * 
	 * @param	string	$code
	 * 		The source code.
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use clear() instead.
	 */
	public static function clearCode($code)
	{
		return self::clear($code);
	}
}
//#section_end#
?>