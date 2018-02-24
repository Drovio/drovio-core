<?php
//#section#[header]
// Namespace
namespace DEV\Tools\coders;

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
 * @namespace	\coders
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * PHP Coder Helper
 * 
 * Helps with the php code handling within the system.
 * 
 * @version	{empty}
 * @created	March 24, 2014, 14:45 (EET)
 * @revised	May 21, 2014, 13:01 (EEST)
 */
class phpCoder
{
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
	 * Wraps a php code section to a delimited section
	 * 
	 * @param	string	$content
	 * 		The php code content.
	 * 
	 * @param	string	$title
	 * 		The section title.
	 * 
	 * @return	string
	 * 		The new code wrapped in a section.
	 */
	public static function section($content, $title)
	{
		return "//#section#[$title]\n".$content."\n"."//#section_end#\n";
	}
	
	/**
	 * Get all sections of a php code content in an array [section_name] = section_content
	 * 
	 * @param	string	$source
	 * 		The source code.
	 * 
	 * @return	array
	 * 		An array of all sections by name and code context.
	 */
	public static function sections($source)
	{
		// Section's regular expression
		$sectionRegexp = '/\/\/\#section\#\[\b([\w]+)\b\]([\w\W]*?)^[\t ]*\/\/\#section_end\#/m';
		
		// Create segments array
		$segments = array();
		
		// After this preg_match_all
		// $segments[0] has the matches
		// $segments[1] has the indicies <- we need this as section indices
		// $segments[2] has the contents <- we need this as section contents
		preg_match_all($sectionRegexp, $source, $segments);
		
		// Form Sections (trim contents)
		$sections = array();
		foreach ($segments[1] as $key => $index)
			$sections[$index] = trim($segments[2][$key]);
		
		return $sections;
	}
	
	/**
	 * Clears the php code from dangerous commands for the system. (For App Engine and for Modules)
	 * 
	 * @param	string	$code
	 * 		The code to be cleared.
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
		$pattern = '/^[ \t]*((?=[^\/ \t])|(\/[*])).*[@]?('.$unsafe.')[ \t]*([:]{2}|\()/m';
		$replacement = '//$0';
		$safeCode = preg_replace($pattern, $replacement, $code);
		
		if (is_null($safeCode))
			$safeCode = $code;
			
		return $safeCode;
	}
}
//#section_end#
?>