<?php
//#section#[header]
// Namespace
namespace DEV\Documentation;

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
 * @package	Documentation
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("DEV", "Documentation", "classDocumentor");

use \DEV\Documentation\classDocumentor;

/**
 * Documentation class that handles the code commenting and parsing
 * 
 * This class is responsible to comment the code based on a given documentation xml and parse documentation info from the code to construct that xml
 * 
 * @version	1.2-8
 * @created	July 4, 2014, 13:28 (EEST)
 * @updated	October 15, 2015, 15:41 (EEST)
 */
class classComments
{
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
	 * Constructor method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Put your constructor method code here.
	}
	
	/**
	 * Position comment blocks in specific spots in the given code
	 * 
	 * @param	string	$sourceCode
	 * 		The code to comment
	 * 
	 * @param	string	$manual
	 * 		The code's manual from which the comment blocks will be created.
	 * 
	 * @param	string	$library
	 * 		Code class' library
	 * 
	 * @param	string	$package
	 * 		Code class' package
	 * 
	 * @param	string	$namespace
	 * 		Code class' namespace
	 * 
	 * @param	string	$copyright
	 * 		The copyright signature.
	 * 		If empty, it gets the default value.
	 * 		The default value is 'Redback'.
	 * 
	 * @return	string
	 * 		The pretified source code
	 */
	public static function pretifySourceCode($sourceCode, $manual, $library, $package, $namespace, $copyright = "Drovio")
	{
		// Strip source code from comments
		$prettyCode = self::stripSourceCode($sourceCode);
		
		// Create class documentor to normalize manual
		$classDocumentor = new classDocumentor();
		$classDocumentor->loadContent($manual);	
		
		// Get stamp comments
		if (!(empty($library) && empty($package) && empty($namespace)))
		{
			$classStampCommentsText = self::getClassStampComments($library, $package, $namespace, $copyright);
			$prettyCode = trim($classStampCommentsText."\n".$prettyCode);
		}
		
		// Get info comments
		$classInfo = $classDocumentor->getInfo();
		
		$dateUpdated = (empty($classInfo['daterevised']) ? $classInfo['dateupdated'] : $classInfo['daterevised']);
		$classInfoCommentsText = self::getClassInfoComments($title = $classInfo['title'], $description = $classInfo['description'], $version = $classInfo['version']."-".$classInfo['build'], $created = $classInfo['datecreated'], $updated = $dateUpdated, $throws = $classInfo['throws'], $deprecated = $classInfo['deprecated']);
		// Implant info in source (before 'class' line)
		$classInfoCommentsText = trim($classInfoCommentsText);
		$prettyCode = preg_replace("/([\t ]*)(abstract)?[\t ]*\bclass\b[ \t]*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[\w, (\r\n|\n|\r)]*(?=\{[\s\S]*\})/", "$1".str_replace("\n", "\n$1", $classInfoCommentsText)."\n"."$0", $prettyCode);
		
		// Get constants comments
		$constants = $classDocumentor->getConstants();
		foreach ($constants as $const)
		{
			$constantCommentsText = self::getPropertyComments($const['description'], $const['type']);
			// Implant constant comments in source (before constant line)
			$constantCommentsText = trim($constantCommentsText);
			$prettyCode = preg_replace("/([ \t]*)\bconst\b[ \t]*\b".preg_quote($const['name'], '/')."\b.*/", "$1".str_replace("\n", "\n$1", $constantCommentsText)."\n"."$0", $prettyCode);
		}
		
		// Get properties comments
		$allProperties = $classDocumentor->getProperties();
		foreach ($allProperties as $scope => $properties)
			foreach ($properties as $propName => $propInfo) 
			{
				// For each property				
				$propertyCommentsText = self::getPropertyComments($propInfo['description'], $propInfo['type']);
				// Implant constant comments in source (before constant line)
				$propertyCommentsText = trim($propertyCommentsText);
				$prettyCode = preg_replace("/([ \t]*)(private|protected|public)[ \t]*(static)?[ \t]*".preg_quote("$".$propInfo['name'], '/')."\b.*/", "$1".str_replace("\n", "\n$1", $propertyCommentsText)."\n"."$0", $prettyCode);
			}
		
		// Get methods comments
		$allMethods = $classDocumentor->getMethods();
		foreach ($allMethods as $scope => $methods)
			foreach ($methods as $methodName => $methodInfo)
			{
				// Get parameters
				$argsInfo = $methodInfo['parameters'];
				$methodCommentsText = self::getMethodComments($methodInfo['description'], $argsInfo, $methodInfo['returntype'], $methodInfo['returndescription'], $methodInfo['throws'], $methodInfo['deprecated']);
				
				// Implant method comments in source (before method line)
				$methodCommentsText = trim($methodCommentsText);
				$prettyCode = preg_replace("/([\t ]*)(final)?([\t ]*)(abstract)?[\t ]*(public|protected|private).*[\t ]*(static)?[\t ]*\bfunction\b[\t ]*\b".preg_quote($methodInfo['name'], '/')."\b.*\(.*\).*/", "$1".str_replace("\n", "\n$1", $methodCommentsText)."\n"."$0", $prettyCode);
			}
		
		return $prettyCode;
	}
	
	/**
	 * Strips specific comments from the code
	 * 
	 * @param	string	$sourceCode
	 * 		The code from which to strip comments
	 * 
	 * @return	string
	 * 		The stripped source code
	 */
	public static function stripSourceCode($sourceCode)
	{
		$strippedCode = preg_replace("/^([ \t]*\/\/.*[\n\r]*|[ \t]*(\/\*([\w\W](?!(\*\/)))*([\w\W]\*\/))[ \t]*[\r\n]*)(?=[^\}]*^[\t ]*(final)?[\t ]*\b(public|protected|private|const|abstract|class)\b)/m", "", $sourceCode);
		$strippedCode = preg_replace("/^([ \t]*\/\/.*[\n\r]*|[ \t]*(\/\*([\w\W](?!(\*\/)))*([\w\W]\*\/))[ \t]*[\r\n]*)/", "", $strippedCode);
		
		return $strippedCode;
	}
	
	/**
	 * Creates and returns a line for a comment block
	 * 
	 * @param	mixed	$values
	 * 		Values to present in the line
	 * 
	 * @param	string	$tag
	 * 		Tag name of the line
	 * 
	 * @param	integer	$tabs
	 * 		Number of tabs between tag and values
	 * 
	 * @return	string
	 * 		The created line
	 */
	private function getDescriptionLine($values, $tag = "", $tabs = 1)
	{
		$line = "";
		$line .= (!empty($tag) ? "@".$tag : "" ).str_repeat("\t", $tabs);
		
		if (is_string($values)) 
			$line .= html_entity_decode($values); 
		else 
			foreach ($values as $v)
				$line .= html_entity_decode($v)."\t";
		
		return preg_replace("/[ \t]*$/", "", $line);
	}
	
	/**
	 * Creates and returns a comment block that holds the objects generic info, such as library, namespace, etc
	 * 
	 * @param	string	$library
	 * 		This value is presented as the object's library
	 * 
	 * @param	string	$package
	 * 		This value is presented as the object's package
	 * 
	 * @param	string	$namespace
	 * 		This value is presented as the object's namespace
	 * 
	 * @param	string	$copyright
	 * 		The copyright signature.
	 * 		If empty, it gets the default value.
	 * 		The default value is 'Redback'.
	 * 
	 * @return	string
	 * 		The comment block
	 */
	private function getClassStampComments($library, $package, $namespace, $copyright = "Drovio")
	{
		// Normalize attributes
		$library = trim($library);
		$package = trim($package);
		$namespace = trim($namespace);
		$namespace = str_replace("::", "\\", $namespace);
		$copyright = (empty($copyright) ? "Drovio" : $copyright);
		
		// Create Template
		$classStamp = array();
		$classStamp['library'] = (empty($library) ? "" : $library);
		$classStamp['package'] = (empty($package) ? "" : $package);
		$classStamp['namespace'] = (empty($namespace) ? "" : $namespace);
		$classStamp['copyright'] = 'Copyright (C) '.date('Y').' '.$copyright.'. All rights reserved.';
		
		$classStampCommentsList = array();
		
		foreach ($classStamp as $tag => $value)
			if (!empty($value))
				$classStampCommentsList[] = self::getDescriptionLine($value, $tag);
		
		array_splice($classStampCommentsList, count($classStampCommentsList)-1, 0, self::getDescriptionLine("", "", 0));
		$classStampCommentsText = implode("\n", $classStampCommentsList);
		
		// Comment content
		$classStampCommentsText = self::documentationComment($classStampCommentsText);
		
		return $classStampCommentsText;
	}
	
	/**
	 * Creates and returns a comment block that holds the objects class info, such as description, version, etc
	 * 
	 * @param	string	$title
	 * 		This value is presented as the object's title
	 * 
	 * @param	string	$description
	 * 		This value is presented as the object's description
	 * 
	 * @param	string	$version
	 * 		This value is presented as the object's version
	 * 
	 * @param	mixed	$created
	 * 		This value is presented as the object's creation date. Should be an integer or string timestamp
	 * 
	 * @param	mixed	$updated
	 * 		This value is presented as the object's last modification date. Should be an integer or string unformated timestamp
	 * 
	 * @param	string	$throws
	 * 		The exceptions thrown in the class. Should be a comma separated string.
	 * 
	 * @param	string	$deprecated
	 * 		This value is presented as the object's deprecation description.
	 * 
	 * @return	string
	 * 		The comment block
	 */
	private function getClassInfoComments($title, $description, $version, $created, $updated, $throws = "", $deprecated = "")
	{
		// Normalize attributes
		$title = trim($title);
		$description = trim($description);
		$version = trim($version, "-. \t\n\r\0\x0B");
		$created = trim($created);
		if (is_numeric($created))
			$created = intval($created);
		$updated = trim($updated);
		if (is_numeric($updated))
			$updated = intval($updated);
		
		// Create Template
		$classInfo = array();
		$classInfo['title'] = (empty($title) ? "{title}" : $title);
		$classInfo['description'] = (empty($description) ? "{description}" : $description);
		$classInfo['version'] = (empty($version) ? "0.1-1" : $version);
		$classInfo['created'] = (empty($created) ? date("F j, Y, G:i (T)") : date("F j, Y, G:i (T)", $created));
		$classInfo['updated'] = (empty($updated) ? date("F j, Y, G:i (T)") : date("F j, Y, G:i (T)", $updated));
		
		if (!empty($throws))
			$classInfo['throws'] = trim($throws);
			
		if (!empty($deprecated))
			$classInfo['deprecated'] = trim($deprecated);
		
		$classInfoCommentsList = array();
		
		foreach ($classInfo as $tag => $value)
			$classInfoCommentsList[] = self::getDescriptionLine($value, $tag);
			
		$classInfoCommentsList[0] = self::getDescriptionLine($classInfo['title'], "", 0);
		$classInfoCommentsList[1] = self::getDescriptionLine($classInfo['description'], "", 0);
		$spliceIndex = 6;
		if (empty($classInfo['throws']))
			$spliceIndex = 5;
		if (!empty($classInfo['deprecated']))
		{
			$classInfoCommentsList[$spliceIndex] = self::getDescriptionLine($classInfo['deprecated'], "deprecated");
			array_splice($classInfoCommentsList, $spliceIndex, 0, self::getDescriptionLine("", "", 0));
		}
		array_splice($classInfoCommentsList, 2, 0, self::getDescriptionLine("", "", 0));
		array_splice($classInfoCommentsList, 1, 0, self::getDescriptionLine("", "", 0));
		
		$classInfoCommentsText = implode("\n", $classInfoCommentsList);
		
		// Fill template and comment content
		$classInfoCommentsText = self::documentationComment($classInfoCommentsText);
		
		return $classInfoCommentsText;
	}
	
	/**
	 * Creates and returns a comment block that holds a class' property info, namely description and type.
	 * 
	 * @param	string	$description
	 * 		This value is presented as the property's description.
	 * 
	 * @param	string	$type
	 * 		This value is presented as the property's type.
	 * 
	 * @return	string
	 * 		The comment block
	 */
	private function getPropertyComments($description, $type)
	{
		// Normalize attributes
		$type = trim($type);
		$description = trim($description);
		
		// Create Template
		$property = array();
		$property['type'] = (empty($type) ? "{type}" : $type);
		$property['description'] = (empty($description) ? "{description}" : $description);
		
		$propertyCommentsList = array();
		$propertyCommentsList[] = self::getDescriptionLine($property['description'], "", 0);
		$propertyCommentsList[] = self::getDescriptionLine($property['type'], "type");
		
		array_splice($propertyCommentsList, 1, 0, self::getDescriptionLine("", "", 0));
		
		$propertyCommentsText = implode("\n", $propertyCommentsList);
		
		// Fill template and comment content
		$propertyCommentsText = self::documentationComment($propertyCommentsText);
		
		return $propertyCommentsText;
	}
	
	/**
	 * Creates and returns a comment block that holds an objects method info, such as description, return value, etc
	 * 
	 * @param	string	$description
	 * 		This value is presented as the method's description.
	 * 
	 * @param	array	$args
	 * 		Holds the method parameters' info. Should have the following keys: 'type', 'name', 'description'.
	 * 
	 * @param	string	$return
	 * 		This value is presented as the method's return type.
	 * 
	 * @param	string	$returnDesc
	 * 		The description of the returning value, if not void
	 * 
	 * @param	string	$throws
	 * 		The exceptions thrown by the method. Should be a comma separated string
	 * 
	 * @param	string	$deprecated
	 * 		This value is presented as the method's deprecation description.
	 * 
	 * @return	string
	 * 		The comment block
	 */
	private function getMethodComments($description, $args, $return, $returnDesc = "", $throws = "", $deprecated = "")
	{
		// Normalize attributes
		$description = trim($description);
		
		// Create Template
		$method = array();
		$method['description'] = (empty($description) ? "{description}" : $description);
		foreach ($args as $key => $ar)
		{
			$method[$key]['arg'] = array( 'type' => trim($args[$key]['type']), 'name' => trim($args[$key]['name']) );
			$method[$key]['arg']['type'] = (empty($method[$key]['arg']['type']) ? "{type}" : $method[$key]['arg']['type']);
			$method[$key]['arg_desc'] = trim($args[$key]['description']);
			$method[$key]['arg_desc'] = (empty($method[$key]['arg_desc']) ? "{description}" : $method[$key]['arg_desc']);
		}
		$method['return'] = trim($return);
		$method['return'] = (empty($method['return']) ? "{type}" : $method['return']);
		if ($method['return'] != "void")
		{
			$method['returnDesc'] = trim($returnDesc);
			$method['returnDesc'] = (empty($method['returnDesc']) ? "{description}" : $method['returnDesc']);
		}
		if (!empty($throws))
			$method['throws'] = trim($throws);
		if (!empty($deprecated))
			$method['deprecated'] = trim($deprecated);
		
		$methodCommentsList = array();
		
		$methodCommentsList[] = self::getDescriptionLine($method['description'], "", 0);
		foreach ($args as $key => $ar)
		{					
			$methodCommentsList[] = self::getDescriptionLine($method[$key]['arg'], "param");
			$desc = self::getDescriptionLine($method[$key]['arg_desc'], "", 2);
			preg_match("/^[\t ]*/", $desc, $tabs);
			$desc = str_replace("\n", "\n".$tabs[0], $desc);
			$methodCommentsList[] = $desc;
		}
		
		$methodCommentsList[] = self::getDescriptionLine($method['return'], "return");
		if ($method['return'] != "void")
		{
			$desc = self::getDescriptionLine($method['returnDesc'], "", 2);
			preg_match("/^[\t ]*/", $desc, $tabs);
			$desc = str_replace("\n", "\n".$tabs[0], $desc);
			$methodCommentsList[] = $desc;
		}
		if (!empty($throws))
		{
			$methodCommentsList[] = self::getDescriptionLine($method['throws'], "throws");
		}
		$c = count($methodCommentsList);
		if (!empty($deprecated))
		{
			$methodCommentsList[] = self::getDescriptionLine($method['deprecated'], "deprecated", 1);
			array_splice($methodCommentsList, $c, 0, self::getDescriptionLine("", "", 0));
		}
		if (!empty($throws))
		{
			$c = $c - 1;
			array_splice($methodCommentsList, $c, 0, self::getDescriptionLine("", "", 0));
		}
		if ($method['return'] == "void")
			$c = $c + 1;
		
		foreach ($args as $key => $ar)
		{
			$num = array_search($key,array_keys($args));
			array_splice($methodCommentsList, $c - (2+$num * 2), 0, self::getDescriptionLine("", "", 0));
		}
		array_splice($methodCommentsList, 1, 0, self::getDescriptionLine("", "", 0));
		
		
		$methodCommentsText = implode("\n", $methodCommentsList);
		
		foreach ($args as $key => $ar)
		{
			$method['type'.$key] = $method[$key]['arg']['type'];
			$method['name'.$key] = $method[$key]['arg']['name'];
			$method['arg_desc'.$key] = $method[$key]['arg_desc'];
			unset($method[$key]);
		}
	
		// Fill template and comment content
		$methodCommentsText = self::documentationComment($methodCommentsText);
		
		return $methodCommentsText;
	}
	
	/**
	 * Creates a documentation comment block
	 * 
	 * @param	string	$content
	 * 		Content of the block
	 * 
	 * @return	string
	 * 		The comment block
	 */
	public static function documentationComment($content)
	{
		$content = " * ".preg_replace("/[\n\r]/", "$0 * ", $content);
		return self::$ml_comment_start."*\n".$content."\n ".self::$ml_comment_end."\n";
	}
}
//#section_end#
?>