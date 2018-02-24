<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder;

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
importer::import("API", "Developer", "ebuilder::templateManager");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Resources", "geoloc::locale");

importer::import("API", "Security", "account");

use \API\Developer\profiler\logger;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;
use \API\Developer\resources\layouts\ebuilderLayout;
use \API\Resources\DOMParser;
use \API\Developer\ebuilder\templateManager;
use \API\Developer\content\document\parsers\phpParser;
use \API\Developer\content\document\parsers\cssParser;
use \API\Resources\geoloc\locale;


use \API\Security\account;
/**
 * template
 * 
 * Object class for ebuilder template and template group manipulation
 * 
 * @version	{empty}
 * @created	June 4, 2013, 23:00 (EEST)
 * @revised	September 24, 2013, 12:22 (EEST)
 */
class template
{
	
	
	
	
	/**
	 * Mapping template object possible status. Represents a lectical presentation of bmapp_projectStatus codes.
	 * 
	 * @type	array
	 */
	private static $status = array(
			'deploy' => '2',
			'project' => '1',
			'revise' => '3');
	
	
	/**
	 * Mapping (string => code) array of all db queries used by the class.
	 * 
	 * @type	array
	 */
	private static $query = array(
			'getAllTemplates' => '1008570389',
			'getUserTemplates' => '1947306124',
			'getTemplateInfo' => '1053660758',
			'getGroupInfo' => '1034585811',
			'getAllSiteTypes' => '981346525',
			'addTemplate' => '1310198126',
			'addTemplateLiteral' => '1271314484',
			'deleteTemplate' => '1875721238',
			'deleteAllTemplateLiteral' => '1168316185',
			'updateTemplateLiteral' => '821658206',
			'updateTemplateInfo' => '270118125',
			'updateGroupLiteral' => '1529248592');			
	
	/**
	 * The default locale code of group and template literals
	 * 
	 * @type	integer
	 */
	private static $defaultLocale;
	
	/**
	 * Current template project object path
	 * 
	 * @type	string
	 */
	private $namePrefix ='';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $folderExt = '';
	
	/**
	 * The starting default code of a new template object. Set to 1 means "project mode"
	 * 
	 * @type	integer
	 */
	private $defaultStatusCode = '1';	
	
	/**
	 * Flag for indicating whatever function are allowed to executed depenting on template status, deployed or else.
	 * 
	 * @type	string
	 */
	private $isDeployed = FALSE;
		
	/**
	 * Folder path which points to objects files
	 * 
	 * @type	string
	 */
	private $objectId = '';
	/**
	 * The current loaded template's grouo id
	 * 
	 * @type	integer
	 */
	private $groupId = '';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $repositoryPath = '';
	
	/**
	 * Current template deploy object path
	 * 
	 * @type	string
	 */
	private $deployPath = '';

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
	 * Constructor Method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$objectManager = new templateManager();
		
		$this->repositoryPath = $objectManager->getRepositoryPath();
		$this->deployPath = $objectManager->getDeployPath();
		
		$this->namePrefix = $objectManager->getNamePrefix();
		$this->folderExt = $objectManager->getFolderExt();
		
		//self::defaultLocale = locale::getDefault();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$objectId
	 * 		{description}
	 * 
	 * @return	bolean
	 * 		{description}
	 */
	public function load($objectId)
	{
		$this->objectId= $objectId;
		$resultArray = $this->getTemplateInfo($objectId);
		if(empty($resultArray))
		{
			$this->objectId= '';
			return FALSE;				
		}
		else
		{
			if($resultArray['templateStatus'] == 2)
			{
				$this->isDeployed = TRUE;
				$objectFolderRoot = $this->deployPath;
			}
			else if($resultArray['templateStatus'] == 1)
			{
				$this->isDeployed = FALSE;
				$objectFolderRoot = $this->repositoryPath;
			}
			else
			{
				$this->templateId = '';
				return FALSE;			
			}
			$objectFolderName = $this->namePrefix.$this->objectId.$this->folderExt;
		}
		$this->workingFolder = $objectFolderRoot."/".$objectFolderName;
		// Load mapping xml					
		$this->parser = new DOMParser();		
		try
		{
			// Load index file
			$this->parser->load($this->workingFolder."/.template"."/index.xml", FALSE, FALSE);
		}
		catch (Exception $ex)
		{
			echo $ex;
			return FALSE;		
		}
		return TRUE;
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
			echo $ex;
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
	 * -----------------------------------------------------------------CREATE
	 * 
	 * @param	{type}	$templateName
	 * 		{description}
	 * 
	 * @param	{type}	$templateDescription
	 * 		{description}
	 * 
	 * @param	{type}	$templateType
	 * 		{description}
	 * 
	 * @param	{type}	$templateGroup
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($templateName, $templateDescription, $templateType, $templateGroup)
	{
		//$profile = user::profile(); 
		//$userId = $profile['id'];
		$accountID = account::getAccountID();
		$userId = $accountID;
	
		//Prevent from creating, when a template is already loaded
		if(!empty($this->templateId))
			return FALSE;	
	
		// Create database entry
		$dbc = new interDbConnection();	
		
		// #Insert Into templates array
		$dbq = new dbQuery(self::$query['addTemplate'], "ebuilder.template");
						
		// Set Query Attributes						
		$attr = array();
		$attr['type'] = $templateType; 
		$attr['templateGroup'] = $templateGroup; 
		$attr['status'] = $this->defaultStatusCode;
		$attr['userId'] = $userId; 
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
		$row = $dbc->fetch($defaultResult);
		$this->templateId = $row['last_id'];
		
		// Check if template db entry is created
		if(empty($this->templateId) || is_null($this->templateId))
			return FALSE;		
	
		$this->addTemplateLiteral($this->templateId, locale::getDefault(), $name, $description);			
		
		$success = TRUE;
		// Create Folders
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
		if($this->isDeployed)
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
		if($this->isDeployed)
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
	 * @param	{type}	$pageName
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function addSequencePage($pageStructureName, $pageName)
	{
		// Return FALSE and exit, in error or if template is in deploy state	
		if($this->isDeployed)
			return FALSE;			
			
		//Check setting.mxl if $pageStructureName exists		
		// Get index root
		$root = $this->parser->evaluate("//".$this->xmlRoots['sequenceRoot'])->item(0);
		
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$page = $this->parser->parser->evaluate($this->xmlNodes['sequencePage'].'[@name=\''.$pageName.'\']')->item(0);
		
		if(!is_null($page))
		{
			//exists 
			return;		
		}
		
		//Add settings xml page entry
		$pageEntry = $this->parser->create($this->xmlNodes['sequencePage']);
		$this->parser->attr($pageEntry, "name", $pageName);
		$this->parser->attr($pageEntry, "page", $pageStructureName);
		$this->parser->append($root, $pageEntry);
		
		// Save File
		$saveFlag = $this->parser->save($this->workingFolder."/.template/", "index.xml", FALSE);
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$templateId
	 * 		{description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function addTemplateLiteral($templateId, $locale, $name, $description)
	{
		// Create database entry
		$dbc = new interDbConnection();	
					
		// #Insert Into templates Literal array
		$dbq = new dbQuery(self::$query['addTemplateLiteral'], "ebuilder.template");
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $templateId;
		$attr['locale'] = $locale;
		$attr['title'] = $name;
		$attr['description'] = $description;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$groupId
	 * 		{description}
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function addGroupLiteral($groupId, $locale, $name, $description)
	{
		$dbc = new interDbConnection();
					
		// #Insert Into templates Literal array
		$dbq = new dbQuery(self::$query['addGroupLiteral'], "ebuilder.template");
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $groupId;
		$attr['locale'] = $locale;
		$attr['title'] = $name;
		$attr['description'] = $description;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
	
	/**
	 * Building the settings.xml file at template creation phase
	 * 
	 * @return	void
	 */
	private function initSettingsXml()
	{ 
		// Return FALSE and exit, in error or if template is in deploy state	
		if($this->isDeployed)
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
	 * Change the values of template in database
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function updateTemplateInfo()
	{
		//eBLD_siteTemplate
			//Template Type	
			//Template group
			$dbc = new interDbConnection();	
			
			// #Insert Into templates array
			$dbq = new dbQuery(self::$query['updateTemplateInfo'], "ebuilder.template");
							
			// Set Query Attributes						
			$attr = array();
			$attr['type'] = $templateType; 
			$attr['templateGroup'] = $templateGroup; 
			$attr['status'] = $this->defaultStatusCode; 
			
			$defaultResult = $dbc->execute_query($dbq, $attr);
				
	
			
			
		//eBLD_siteTemplateLiterals
			//Locale
			//name
			//Desc
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$templateName
	 * 		{description}
	 * 
	 * @param	{type}	$templateDescription
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function updateTemplateLiterals($templateName = '', $templateDescription = '')
	{
		// if one is empty ???????		
	
		$dbc = new interDbConnection();		
			
		$dbq = new dbQuery(self::$query['updateTemplateLiteral'], "ebuilder.template");
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $this->templateId;
		$attr['locale'] = locale::getDefault();
		$attr['title'] = $templateName;
		$attr['description'] = $templateDescription;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
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
		if($this->isDeployed)
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
		if($this->isDeployed)
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
		if($this->isDeployed)
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
	 * ----------------------------------------------------------------DEPLOY
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function deploy()
	{
		// This function / future can only be used if template is in project state	
		if($this->isDeployed)
			return FALSE;
			
		// Change db entry to deploy
		
		
		// Copy template project last version to system template folder
		$destination = $this->deployPath."/".$this->templateFolderName;
		$source = $this->repositoryPath."/".$this->templateFolderName;
		folderManager::copy($source, $destination);	
		
		//create thumbs
		// #Create Thumbs Folder
		//$success = folderManager::create($this->workingFolder."/Thumbs");
	}
	
	/**
	 * -----------------------------------------------------------------DELETE
	 * 
	 * @param	{type}	$deployCheck
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function deleteTemplate($deployCheck = TRUE)
	{
		if($deployCheck)
		{
			// This function / future can only be used if template is in project state	
			if($this->isDeployed)
				return FALSE;
		}
		
		// Delete Template db entry
		$this->deleteTemplateDbEntry();
					
		// Literal are deleting as forign key contraints			
					
		//Delete Template Folder
		$this->deleteTemplateFolder();				
	}
	
	/**
	 * Deletes the templates database entry
	 * 
	 * @return	void
	 */
	protected function deleteTemplateDbEntry()
	{
		$dbq = new dbQuery(self::$query['deleteTemplate'], "ebuilder.template");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes						
		$attr = array();
		$attr['id'] = $this->templateId;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
	}
			
	/**
	 * Deletes all template literal database entries for the loaded template if given locale is empty or the db entry for given locale elsewhere
	 * 
	 * @param	string	$locale
	 * 		Literal locale code
	 * 
	 * @return	void
	 */
	protected function deleteTemplateLiteral($locale = '')
	{
		$dbc = new interDbConnection();
		$attr = array();
		$attr['id'] = $this->templateId;	
		if(empty($locale))
		{
			$dbq = new dbQuery(self::$query['deleteAllTemplateLiteral'], "ebuilder.template");
		}
		else
		{
			$locale = locale::getDefault();
			$attr['locale'] = $locale;
			$dbq = new dbQuery(self::$query['deleteTemplateLiteral'], "ebuilder.template");
		}
		$defaultResult = $dbc->execute_query($dbq, $attr);		
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
		if($this->isDeployed)
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
		if($this->isDeployed)
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
	public function deleteSequence()
	{
		// This function / future can only be used if template is in project state	
		if($this->isDeployed)
			return FALSE;
		
		$pageArray = $this->getPageSequence();
		foreach($pageArray as $name => $pageStructure)
		{
			$status =  $this->deleteSequencePage($name);
		}
		
		return $status;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$pageName
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function deleteSequencePage($pageName = '') 
	{
		// This function / future can only be used if template is in project state	
		if($this->isDeployed)
			return FALSE;
		
		//Delete index entry			
		// Get index root
		$root = $this->parser->evaluate('//'.$this->xmlRoots['sequenceRoot'])->item(0);
		
		$status = FALSE;
		if(is_null($root))
			return FALSE;		
		
		//Check if entry exists
		//If not $layout if null, thus FALSE->item(0) => null
		$page = $this->parser->evaluate($this->xmlNodes['sequencePage'].'[@name=\''.$pageName.'\']', $root)->item(0);
		
		if(!is_null($page))
		{
			//Delete entry
			$this->parser->replace($theme, NULL);
			// Save File
			$status = $this->parser->save($this->workingFolder."/.template/", "index.xml", FALSE);	
		}						
		return $status;
	}
	
	/**
	 * -----------------------------------------------------------------GENERAL STATIC
	 * 
	 * @param	{type}	$group
	 * 		{description}
	 * 
	 * @param	{type}	$status
	 * 		{description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getTemplates($group = '', $status = 'deploy')
	{
		$templatesArray = array();
			
		if(empty($group))
		{
			//Get all tempates / or array type => templateName
			$dbq = new dbQuery(self::$query['getAllTemplates'], "ebuilder.template");
			$dbc = new interDbConnection();			
			
			// Set Query Attributes						
			$attr = array();
			$attr['status'] = self::$status[$status];
			$attr['locale'] = locale::getDefault();
			
			$defaultResult = $dbc->execute($dbq, $attr);			
			
			while ($row = $dbc->fetch($defaultResult))
				$templatesArray[$row['templateID']] = $row['templateTitle'];		
		}
		
		//Else
		//Get templates by group
		
		
		return $templatesArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$group
	 * 		{description}
	 * 
	 * @param	{type}	$status
	 * 		{description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getUserTemplates($group = '', $status = 'deploy')
	{
		//$profile = user::profile(); 
		//$userId = $profile['id'];
		$accountID = account::getAccountID();
		$userId = $accountID;
	
		$templatesArray = array();
			
		if(empty($group))
		{
			//Get all tempates / or array type => templateName
			$dbq = new dbQuery(self::$query['getUserTemplates'], "ebuilder.template");
			$dbc = new interDbConnection();			
			
			// Set Query Attributes						
			$attr = array();
			$attr['locale'] = locale::getDefault();
			$attr['status'] = self::$status[$status];			
			$attr['userId'] = $userId;
			
			$defaultResult = $dbc->execute($dbq, $attr);			
			
			while ($row = $dbc->fetch($defaultResult))
				$templatesArray[$row['templateID']] = $row['templateTitle'];		
		}
		
		//Else
		//Get templates by group
		
		
		return $templatesArray;	
	}
	
	/**
	 * {description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getAllTypes()
	{
		$typesArray = array();
		
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['getAllSiteTypes'], "ebuilder.website");
		$dbc = new interDbConnection();			
						
		$defaultResult = $dbc->execute_query($dbq);
		
		while ($row = $dbc->fetch($defaultResult))
			$typesArray[$row['id']] = $row['type'];
		
		return $typesArray;
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getTemplateInfo($id = '')
	{		
		if(isset($this))
		{
			$id = $this->templateId;
		}
		if(empty($id))
		{
			return;
		}
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['getTemplateInfo'], "ebuilder.template");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $id;
		$attr['locale'] = locale::getDefault();
		
		$defaultResult = $dbc->execute_query($dbq, $attr);			
		
		$row = $dbc->fetch($defaultResult);
		//$infoArray['templateID'] = $row['templateID'];
		$infoArray['templateType'] = $row['templateType'];
		$infoArray['templateTitle'] = $row['templateTitle'];
		$infoArray['templateDescription'] = $row['templateDescription'];
		$infoArray['templateStatus'] = $row['templateStatus'];
		//$infoArray['groupID'] = $row['groupID'];
		$infoArray['groupTitle'] = $row['groupTitle'];
		$infoArray['groupDescription'] = $row['groupDescription'];
			
		return $infoArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	array
	 * 		{description}
	 */
	public static function getTemplateGroupInfo($id = '')
	{		 	
		if(isset($this))
		{
			$id = $this->groupId;
		}
		if(empty($id))
		{
			return;
		}
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['getGroupInfo'], "ebuilder.template");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $id;
		$attr['locale'] = locale::getDefault();
		
		$defaultResult = $dbc->execute_query($dbq, $attr);			
		
		$row = $dbc->fetch($defaultResult);		
		//$infoArray['groupID'] = $row['groupID'];
		$infoArray['groupTitle'] = $row['groupTitle'];
		$infoArray['groupDescription'] = $row['groupDescription'];
			
		return $infoArray;
	}
	
	public static function getSimpleArea()
	{
		$parser = new DOMParser();
		$area = $parser->create('div');
		return $parser->getXML();
	}
	public static function getEditableArea()
	{
		$parser = new DOMParser();
		$area = $parser->create('div');
		$parser->data($area, "edit", array('editable' => 1));
		return $parser->getXML();
	}
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
	public function validate()
	{
		// A gloabal area cannot contain another global area
		
		// An editable area cannot have childrens
	}
}
//#section_end#
?>