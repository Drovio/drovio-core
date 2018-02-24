<?php
//#section#[header]
// Namespace
namespace API\Developer\model\units\modules;

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
 * @namespace	\model\units\modules
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOMParser");
importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Security", "privileges::modules::manage::developer");
importer::import("API", "Security", "account");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "model::version::repository");
importer::import("API", "Developer", "model::units::modules::Umodule");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\folderManager;
use \API\Model\units\sql\dbQuery;
use \API\Model\units\modules\Smodule;
use \API\Security\privileges\modules\manage\developer;
use \API\Security\account;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\model\version\repository;
use \API\Developer\model\units\modules\Umodule;

/**
 * Module Group Manager
 * 
 * Handles the system's module groups
 * 
 * @version	{empty}
 * @created	March 20, 2013, 10:27 (EET)
 * @revised	March 20, 2013, 10:27 (EET)
 * 
 * @deprecated	Use \API\Developer\components\modules\ instead.
 */
class Ugroup
{
	/**
	 * The group's id
	 * 
	 * @type	integer
	 */
	protected $id;
	/**
	 * The group's description
	 * 
	 * @type	string
	 */
	protected $description;
	/**
	 * The group's parent id
	 * 
	 * @type	string
	 */
	protected $parent_id;
	/**
	 * The group's parent description
	 * 
	 * @type	string
	 */
	protected $parent_description;
	/**
	 * A database connection
	 * 
	 * @type	interDbConnection
	 */
	protected $dbc;
	
	/**
	 * Constructor method
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->dbc = new interDbConnection();
	}
	
	/**
	 * Initializes the object
	 * 
	 * @param	integer	$id
	 * 		The group's id
	 * 
	 * @return	void
	 */
	public function initialize($id)
	{
		$this->id = $id;
	}
	
	/**
	 * Loads the object's information
	 * 
	 * @return	void
	 */
	public function load()
	{
		// Get Module Group's hierarchy Path
		$dbq = new dbQuery("142968574", "units.groups");
		
		// Get Module Group Data
		$attr = array();
		$attr['id'] = $this->id;
		$result = $this->dbc->execute_query($dbq, $attr);

		// Fetch
		$group = $this->dbc->fetch($result);
		
		// Initialize
		$this->description = $group["group_description"];
		$this->parent_id = $group["parent_id"];
		$this->parent_description = $group["parent_description"];
		
		$this->directory = Umodule::PATH.$this->get_trail();
	}
	
	/**
	 * Returns the full path of the group
	 * 
	 * @param	string	$delimiter
	 * 		The path separator. By default, it's the directory separator.
	 * 
	 * @return	string
	 */
	public function get_trail($delimiter = "/")
	{
		// Get Module Group's hierarchy Path
		$dbq = new dbQuery("158198994", "units.groups");

		$attr = array();
		$attr['id'] = $this->id;
		$result = $this->dbc->execute_query($dbq, $attr);
		
		$gpath = $delimiter;
		while ($row = $this->dbc->fetch($result))
			$gpath .= $this->get_directoryName($row['id']).$delimiter;

		return $gpath;
	}
	
	/**
	 * Returns all auxiliary info of child modules of the given group. 
	 * Return value is an array: 
	 * - key: module id
	 * - value: array with auxiliary titles
	 * 
	 * @return	array
	 */
	public function get_aux()
	{	
		if (!isset($this->directory))
			$this->directory = repository::PATH."/Library/Units/Modules".$this->get_trail();
			
		// get dir.xml
		$dom_parser = new DOMParser();
		
		$dbq = new dbQuery("666615842", "units.modules");
		$attr = array();
		$attr['gid'] = $this->id;
		$result = $this->dbc->execute_query($dbq, $attr);
		
		$groupAuxs = array();

		while ($row = $this->dbc->fetch($result))
		{
			$module = new Umodule();
			$module->initialize($row["id"]);
			$moduleDirectory = Smodule::directoryName($row["id"]);
			
			$dirXML = $this->directory.$moduleDirectory."/_vcs/trunk/master/index.xml";
			$dom_parser->load($dirXML, $rootRelative = true);
			
			// Get Modules
			$moduleList = $dom_parser->evaluate("//trunk");
			foreach ($moduleList as $module)
			{
				// Get Auxiliaries
				$auxList = $dom_parser->evaluate("item[@seed]", $module);
				$moduleAuxs = array();
				foreach ($auxList as $aux)
				{
					$auxArr = array();
					$auxArr['seed'] = $aux->getAttribute("seed");
					$auxArr['title'] = $aux->getAttribute("title");
					$moduleAuxs[$auxArr['seed']] = $auxArr;
				}
				
				if (count($moduleAuxs) > 0)
					$groupAuxs["'".$row['id']."'"] = $moduleAuxs;
			}
		}
		
		return $groupAuxs;
	}
		
	/**
	 * Creates a new module group
	 * 
	 * @param	string	$description
	 * 		The group's description
	 * 
	 * @param	integer	$parent_id
	 * 		The parent's group id
	 * 
	 * @return	boolean
	 */
	public function create($description, $parent_id = NULL)
	{
		// check if user has master access to parent group
		$result = developer::get_masterGroupStatus($parent_id);
		
		if ($result === FALSE)
			return $result;
		
		// create new group
		$dbq = new dbQuery("1238026976", "units.groups");
		
		$attr = array();
		$attr['parent_id'] = (is_null($parent_id) ? 'NULL' : $parent_id);
		$attr['description'] = $description;

		$result = $this->dbc->execute_query($dbq, $attr);

		if ($result === FALSE)
			return $result;
		
		// Get group id
		$row = $this->dbc->fetch($result);
		$this->initialize($row['last_id']);
		$this->load();
		
		// grant development acces to user for new group
		$dbq = new dbQuery("184651428", "security.privileges.developer");
	
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$attr['gid'] = $this->id;

		$result = $this->dbc->execute_query($dbq, $attr);
		
		if ($result === FALSE)
			return $result;

		// Create directory
		if (!isset($this->directory))
			$this->directory = Umodule::PATH.$this->get_trail();

		folderManager::create(systemRoot.$this->directory);
		
		return $result;
	}
	
	/**
	 * Deletes an existing module group
	 * 
	 * @return	boolean
	 */
	public function delete()
	{
		// check if user has development access to group
		$result = developer::get_groupStatus($this->id);
		
		if ($result === FALSE)
			return $result;
		
		$dbq = new dbQuery("1513206737", "security.privileges.developer");
		
		$attr = array();
		$profile = developer::profile();
		$attr['uid'] = $profile['id'];
		$attr['gid'] = $this->id;
		
		$result = $this->dbc->execute_query($dbq, $attr);
		
		if ($result === FALSE)
			return $result;
		
		// remove group from DB
		if (!isset($this->directory))
			$this->directory = Umodule::PATH.$this->get_trail();
		
		$dbq = new dbQuery("389193473", "units.groups");
		
		$attr = array();
		$attr['id'] = $this->id;
		
		$result = $this->dbc->execute_query($dbq, $attr);

		// Delete group directory
		if (!$result)
			return FALSE;
		
		// also need to remove group from repository!!!
		return folderManager::remove(systemRoot.$this->directory);
	}
	
	/**
	 * Returns the directory name of the group
	 * 
	 * @param	integer	$id
	 * 		The group's id
	 * 
	 * @return	string
	 */
	protected function get_directoryName($id = "")
	{
		$id = ($id == "" ? $this->id : $id);
		return "_grp_".$id;
	}
}
//#section_end#
?>