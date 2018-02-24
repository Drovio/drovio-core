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

importer::import("API", "Profile", "ServiceManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "ebuilder::template");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "projects::projectStatus");

use \API\Profile\ServiceManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\ebuilder\template;
use \API\Developer\projects\project;
use \API\Developer\projects\projectStatus;


// FOR DEVELOPING
importer::import("API", "Developer", "profiler::tester");
use API\Developer\profiler\tester;
// FOR DEVELOPING	
/**
 * Template Manager
 * 
 * A Manager class for template type developer's project
 * 
 * @version	{empty}
 * @created	July 22, 2013, 10:39 (EEST)
 * @revised	December 30, 2013, 18:14 (EET)
 */
class templateManager
{
	const DEPLOYED = 'deployed';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PROJECT_TYPE = 6;

	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $repositoryPath = '';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $deployPath = '';
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$pServices = new ServiceManager();
		$devRootFolder = $pServices->getAccountFolder("Developer");
		//$this->deployPath = systemRoot."/".self::PATH_DEPLOY;
		
		
		// -- FOR DEVELOPMENT
		$PATH_PROJECT = "Templates";
		$PATH_DEPLOY = "Library/ebuilder/Templates";
 
		$trunk = tester::getTrunk();
				
		$this->repositoryPath = systemRoot."/".$trunk."/".$PATH_PROJECT;
		$this->deployPath = systemRoot."/".$trunk."/".$PATH_DEPLOY;
		// -- END
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getTemplateInfo($id = '')
	{		
		if(empty($id))
		{
			return array();
		}
		
		//$info['projectCategory'], $info['projectType'], $info['projectStatus']
		
		return project::info($id); 
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$destination
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insertToWebsite($id, $destination)
	{
		
		$extInfo = $this->getTemplateInfo($id);
		
		//  Check deploy state
		if(!$extInfo['templateStatus'] == 2)
			return FALSE;		
		
		// Copy from library/ebuilder to destination
		$NAME_PREFIX = 't';
		$FOLDER_EXT = '.template';
		
		$source = systemRoot.$this->deployPath."/".$NAME_PREFIX.$id.$FOLDER_EXT;	
		folderManager::copy($source, $destination);		
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function loadTemplate($id)
	{
		$NAME_PREFIX = 't';
		$FOLDER_EXT = '.template';
		$resultArray = $this->getTemplateInfo($id);
		if(empty($resultArray))
		{
			return NULL;				
		}
		else
		{
			if($resultArray['projectStatus'] == 2)
			{
				$readOnly = TRUE;
				$objectFolderRoot = $this->deployPath;
			}
			else if($resultArray['projectStatus'] == 1)
			{
				$readOnly = FALSE;
				$objectFolderRoot = $this->repositoryPath;
			}
			else
			{
				$this->templateId = '';
				return NULL;			
			}
			$objectFolderName = $NAME_PREFIX.$id.$FOLDER_EXT;
		}
		$workingFolder = $objectFolderRoot."/".$objectFolderName;
		
		//$workingFolder = project::getRepository($id);		
		$template = new template($workingFolder, $readOnly);
		
		return $template;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function getTemplate($id)
	{		
		$template = $this->loadTemplate($id);		
		if(!is_null($template))
			$template->init();
		
		return $template;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$category
	 * 		{description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function createTemplate($title, $category = "" , $description = "")
	{
		$id = project::create($title,  self::PROJECT_TYPE, $description);  
		
		if($id == FALSE)
			return FALSE;
		
		//project::setName($id, $name);
		
		//project::setCategory($id, $category);
		
		// Instatiate newly created template
		$template = $this->loadTemplate($id);
		if(is_null($template))
			return FALSE;
		
		// Create Folders
		$template->initializeRepository();
		
		return TRUE;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @param	{type}	$title
	 * 		{description}
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateTemplateInfo($id, $title, $description = "")
	{
		return project::updateInfo($id, $title, $description = "");
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function deploy($id)
	{			
		$template = $this->getTemplate($id);
		
		if(is_null($template))
			return FALSE;
		/*
		if($template->status == self::DEPLOYED)
		{
			// Upgrade
			$template->increaseVersion();
		}
		*/
		
		// Deploy
		$deployPath = $this->deployPath;
		$status = $template->deploy($deployPath);
	}
	
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$deployCheck
	 * 		{description}
	 * 
	 * @return	void
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
	 * {description}
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
	 * {description}
	 * 
	 * @param	{type}	$category
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getTemplates($category = '')
	{
		$templatesArray = array();
		
		return $templatesArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$category
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getUserTemplates($category = '')
	{
		$templatesArray = array();
		
		return $templatesArray;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$category
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getDeveloperTemplates($category = '')
	{
		$templatesArray = array();
		
		$templatesArray = project::getMyProjects(FALSE);

		return $templatesArray;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public static function getAllTypes()
	{
		$typesArray = array();
		
		
		return $typesArray;
	}
}
//#section_end#
?>