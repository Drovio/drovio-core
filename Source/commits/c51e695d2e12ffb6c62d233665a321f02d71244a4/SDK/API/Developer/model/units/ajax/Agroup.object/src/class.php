<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\ajax;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\model\units\ajax
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOMParser");

use \API\Platform\DOM\DOMParser;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	July 3, 2013, 12:56 (EEST)
 * @revised	July 3, 2013, 12:56 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\ajaxDirectory instead.
 */
class Agroup
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const FILE = "/Mapping/ajax.xml";
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $description;
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function __construct() 
	{
		$this->ajaxMappingFile = systemRoot.$this->ajaxMappingFile;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_directoryName($id)
	{
		$ajax_parser = new DOMParser();
		$ajax_parser->load($this->ajaxMappingFile, true);
		
		$group = DOMParser::find($id)->parentNode;
		
		return $group->getAttribute("title");
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$ajax_id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function get_trail($ajax_id = "")
	{
		$ajax_parser = new DOMParser();
		$ajax_parser->load($this->ajaxMappingFile, true);
		$group = DOMParser::find($id)->parentNode;
		
		$path = "";
		while (!is_null($group->parentNode))
		{
			$path = $group->getAttribute("title")."/".$path;
			$group = $group->parentNode;
		}
		
		return $path;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @param	{type}	$path
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function create($description, $path = "")
	{
		parent::create(systemRoot."/ajax/".$path."/".$description."/");
		
		// add group element in mapping file
		$mapping_builder = new DOMParser();
		
		// Create mapping file if not exists
		if (!file_exists($this->ajaxMappingFile))
		{			
			// Create root element
			$root = $mapping_builder->get("ajaxStructure");
			DOMParser::append($root);
			
			// Save file
			$mapping_builder->save($this->ajaxMappingFile, true);
		}
		
		$mapping_builder->load($this->ajaxMappingFile, true);
		$ajax_root = $mapping_builder->evaluate("//ajaxStructure")->item(0);
		
		// create necessary groups
		$levels = preg_split("/\/*/", trim($path."/".$description, "/"));

		$curent_level = $ajax_root;
		foreach ($levels as $level)
		{
			$base = $mapping_builder->evaluate("group[@title='$level']", $curent_level)->item(0);
			if (is_null($base))
			{
				$base = $mapping_builder->get("group");
				DOMParser::attr($base, "title", $level);
				DOMParser::append($curent_level, $base);
			}
			$curent_level = $base;
		}
		
		$mapping_builder->save($this->ajaxMappingFile, true);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @param	{type}	$path
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function delete($description, $path = "")
	{
		$success = parent::remove(systemRoot."/ajax/".$path."/".$description."/");
		
		// remove from xml
		if ($success)
		{
			$mapping_builder = new DOMParser();
			$mapping_builder->load($this->ajaxMappingFile, true);
			
			// find proper group and remove it
			$ajax_root = $mapping_builder->evaluate("//ajaxStructure")->item(0);
			
			// create necessary groups
			$levels = preg_split("/\/*/", trim($path."/".$description, "/"));
	
			$curent_level = $ajax_root;
			foreach ($levels as $level)
			{
				$base = $mapping_builder->evaluate("group[@title='$level']", $curent_level)->item(0);
				$curent_level = $base;
			}
			$curent_level->parentNode->removeChild($curent_level);
			
			$mapping_builder->save($this->ajaxMappingFile, true);
		}
		
		return $success;
	}
}
//#section_end#
?>