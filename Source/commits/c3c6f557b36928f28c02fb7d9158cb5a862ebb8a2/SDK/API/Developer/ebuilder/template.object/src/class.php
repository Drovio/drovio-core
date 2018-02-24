<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder;

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
 * @namespace	\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Developer", "resources::layouts::ebuilderLayout");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "profiler::logger");

use \API\Developer\profiler\logger;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \API\Developer\resources\layouts\ebuilderLayout;
use \API\Resources\DOMParser;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\parsers\cssParser;


/**
 * Template
 * 
 * Object class for ebuilder template.
 * 
 * @version	{empty}
 * @created	June 4, 2013, 23:00 (EEST)
 * @revised	December 24, 2013, 13:10 (EET)
 */
class template
{
	
	/**
	 * The starting default code of a new template object. Set to 1 means "project mode"
	 * 
	 * @type	integer
	 */
	private $defaultStatusCode = '1';	
	

	/**
	 * The current loaded template's id
	 * 
	 * @type	integer
	 */
	private $workingFolder = '';

	/**
	 * Mapping array for all xml prime nodes tags used
	 * 
	 * @type	array
	 */
	private $xmlRoots = array(
				'root' => 'settings',
				'pageStructureRoot' => 'pageStructure',
				'themeRoot' => 'theme',
				'sequenceRoot' => 'sequence');
	
	/**
	 * Global DOMparser object, loaded with templates setting.xml
	 * 
	 * @type	DOMparser
	 */
	private $parser;
	
	/**
	 * Mapping array for all xml secondary nodes tags used
	 * 
	 * @type	array
	 */
	private $xmlNodes = array(
				'pageStructure' => 'ps',
				'theme' => 'th',
				'sequencePage' => 'pg');
	
	
	/**
	 * Mapping template object possible status. Represents a lectical presentation of bmapp_projectStatus codes.
	 * 
	 * @type	array
	 */
	private $readOnly;
	
	/**
	 * Constructor Method
	 * 
	 * @param	{type}	$path
	 * 		{description}
	 * 
	 * @param	{type}	$readOnly
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($path, $readOnly = FALSE)
	{
		$this->workingFolder = $path;		
		$this->readOnly = $readOnly;
				
		logger::log(get_class($this).": Caller. (".$this->getCaller().")", logger::DEBUG);
		
		//$this->repositoryPath = $objectManager->getRepositoryPath();
		//$this->deployPath = $objectManager->getDeployPath();
		
		//$this->namePrefix = $objectManager->getNamePrefix();
		//$this->folderExt = $objectManager->getFolderExt();
	}
	
	/**
	 * {description}
	 * 
	 * @return	bolean
	 * 		{description}
	 */
	public function init()
	{
		$this->parser = new DOMParser();		
		try
		{
			// Load index file
			$this->parser->load($this->workingFolder."/.template"."/index.xml", FALSE, FALSE);
		}
		catch (Exception $ex)
		{
			logger::log(get_class($this).": Document file not Loaded. (".$ex.")", logger::DEBUG);
			return FALSE;		
		}
				
		$this->id = '';
		$this->title = '';
		$this->description = '';
		$this->projectCategory = '';		
		$this->projectType = '';		
		$this->projectStatus = '';
	}
	
	/**
	 * {description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function initializeRepository()
	{
		$objectFolderName = $this->namePrefix.$this->templateId.$this->folderExt; 
		$this->workingFolder = $this->repositoryPath."/".$objectFolderName;
		if ($success)
		{
			//Create Template Folder		
			$success = folderManager::create($this->workingFolder);
			// Create Configuration Hidden Folder
			$success = folderManager::create($this->workingFolder."/.template");
			// Create Page Structures Root Folder
			$success = folderManager::create($this->workingFolder."/Pages");
			// #Create Themes Root Folder
			$success = folderManager::create($this->workingFolder."/Themes");
			// #Create Assets Folder
			$success = folderManager::create($this->workingFolder."/Media");
			// #Create Thumbs Folder
			//$success = folderManager::create($this->workingFolder."/Thumbs");
		}
		
		// ##Create Template Settings XML
		$this->parser = new DOMParser();		
		$root = $this->parser->create("settings");
		$this->parser->append($root);		
		$success = $this->parser->save($this->workingFolder."/.template"."/", "index.xml", FALSE);
		
		if($success)
			$this->initSettingsXml();
		
		return TRUE;	
	}
	
	/**
	 * Building the settings.xml file at template creation phase
	 * 
	 * @return	void
	 */
	private function initSettingsXml()
	{ 
		// Return FALSE and exit, in error or if template is in deploy state	
		if($this->readOnly)
			return FALSE;
		
		$root = $this->parser->evaluate("//settings")->item(0);
			
		//Add settings xml pageStructures root entry
		$pageStructureEntry = $this->parser->create($this->xmlRoots['pageStructureRoot']);
		$this->parser->append($root, $pageStructureEntry);
		
		//Add settings xml themes root entry
		$pageStructureEntry = $this->parser->create($this->xmlRoots['themeRoot']);
		$this->parser->append($root, $pageStructureEntry);
		
		//Add settings xml Page Sequence root entry
		$pageStructureEntry = $this->parser->create($this->xmlRoots['sequenceRoot']);
		$this->parser->append($root, $pageStructureEntry);
		
		// Save File
		$saveFlag = $this->parser->save($this->workingFolder."/.template/", "index.xml", FALSE);	
	}
	
	/**
	 * {description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getSettingsXML()
	{		
		$settingsRoot = $this->parser->evaluate("//"."settings")->item(0);		
		return trim($this->parser->innerHTML($settingsRoot));
	}
	
	public function deploy($deployDest)
	{
		// This function / future can only be used if template is in project state	
		if($this->readOnly)
			return FALSE;
			
		// Copy template project last version to system template folder
		folderManager::copy($this->workingFolder, $deployDest);
		
		//create thumbs
		// #Create Thumbs Folder
		//$success = folderManager::create($this->workingFolder."/Thumbs");
	}
	
	/**
	 * {description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getAllStructures()
	{
		$settingsRoot = $this->parser->evaluate("//"."settings")->item(0);	
		
		$pageStructureArray = array();			
		$pageStructureRoot = $this->parser->evaluate('//'.$this->xmlRoots['pageStructureRoot'], $settingsRoot)->item(0);
		if(is_null($pageStructureRoot))
			return FALSE;
		$pageStructureNodes = $this->parser->evaluate('//'.$this->xmlNodes ['pageStructure'], $pageStructureRoot);		
		foreach ($pageStructureNodes as $node)
		{
			array_push($pageStructureArray, $this->parser->attr($node, 'name'));
		}
		return $pageStructureArray;	
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$pageStructureName
	 * 		{description}
	 * 
	 * @param	{type}	$format
	 * 		{description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getStructureXML($pageStructureName = '', $format = TRUE)
	{					
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$root = $this->parser->evaluate('//'.$this->xmlRoots['pageStructureRoot'])->item(0);		
		$pageStructure = $this->parser->evaluate($this->xmlNodes['pageStructure'].'[@name=\''.$pageStructureName.'\']', $root)->item(0);
		
		if(is_null($pageStructure))
			return FALSE;
		
		try
		{
			// Load structure file
			$this->parser->load($this->workingFolder."/Pages/".$pageStructureName."/structure.xml", FALSE, $format);
			$root = $this->parser->evaluate('//'.$this->xmlRoots['pageStructureRoot'])->item(0);
		}
		catch (Exception $ex)
		{
			logger::log(get_class($this).": Document file not Loaded. (".$ex.")", logger::DEBUG);
			return FALSE;
		}
		
		return trim($this->parser->innerHTML($root));
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$pageStructureName
	 * 		{description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getStructureCSS($pageStructureName = '')
	{
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$root = $this->parser->evaluate('//'.$this->xmlRoots['pageStructureRoot'])->item(0);		
		$pageStructure = $this->parser->evaluate($this->xmlNodes['pageStructure'].'[@name=\''.$pageStructureName.'\']', $root)->item(0);
		
		if(is_null($pageStructure))
			return FALSE;
	
		return fileManager::get_contents($this->workingFolder."/Pages/".$pageStructureName."/style.css");
	}
	
	/**
	 * {description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getAllThemes()
	{
		$settingsRoot = $this->parser->evaluate("//"."settings")->item(0);
			
		$themesArray = array();
		$themeRoot = $this->parser->evaluate('//'.$this->xmlRoots['themeRoot'])->item(0);
		if(is_null($themeRoot))
			return FALSE;
		$themeNodes = $this->parser->evaluate('//'.$this->xmlNodes ['theme'], $themeRoot );		
		foreach ($themeNodes as $node)
		{
			array_push($themesArray, $this->parser->attr($node, 'name'));
		}
		
		return $themesArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$themeName
	 * 		{description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getThemeCSS($themeName = '')
	{
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$root = $this->parser->evaluate('//'.$this->xmlRoots['pageStructureRoot'])->item(0);		
		$theme = $this->parser->evaluate($this->xmlNodes['pageStructure'].'[@name=\''.$themeName.'\']', $root)->item(0);
		
		if(is_null($theme))
			return FALSE;	
		
		return fileManager::get_contents($this->workingFolder."/Themes/".$themeName.".css");
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$rootRelative
	 * 		{description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getAssetsPath($rootRelative = TRUE)
	{
		$assetsPath = $this->workingFolder."/Media";
		if(!$rootRelative)
		{
			// Strip the system root
			$assetsPath = str_replace(systemRoot, '' , $assetsPath);
		}
		
		return $assetsPath;
	}
	
	/**
	 * {description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getPageSequence()
	{
		$pagewArray = array();	
		$pageSequenceRoot = $this->parser->evaluate('//'.$this->xmlRoots['sequenceRoot'])->item(0);
		if(is_null($themeRoot))
			return FALSE;
		$pages = $this->parser->evaluate('//'.$this->xmlNodes ['sequencePage'], $pageSequenceRoot);		
		foreach ($pages as $node)
		{
			$pagewArray[$this->parser->attr($node, 'name')] = $this->parser->attr($node, 'page');
		}
		
		return $pagewArray;
	}
	
	/**
	 * {description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function themeThumbs()
	{
		//array[themeName, array
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$pageStructureName
	 * 		{description}
	 * 
	 * @param	{type}	$layoutName
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function createPageStructure($pageStructureName, $layoutName)
	{
		// Return FALSE and exit, in error or if template is in deploy state	
		if($this->readOnly)
			return FALSE;		
		
		//Check setting.mxl if $pageStructureName exists		
		$root = $this->parser->evaluate("//".$this->xmlRoots['pageStructureRoot'])->item(0);
		
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$pageStructure = $this->parser->evaluate($this->xmlNodes['pageStructure'].'[@name=\''.$pageStructureName.'\']', $root)->item(0);
		
		if(!is_null($pageStructure))
		{
			//exists 
			return;		
		}
		
		// Load layout object
		$layoutManager = new ebuilderLayout($layoutName);		
		$model = $layoutManager->getModel();
		$structure = $layoutManager->getStructure(TRUE);
		
		// Create Single Structure Folder IN Page Structures Folder
		if (!file_exists($this->workingFolder."/".$pageStructureName));
			$success = folderManager::create($this->workingFolder."/Pages/".$pageStructureName);		
		
		// Copy Files from Layouts Folder to Structure Folder
		$status = $this->savePageStructureCSS($pageStructureName, $model);
		$status = $this->savePageStructureXML($pageStructureName, $structure);
		
		//Add settings xml pageStructureName entry
		$pageStructureEntry = $this->parser->create($this->xmlNodes['pageStructure']);
		$this->parser->attr($pageStructureEntry, "name", $pageStructureName);
		$this->parser->append($root, $pageStructureEntry);
		
		// Save File
		$saveFlag = $this->parser->save($this->workingFolder."/.template/", "index.xml", FALSE);
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$themeName
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function createTheme($themeName)
	{
		// Return FALSE and exit, in error or if template is in deploy state	
		if($this->readOnly)
			return FALSE;		
		
		//Check setting.mxl if $pageStructureName exists		
		$root = $this->parser->evaluate("//".$this->xmlRoots['themeRoot'])->item(0);
						
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$theme = $this->parser->evaluate($this->xmlNodes['theme'].'[@name=\''.$themeName.'\']', $root)->item(0);		
		if(!is_null($theme))
		{		
			//exists 
			return;			
		}
		
		// Create Empty Theme
		$status = $this->saveTheme($themeName, '');		
		
		//Add settings xml pageStructureName entry	
		$themeEntry = $this->parser->create($this->xmlNodes['theme']);
		$this->parser->attr($themeEntry, "name", $themeName);
		$this->parser->append($root, $themeEntry);
		
		// Save File
		$status = $this->parser->save($this->workingFolder."/.template"."/", "index.xml", TRUE); 
		
		return $status;
	}
	
	
	
	
	
	
		
	/**
	 * {description}
	 * 
	 * @param	{type}	$pageStructureName
	 * 		{description}
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function savePageStructureXML($pageStructureName = '', $code = '')
	{
		// This function / future can only be used if template is in project state	
		if($this->readOnly)
			return FALSE;
		
		$dom_parser = new DOMParser();
		
		$code = phpParser::clearCode($code);
		
		$root = $dom_parser->create($this->xmlRoots['pageStructureRoot']);
		$dom_parser->attr($root , "id", $pageStructureName);
		$dom_parser->append($root);
		$dom_parser->innerHTML($root, $code);
		
		return $dom_parser->save($this->workingFolder."/Pages"."/".$pageStructureName."/", "structure.xml", FALSE);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$pageStructureName
	 * 		{description}
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function savePageStructureCSS($pageStructureName = '', $code = '')
	{
		// This function / future can only be used if template is in project state	
		if($this->readOnly)
			return FALSE;	
			
		// If code is empty, create an empty CSS file
		if ($code == '')
			$code = phpParser::get_comment("Write Your CSS Style Rules Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Save css file
		return fileManager::create($this->workingFolder."/Pages"."/".$pageStructureName."/style.css", $code);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$themeName
	 * 		{description}
	 * 
	 * @param	{type}	$code
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function saveTheme($themeName, $code)
	{
		// This function / future can only be used if template is in project state	
		if($this->readOnly)
			return FALSE;	
	
		// If code is empty, create an empty CSS file
		if ($code == '')
			$code = phpParser::get_comment("Write Your CSS Style Rules Here", $multi = TRUE);
		
		// Clear Code
		$code = phpParser::clearCode($code);
		
		// Save css file
		return fileManager::create($this->workingFolder."/Themes"."/".$themeName.".css", $code);
	}
		
	
			
	
		
	/**
	 * Deletes the given folder in the template contex, if given folder is empty deletes the entire template folder. Recursive deletetion is optional
	 * 
	 * @param	string	$folderName
	 * 		The name of folder which goint to be deleted
	 * 
	 * @param	boolean	$recursive
	 * 		If true, deletes the folder and its contents
	 * 
	 * @return	boolean
	 * 		Return true on success, false elsewhere
	 */
	private function deleteTemplateFolder($folderName = '', $recursive = TRUE)
	{
	
		if(empty($this->templateFolderName)) 
		{
 			return FALSE;			
		}
		
		if(empty($folderName))
		{
			$folderName = $this->templateFolderName;
		}
		else		
		{
			$folderName = $this->templateFolderName."/".$folderName;
		}	
		if($recursive)
		{
			if (file_exists($this->templatesFolderRoot."/".$folderName))
				$status = folderManager::remove_full($this->templatesFolderRoot."/".$folderName);
		}
		
		return $status;	
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$pageStructureName
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function deletePageStructure($pageStructureName)
	{
		// This function / future can only be used if template is in project state	
		if($this->readOnly)
			return FALSE;
					
		//Delete index entry			
		// Get index root
		$root = $this->parser->evaluate('//'.$this->xmlRoots['pageStructureRoot'])->item(0);
				
		if(is_null($root))
			return FALSE;	
		
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$pageStructure = $this->parser->evaluate($this->xmlNodes['pageStructure'].'[@name=\''.$pageStructureName.'\']', $root)->item(0);
		
		logger::log("Found It ".$pageStructureName, logger::DEBUG);
		$status = FALSE;
		if(!is_null($pageStructure))
		{
			
			//Delete entry
			$this->parser->replace($pageStructure, NULL);
			// Save File
			$saveFlag = $this->parser->save($this->workingFolder."/.template/", "index.xml", FALSE);
			
			logger::log("Delete It ".$this->workingFolder."/Pages/".$pageStructureName, logger::DEBUG);			
			//Delete Directory
			if (file_exists($this->workingFolder."/Pages/".$pageStructureName))
				$status = folderManager::remove_full($this->workingFolder."/Pages/".$pageStructureName);
		}
		
		return $status;		
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$themeName
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function deleteTheme($themeName)
	{
		// This function / future can only be used if template is in project state	
		if($this->readOnly)
			return FALSE;			
		
		//Delete index entry			
		// Get index root
		$root = $this->parser->evaluate('//'.$this->xmlRoots['themeRoot'])->item(0);
		
		
		if(is_null($root))
			return FALSE;		
		
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$theme = $this->parser->evaluate($this->xmlNodes['theme'].'[@name=\''.$themeName.'\']', $root)->item(0);
		
		$status = FALSE;
		if(!is_null($theme))
		{		
			//Delete entry
			$this->parser->replace($theme, NULL);
			// Save File
			$saveFlag = $this->parser->save($this->workingFolder."/.template/", "index.xml", FALSE);
			
			//Delete Theme
			if (file_exists($this->workingFolder."/Themes/".$themeName.".css"))
				$status = fileManager::remove($this->workingFolder."/Themes/".$themeName.".css");
		}
		return $status;		
	}
	
	
	/**
	 * {description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function getInfo()
	{
		$infoArray = array();
		$infoArray['id'] = $this->id;
		$infoArray['title'] = $this->title;
		$infoArray['description'] = $this->description;
		$infoArray['projectCategory'] = $this->projectCategory;
		$infoArray['projectType'] = $this->projectType;	
		$infoArray['projectStatus'] = $this->projectStatus;
			
		return $infoArray;
	}
	
	
	/**
	 * {description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function getSimpleArea()
	{
		$parser = new DOMParser();
		$area = $parser->create('div');
		return $parser->getXML();
	}
	/**
	 * -----------------------------------------------------------------DELETE
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function getEditableArea()
	{
		$parser = new DOMParser();
		$area = $parser->create('div');
		$parser->data($area, "edit", array('editable' => 1));
		return $parser->getXML();
	}
	/**
	 * {description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function getGlobalArea()
	{
		$parser = new DOMParser();
		$area = $parser->create('div');
		$parser->data($area, "scope", array('global' => 1));
		return $parser->getXML();
	}

	/**
	 * validates page structure integrity
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function validate($structure)
	{
		$parser = new DOMParser();
		$parser->loadContent($structure, DOMParser::XML);
		
		// A gloabal area cannot contain another global area
		
		// An editable area cannot have childrens
	}
	
	private function getCaller() 
	{		
		$class = '';
		$trace = debug_backtrace();	
    
		if (isset($trace[3]['class'])) 
		{
			$class = $trace[3]['class'];
		} 
		else if (isset($trace[2]['class'])) 
		{
			$class = $trace[2]['class'];
		}
    
		if($class != '')
		{
			$preg = '[^/]+$';
			preg_match($preg, $class, $matches);
			print_r($matches);
		
			$className = "c_".$matches[0]."_".$matches[1];
		}
		else
		{
			$className = "";
		}
		return($className);
	}
}
//#section_end#
?>