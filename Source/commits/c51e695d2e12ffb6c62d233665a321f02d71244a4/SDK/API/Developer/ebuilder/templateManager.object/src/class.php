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

importer::import("API", "Profile", "ServiceManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Developer", "ebuilder::templateGroup");

use \API\Profile\ServiceManager;
use \API\Resources\filesystem\folderManager;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\ebuilder\templateGroup;

// FOR DEVELOPING
importer::import("API", "Developer", "profiler::tester");
use API\Developer\profiler\tester;
// FOR DEVELOPING	
/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	July 22, 2013, 10:39 (EEST)
 * @revised	July 22, 2013, 11:07 (EEST)
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
	private static $query = array(
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
		//$this->projectPath = systemRoot."/".$devRootFolder.self::PATH_PROJECT;
		//$this->deployPath = systemRoot."/".self::PATH_DEPLOY;
		
		// FOR DEVELPMENT
		$tester = new tester();
		$trunk = $tester->getTrunk();
		
		$this->projectPath = systemRoot."/".$trunk."/".self::PATH_PROJECT;
		$this->deployPath = systemRoot."/".$trunk."/".self::PATH_DEPLOY;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getRepositoryPath()
	{
		return $this->projectPath;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getDeployPath()
	{
		return $this->deployPath;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getNamePrefix()
	{
		return self::NAME_PREFIX;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function getFolderExt()
	{
		return self::FOLDER_EXT;
	}
	
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
}
//#section_end#
?>