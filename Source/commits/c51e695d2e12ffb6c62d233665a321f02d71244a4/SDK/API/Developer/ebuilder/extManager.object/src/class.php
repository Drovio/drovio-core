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
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Security", "account");

use \API\Profile\ServiceManager;
use \API\Resources\filesystem\folderManager;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \API\Resources\geoloc\locale;
use \API\Security\account;

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
 * @created	July 17, 2013, 17:23 (EEST)
 * @revised	July 17, 2013, 17:23 (EEST)
 */
class extManager
{
	const FOLDER_EXT = '.extension';
	const NAME_PREFIX = 'ext';	

	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PATH_PROJECT = "Extensions";
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const PATH_DEPLOY = "Library/ebuilder/Extensions";

	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $projectPath = '';	
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
			'getExtensionInfo' => '1621207856',
			'getUserExtensions' => '345034401',
			'getAllCategories' => '499199859');

	private static $status = array(
			'deploy' => '2',
			'project' => '1',
			'revise' => '3');
			
	

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
		
		$this->projectPath = $trunk."/".self::PATH_PROJECT;
		$this->deployPath = $trunk."/".self::PATH_DEPLOY;
	}
	
	public function getRepositoryPath()
	{
		return $this->projectPath;
	}
	
	public function getDeployPath()
	{
		return $this->deployPath;
	}
	
	public function getNamePrefix()
	{
		return self::NAME_PREFIX;
	}
	
	public function getFolderExt()
	{
		return self::FOLDER_EXT;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	private static function getExtensionInfo($id = '')
	{		
		if(empty($id))
		{
			return array();
		}
		//Get all tempates / or array type => templateName
		$dbq = new dbQuery(self::$query['getExtension'], "ebuilder.extension");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes
		$attr = array();
		$attr['id'] = $id;
		
		$defaultResult = $dbc->execute_query($dbq, $attr);			
		
		$row = $dbc->fetch($defaultResult);
		$infoArray['extensionID'] = $row['extensionID'];
		$infoArray['extensionStatus'] = $row['extensionStatus'];
			
		return $infoArray;
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function extensionContainer($id)
	{
		$extensionContainer = DOM::create("div", "", "ext_".$id, "extensionContainer");
		
		return $extensionContainer;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$destination
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function insertToWebsite($id, $destination)
	{
		
		$extInfo = $this->getExtensionInfo($id);
		
		//  Check deploy state
		if(!$extInfo['extensionStatus'] == 2)
			return FALSE;		
		
		// Copy from library/ebuilder to destination
		$source = systemRoot.$this->deployPath."/".self::NAME_PREFIX.$id.self::FOLDER_EXT;	
		folderManager::copy($source, $destination);		
	}
		
	public static function getUserExtensions($group = '', $status = 'deploy')
	{
		$accountID = account::getAccountID();
	
		$extensionsArray = array();
			
		if(empty($group))
		{
			//Get all tempates / or array type => templateName
			$dbq = new dbQuery(self::$query['getUserExtensions'], "ebuilder.extension");
			$dbc = new interDbConnection();			
			
			// Set Query Attributes						
			$attr = array();
			$attr['locale'] = locale::getDefault();
			$attr['status'] = self::$status[$status];			
			$attr['userId'] = $accountID;
			
			$defaultResult = $dbc->execute_query($dbq, $attr);	
			
			while ($row = $dbc->fetch($defaultResult))
				$extensionsArray[$row['extensionID']] = $row['extensionTitle'];	
		}
		
		//Else
		//Get templates by group
		
		
		return $extensionsArray;	
	}
	
	
	public static function getAllCategories()
	{
		$categoriessArray = array();
		
		//Get all tempates / or array categoryID => categoryTitle
		$dbq = new dbQuery(self::$query['getAllCategories'], "ebuilder.extension");
		$dbc = new interDbConnection();			
		
		// Set Query Attributes						
		$attr = array();
		$attr['locale'] = locale::getDefault();
		
		$defaultResult = $dbc->execute_query($dbq, $attr);
		
		while ($row = $dbc->fetch($defaultResult))
			$categoriessArray[$row['categoryID']] = $row['categoryTitle'];		
		
		return $categoriessArray;
	}
}
//#section_end#
?>