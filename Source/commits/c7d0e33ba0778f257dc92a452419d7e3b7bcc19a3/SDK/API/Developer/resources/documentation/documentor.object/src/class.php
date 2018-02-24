<?php
//#section#[header]
// Namespace
namespace API\Developer\resources\documentation;

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
 * @namespace	\resources\documentation
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "resources");

use \API\Resources\DOMParser;
use \API\Resources\resources;

// FOR DEVELOPING
importer::import("API", "Profile", "tester");
use API\Profile\tester;
// FOR DEVELOPING

importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger;


/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	October 10, 2013, 10:59 (EEST)
 * @revised	October 10, 2013, 10:59 (EEST)
 */
class documentor
{
	const TEST_FILEPATH = "Documentation/";
	
	private $testPath = '';
	
	private $parser;

	/**
	 * The contructor method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->parser = NULL;
		
		// FOR DEVELPMENT
		$trunk = tester::getTrunk();
		
		$this->testPath = systemRoot.$trunk."/".self::TEST_FILEPATH;
	}
	
	
	
	
	
	public function loadFile($filepath, $rootRelative = TRUE)
	{
		// Load Object Documentation
		$this->parser = new DOMParser();
		
		// Check Existance
		try
		{
			$this->parser->load($filepath, $rootRelative);
		}
		catch(Exception $ex)
		{
			$this->parser = NULL;
			logger::log(get_class($this).": Document file not Loaded. (".$ex.")", logger::INFO);
			return FALSE;
		}
		return TRUE;
	}
	
	
	public function loadContent($manual)
	{
		// Load Object Documentation
		$this->parser = new DOMParser();
		
		// Check Existance
		try
		{
			$this->parser->loadContent($manual, "XML");
		}
		catch(Exception $ex)
		{
			$this->parser = NULL;
			logger::log(get_class($this).": Document content not Loaded. (".$ex.")", logger::INFO);
			return FALSE;
		}
		return TRUE;
	}
	
	public function create($library, $package, $namespace = "", $objectName)
	{
		$this->parser = new DOMParser();
		$manual = $this->parser->create("manual");
		$this->parser->append($manual);			
		
		$this->parser->attr($manual, "count", '0');

		$this->parser->attr($manual, "library", $library);
		$this->parser->attr($manual, "package", $package);
		$this->parser->attr($manual, "objectName", $objectName);	
		
		$namespaceAttr = "\\".str_replace("::", "\\", $namespace);
		$this->parser->attr($manual, "namespace", $namespaceAttr);
		
	}
	
	public function getDoc()
	{
		if(!(is_null($this->parser)))
		{
			return $this->parser->getXML();
		}
	}
	
	public function deleteSection($pos)
	{
		$root =  $this->parser->evaluate("/manual")->item(0);
		
		$count = $this->parser->attr($root, 'count');
		if(empty($count) || $count == 0)
		{
			return FALSE;
		}
		$count--;
		$this->parser->attr($root, 'count', $count);
		
		$section = $this->parser->evaluate("section[@pos='".$pos."']", $root)->item(0); 
		if(is_null($section))
		{	
			echo "Not fount";
			return FALSE;
		}
		$this->parser->replace($section, NULL);
		echo "|".($pos + 1)."__".($count + 1)."|";
		
		for($i = ($pos + 1); $i <= ($count + 1); $i++)
		{
			$section = $this->parser->evaluate("section[@pos='".$i."']", $root)->item(0);
			$this->parser->attr($section, "pos", $i-1);
		}
		return $count;
	}
	
	public function getSections()
	{
		$sectionsArray = array();
		 
		// Get Public Properties
		$sections = $this->parser->evaluate("/manual/section");
		foreach ($sections as $sect)
			$sectionsArray[$this->parser->attr($sect, "pos")] = $this->getSectionArray($sect);
			
		return $sectionsArray;
	}
	
	public function getSectionArray($sect)
	{
		$section = array();
		$section['type'] = $this->parser->attr($sect, "type");
		$section['content'] = $sect->nodeValue;
		
		return $section;
	}
	
	public function updateSection($type, $content, $pos  = '')
	{
		$root =  $this->parser->evaluate("/manual")->item(0);
		
		// If position pos is empty add a new section
		if(empty($pos))
		{	
			$count = $this->parser->attr($root, 'count');
			$pos = ((empty($count)) ? 0 : $count);
			$pos++;
			$this->parser->attr($root, 'count', $pos);
		}

		$section = $this->parser->evaluate("section[@pos='".$pos."']", $root)->item(0); 
		if(!is_null($section))
		{
			$this->parser->replace($section, NULL);
		}	
				
		$section = $this->parser->create("section");
		$this->parser->append($section);			
		
		$this->parser->attr($section, "type", $type);
		$this->parser->attr($section, "pos", $pos);
		
		$this->parser->innerHTML($section, $content);

		$this->parser->append($root, $section);	
		
		return 	$pos;
	}
	public function updateFile()
	{
		return $this->parser->update(TRUE);
	}
	public function save()
	{
		$this->parser->save($this->testPath."/doc.xml", '', TRUE);
	}

public function getPath()
{
 return $this->testPath."/doc.xml";
}


}
//#section_end#
?>