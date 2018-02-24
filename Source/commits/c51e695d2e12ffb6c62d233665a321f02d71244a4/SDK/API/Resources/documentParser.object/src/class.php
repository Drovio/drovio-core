<?php
//#section#[header]
// Namespace
namespace API\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "resources");
importer::import("API", "Resources", "geoloc::locale");

use \API\Resources\filesystem\fileManager;
use \API\Resources\resources;
use \API\Resources\DOMParser;
use \API\Resources\geoloc\locale;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	October 25, 2013, 17:49 (EEST)
 * @revised	October 25, 2013, 17:49 (EEST)
 */
class documentParser
{


	private $locale = '';
	

	/**
	 * {description}
	 * 
	 * @param	{type}	$file
	 * 		{description}
	 * 
	 * @param	{type}	$text
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function load($file, $text = TRUE)
	{
		// Normalize File Path
		$file = str_replace("::", "/", $file);
		
		// Get current locale
		if(empty($this->locale))
			$this->setLocale(locale::get());
		
		$documentPath = systemRoot.resources::PATH."/Documents/".$this->locale."/".$file;
	
		// If file not exists, try default locale
		if (!file_exists($documentPath))
			$this->setLocale(locale::getDefault());
		
		// Create full path
		$documentPath = systemRoot.resources::PATH."/Documents/".$this->locale."/".$file;
		
		// Get Content
		$content = fileManager::get($documentPath);
			
		// If only text requested, send text
		if ($text)
			return $content;
			
		$parser = new DOMParser();
		// Wrap in a div element
		$element = $parser->create("div");
		
		$parser->innerHTML($element, $content);
		
		return $element;
	}
	
	public function setLocale($locale)
	{
		$this->locale = $locale;
	}
}
//#section_end#
?>