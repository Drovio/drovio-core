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
 * @version	1.0-7
 * @created	March 24, 2014, 14:51 (EET)
 * @revised	November 28, 2014, 19:23 (EET)
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
	{return TRUE;
		$output = shell_exec('php -l '.$file);

		// Check the output for errors
		if (strpos($output, "No syntax errors detected") === 0)
			return TRUE;
		else
			return $output;
	}
	
	/**
	 * Given a php code, it detects all used classes.
	 * 
	 * @param	string	$code
	 * 		The php code to get the uses from.
	 * 
	 * @return	array
	 * 		Returns an array that lists all uses. Each entry has the following info...
	 * 		path: The use path of the class,
	 * 		alias: The alias of the class throughout the script or NULL,
	 * 		inactive: If set, it means that this use has been declared but not used in the script.
	 */
	public static function getUses($code)
	{
		// useMap[incl|namespace] -> refs
		// Strip Comments
		$code = self::removeComments($code);
		
		// Get uses
		preg_match_all("/^[ \s\t]*\buse\b([^\n\r;]*)/m", $code, $matches);
		// Get actual uses
		preg_match_all("/\bnew\b[ \s\t]+([a-zA-Z_\x7f-\xff\\\\][a-zA-Z0-9_\x7f-\xff\\\\]*)[ \s\t]*\(/", $code, $newMatches);
		preg_match_all("/([a-zA-Z_\x7f-\xff\\\\][a-zA-Z0-9_\x7f-\xff\\\\]*)::(?=[^\)\n\r]+\()/", $code, $staticMatches);
		
		// Build uses map
		$useMap = array();
		
		// Add namespace
		$useMap['namespace']['path'] = "\\";
		$useMap['namespace']['alias'] = NULL;
		
		// Get defined uses 
		// Only classes as accounted for. Usage of namespace functions and constants (PHP 5.6+) is ignored in this version.
		foreach ($matches[1] as $hits) 
		{
			$h = explode(',', $hits);
			foreach ($h as $hit)
			{
				$hit = trim($hit);
				// If hit starts with "function " or "const " ignore use
				if (strpos($hit, "function ") === 0 || 
						strpos($hit, "Function ") === 0 || 
						strpos($hit, "const ") === 0)
					continue;
					
				$parts = preg_split("/ [aA][sS] /", $hit);
				$path = trim($parts[0], "\\ \t\n\r\0\x0B");
				/*$head = strstr($path, "\\", TRUE);
				if (!empty($head) && empty($useMap[$head]['path']))
					$head = "namespace";
					
				$path = $useMap[$head]['path'].($head == "namespace" ? "\\".$path : strstr($path, "\\"));*/
				$alias = trim($parts[1]);
				$ref = (empty($alias) ? array_pop(explode("\\", $path)) : $alias);
				
				$useMap[$ref]['path'] = "\\".$path;
				$useMap[$ref]['alias'] = $alias;
			}
		}
		
		// Get/filter unique uses
		$uses = array_merge($newMatches[1], $staticMatches[1]);
		$uses = array_flip($uses);
		unset($uses['parent']);
		unset($uses['self']);
		unset($uses['static']);
		$uses = array_keys($uses);
		
		$realUses = array();
		$i = -1;
		// Create uses array. For each use, keep name (path), type, and alias if available...
		foreach ($uses as $idx => $use)
		{
			$realUses[++$i] = self::normalizeUse($use, $useMap);
			//$realUses[$idx]['type'] = "use";
		}
		
		foreach ($useMap as $ref => $info)
		{
			if (!empty($info['used']) || $ref == "namespace")
				continue;
			
			$info['inactive'] = TRUE;
			$realUses[++$i] = $info;
		}
		
		return $realUses;
	}
	
	/**
	 * Normalizes a use path based on a usemap.
	 * 
	 * @param	string	$use
	 * 		The path of the class being used.
	 * 
	 * @param	array	$useMap
	 * 		The array of uses that have been defined in a given file. Warning: this array is being altered in a semi-transparent way by the function.
	 * 
	 * @return	array
	 * 		An array with the normalized path and an alias where it exists.
	 */
	private static function normalizeUse($use, &$useMap)
	{
		$info = array();
		$use = trim($use);
		$pos = strpos("\\", $use);
		if ($pos === 0)
		{
			$info['path'] = $use;
			$info['alias'] = NULL;
			return $info;
		}
		
		$head = array_shift(explode("\\", $use));
		if (!empty($head) && empty($useMap[$head]['path']))
			$head = "namespace";
		
		$info['path'] = $useMap[$head]['path'].($head == "namespace" ? "\\".$use : strstr($use, "\\"));
		$info['alias'] = $useMap[$head]['alias'];
		$useMap[$head]['used'] = TRUE;
		
		return $info;
	}
	
	/**
	 * Parses a string of code and returns the calculated metrics for that piece of code.
	 * 
	 * @param	string	$code
	 * 		The code to be parsed in order to calculate its metrics.
	 * 
	 * @return	array
	 * 		An array holding the metrics for the specific piece of code as follows...
	 * 		LOC: Total lines in the file as received,
	 * 		CLOC: Lines of comments,
	 * 		SLOC-P: Lines of pure physical code,
	 * 		NOF: Number of functions,
	 * 		LOC-PF: This is the lines of pure code devided by the number of functions. This approximates to the lines per function.
	 */
	public static function getMetrics($code)
	{
		$metrics = array();
		
		// Code as is
		$metrics['LOC'] = substr_count($code, "\n");

		// Code without comments
		$code = self::removeComments($code);
		$noCommentLines = substr_count($code, "\n");
		$metrics['CLOC'] = $metrics['LOC'] - $noCommentLines;
		
		// Pure code
		$code = preg_replace("/^[ \t]+?[\r\n]/m", "", $code);
		$metrics['SLOC-P'] = substr_count($code, "\n");
		
		// Number of functions
		$metrics['NOF'] = preg_match_all("/^[\r\n\t ]*[\t ]*(final)?[\t ]*(abstract)?[\t ]*(public|private|protected)?[\t ]*(static)?[\t ]*\b[fF]unction\b.*\(.*\)/m", $code, $matches);
		
		// Average lines of code in functions*
		$metrics['LOC-PF'] = (empty($metrics['NOF']) ? $metrics['SLOC-P'] : $metrics['SLOC-P']/$metrics['NOF']);
		$metrics['LOC-PF'] = number_format($metrics['LOC-PF'], 3, ".", "");
		
		return $metrics;
	}
}
//#section_end#
?>