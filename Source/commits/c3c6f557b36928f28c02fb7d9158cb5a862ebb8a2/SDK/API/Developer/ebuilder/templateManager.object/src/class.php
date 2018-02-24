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
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Developer", "ebuilder::templateGroup");
importer::import("API", "Developer", "ebuilder::template");
importer::import("API", "Security", "account");
importer::import("API", "Developer", "projects::project");

use \API\Profile\ServiceManager;
use \API\Resources\filesystem\folderManager;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\ebuilder\templateGroup;
use \API\Developer\ebuilder\template;
use \API\Developer\projects\project;

use \API\Security\account;
// FOR DEVELOPING
importer::import("API", "Developer", "profiler::tester");
use API\Developer\profiler\tester;
// FOR DEVELOPING	
/**
 * Template Manager
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	July 22, 2013, 10:39 (EEST)
 * @revised	December 23, 2013, 21:46 (EET)
 */
class templateManager
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const NAME_PREFIX = 't';
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const FOLDER_EXT = '.template';

	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PATH_PROJECT = "Templates";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PATH_DEPLOY = "Library/ebuilder/Templates";
		
	const DEPLOYD = 'deployed';

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
	 * @type	{empty}
	 */
	private static $status = array(
			'deploy' => '2',
			'project' => '1',
			'revise' => '3');

	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private static $query = array(
			'getAllTemplates' => '1008570389',
			'getUserTemplates' => '1947306124',
			'getTemplateInfo' => '1053660758',
			'getGroupInfo' => '1034585811',
			'getAllSiteTypes' => '981346525',
			'addTemplate' => '1310198126',
			'deleteTemplate' => '1875721238',
			'deleteAllTemplateLiteral' => '1168316185',
			'updateTemplateInfo' => '270118125',
			'getTemplate' => '512406131');
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$pServices = new ServiceManager();
		$devRootFolder = $pServices->getAccountFolder("Developer");
		//$this->repositoryPath = systemRoot."/".$devRootFolder.self::PATH_PROJECT;
		//$this->deployPath = systemRoot."/".self::PATH_DEPLOY;
		
		
		// -- FOR DEVELPMENT
		$trunk = tester::getTrunk();
				
		$this->repositoryPath = systemRoot."/".$trunk."/".self::PATH_PROJECT;
		$this->deployPath = systemRoot."/".$trunk."/".self::PATH_DEPLOY;
		// -- END
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getGroupManager()
	{
		return new templateGroup();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function getTemplateInfo($id = '')
	{		
		if(empty($id))
		{
			return array();
		}
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['getTemplate'], "ebuilder.template");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $id;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);			
		
		$row = $dbc->fetch($defaultResult);
		$infoArray['templateID'] = $row['templateID'];
		$infoArray['templateStatus'] = $row['templateStatus'];
		$infoArray['templateGroup'] = $row['templateGroup'];
		$infoArray['templateType'] = $row['templateType'];
			
		return $infoArray;
		
		/* NEw Code */
		/*
		
		return project::info($id); 
		
		*/
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
		$source = systemRoot.$this->deployPath."/".self::NAME_PREFIX.$id.self::FOLDER_EXT;	
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
		$resultArray = $this->getTemplateInfo($id);
		if(empty($resultArray))
		{
			return FALSE;				
		}
		else
		{
			if($resultArray['templateStatus'] == 2)
			{
				$readOnly = TRUE;
				$objectFolderRoot = $this->deployPath;
			}
			else if($resultArray['templateStatus'] == 1)
			{
				$readOnly = FALSE;
				$objectFolderRoot = $this->repositoryPath;
			}
			else
			{
				$this->templateId = '';
				return NULL;			
			}
			$objectFolderName = self::NAME_PREFIX.$id.self::FOLDER_EXT;
		}
		$workingFolder = $objectFolderRoot."/".$objectFolderName;
		
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
		$template->init();
		
		return $template;
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
	 * @param	{type}	$templateType
	 * 		{description}
	 * 
	 * @param	{type}	$templateGroup
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function createTemplate($title, $category, $description = "")
	{
		$id = project::create($title, $category, $description = ""); 
		
		//if()
		//	return;
		
		// Create Folders
		$template->initializeRepository();	
		$template = $this->loadTemplate($templateId);
		
		return TRUE;

	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function updateTemplateInfo($id, $title, $description = "")
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
		 		
	
		/* NEw Code */
		/*
		
		return project::updateInfo($id, $title, $description = "");
		
		*/
	}
	
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function deploy($id)
	{
		// Create Folders
		$template->initializeRepository();	
		$template = $this->loadTemplate($templateId);
		/*
		if($template->status == self::DEPLOYD)
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
	 * @param	{type}	$group
	 * 		{description}
	 * 
	 * @param	{type}	$status
	 * 		{description}
	 * 
	 * @return	void
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
	 * @return	void
	 */
	public static function getUserTemplates($group = '', $status = 'deploy')
	{
		$accountID = account::getAccountID();
	
		$templatesArray = array();			
		if(empty($group))
		{
			//Get all tempates / or array type => templateName
			$dbq = new dbQuery(self::$query['getUserTemplates'], "ebuilder.template");
			$dbc = new interDbConnection();			
			
			// Set Query Attributes						
			$attr = array();
			$attr['status'] = self::$status[$status];			
			$attr['accId'] = $accountID;
			
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
	 * @return	void
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
}
//#section_end#
?>