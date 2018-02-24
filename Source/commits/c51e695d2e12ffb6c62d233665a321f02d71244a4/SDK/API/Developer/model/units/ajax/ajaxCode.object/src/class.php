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
importer::import("API", "Content", "resources");

use \API\Platform\DOM\DOMParser;
use \API\Content\resources;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	July 3, 2013, 12:56 (EEST)
 * @revised	July 3, 2013, 12:56 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\ajax\ajaxPage instead.
 */
class ajaxCode
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	protected $ajaxMappingFile = "/ajax/_dir.xml";
	
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
	 * @return	void
	 */
	protected function _get_fileName()
	{
		//return hash($this->hashAlgorithm, $this->_get_directory());
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	protected function _build_privateCode()
	{
		// Build Private Section
		
		//_____ Get header
		// need to create headers for ajax use in proper path!!!!!!!!!
		$path = systemRoot.resources::PATH."/Developer/Modules/Headers/private.inc";
		
		$private = fileManager::get_contents($path);
		$private = phpParser::unwrap($private);
		
		//_____ Prepend Policy Codes
		/*$policies = "";
		$policies .= '$policyCode = '.$id.";\n\n";
		
		//_____ Set Inner Policy Codes
		$policies .= '$innerPolicyCodes = array();'."\n";
		foreach ($this->inner as $key => $value)
			$policies .= '$innerPolicyCodes[\''.$key.'\'] = '.$value.";\n";
		
		//_____ Merge
		$private = $policies.$private;*/
		//_____ Section
		$private = $this->wrap_section($private, "private");
		
		return $private;
	}
	
	
	protected function _update_directoryMapping()
	{
		$mapping_builder = new DOMParser();
		$mapping_builder->load($this->ajaxMappingFile, true);
			
		// find proper group and remove it
		$ajax_root = $mapping_builder->evaluate("//ajaxStructure")->item(0);
		
		// create necessary groups
		// check what this->directory has
		$levels = preg_split("/\/*/", trim($this->directory, "/"));

		$curent_level = $ajax_root;
		foreach ($levels as $level)
		{
			$base = $mapping_builder->evaluate("group[@title='$level']", $curent_level)->item(0);
			$curent_level = $base;
		}
		
		// add proper attributes here, like id etc.
		$item = $mapping_builder->get("item");
		DOMParser::append($curent_level, $item);
		
		$mapping_builder->save($this->ajaxMappingFile, true);
	}
}
//#section_end#
?>