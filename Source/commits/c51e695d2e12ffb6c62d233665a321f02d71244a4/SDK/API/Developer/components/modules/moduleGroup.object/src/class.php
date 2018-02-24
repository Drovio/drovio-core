<?php
//#section#[header]
// Namespace
namespace API\Developer\components\modules;

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
 * @namespace	\components\modules
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "filesystem::folderManager");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "components::modules::module");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Profile", "developer");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Security", "account");

use \API\Content\filesystem\folderManager;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\components\modules\module;
use \API\Model\units\sql\dbQuery;
use \API\Model\units\modules\Smodule;
use \API\Profile\developer;
use \API\Resources\DOMParser;
use \API\Security\account;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;


/**
 * Module Group Manager
 * 
 * Handles the system's module groups
 * 
 * @version	{empty}
 * @created	March 15, 2013, 13:25 (EET)
 * @revised	August 23, 2013, 14:18 (EEST)
 */
class moduleGroup
{
	/**
	 * The group's id
	 * 
	 * @type	string
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
	 * @type	integer
	 */
	protected $parent_id;
	/**
	 * The group's parent description
	 * 
	 * @type	string
	 */
	protected $parent_description;
	/**
	 * The database connection object
	 * 
	 * @type	interDbConnection
	 */
	protected $dbc;
	
	private static $groupPaths = array();
	
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
		$result = $this->dbc->execute($dbq, $attr);

		// Fetch
		$group = $this->dbc->fetch($result);
		
		// Initialize
		$this->description = $group["group_description"];
		$this->parent_id = $group["parent_id"];
		$this->parent_description = $group["parent_description"];
		
		$this->directory = module::EXPORT_PATH.$this->getTrail();	
	}
	
	/**
	 * Returns the full path of the group
	 * 
	 * @param	string	$delimiter
	 * 		The path separator. By default, it's the directory separator.
	 * 
	 * @return	void
	 */
	public function getTrail($groupID, $delimiter = "/")
	{
		if (empty(self::$groupPaths[$groupID."_".$delimiter]))
		{
			// Get Module Group's hierarchy Path
			$dbc = new interDbConnection();
			$dbq = new dbQuery("158198994", "units.groups");
		
			$attr = array();
			$attr['id'] = $groupID;
			$result = $dbc->execute($dbq, $attr);
			
			$gpath = $delimiter;
			while ($row = $dbc->fetch($result))
				$gpath .= self::getDirectoryName($row['id']).$delimiter;
			
			// Set variable
			self::$groupPaths[$groupID."_".$delimiter] = $gpath;
		}
		
		return self::$groupPaths[$groupID."_".$delimiter];
	}
	
	/**
	 * Returns all auxiliary info of child modules of the given group. 
	 * Return value is an array: 
	 * - key: module id
	 * - value: array with auxiliary titles
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getAuxiliary()
	{	
		if (!isset($this->directory))
			$this->directory = paths::getDevPath()."/Repositories/Library/Units/Modules".$this->getTrail($this->id);
			
		// get dir.xml
		$dom_parser = new DOMParser();
		
		$dbq = new dbQuery("666615842", "units.modules");
		$attr = array();
		$attr['gid'] = $this->id;
		$result = $this->dbc->execute($dbq, $attr);
		
		$groupAuxs = array();

		while ($row = $this->dbc->fetch($result))
		{
			$module = new module();
			$module->initialize($row["id"]);
			$moduleDirectory = Smodule::directoryName($row["id"]);
			
			$dirXML = $this->directory.$moduleDirectory."/_vcs/trunk/master/index.xml";
			$dom_parser->load($dirXML, $rootRelative = true);

			$moduleList = $dom_parser->evaluate("//trunk");
			
			// for each module
			foreach ($moduleList as $module)
			{	
				// get module id
				//$mid = $module->getAttribute("id");
				
				$auxList = $dom_parser->evaluate("item[@seed]", $module);
				$moduleAuxs = array();
				// for each aux
				foreach ($auxList as $aux)
				{
					// get aux title
					$auxTitle = $aux->getAttribute("title");
					$moduleAuxs[] = $auxTitle;
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
	 * 		The paren's group id
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($description, $parent_id = NULL)
	{
		// check if user has master access to parent group
		$result = developer::masterGroup($parent_id);
		
		if ($result === FALSE)
			return $result;
		
		// create new group
		$dbq = new dbQuery("1238026976", "units.groups");
		
		$attr = array();
		$attr['parent_id'] = (is_null($parent_id) ? 'NULL' : $parent_id);
		$attr['description'] = $description;

		$result = $this->dbc->execute($dbq, $attr);

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
		$result = $this->dbc->execute($dbq, $attr);
		
		if ($result === FALSE)
			return $result;

		// Create directory
		if (!isset($this->directory))
			$this->directory = module::EXPORT_PATH.$this->getTrail();

		folderManager::create(systemRoot.$this->directory);
		
		return $result;
	}
	
	/**
	 * Deletes an existing module group
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function delete()
	{
		// check if user has development access to group
		$result = developer::masterGroup($this->id);
		
		if ($result === FALSE)
			return $result;
		
		$dbq = new dbQuery("1513206737", "security.privileges.developer");
		
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$attr['gid'] = $this->id;
		
		$result = $this->dbc->execute($dbq, $attr);
		
		if ($result === FALSE)
			return $result;
		
		// remove group from DB
		if (!isset($this->directory))
			$this->directory = module::EXPORT_PATH.$this->getTrail();
		
		$dbq = new dbQuery("389193473", "units.groups");
		
		$attr = array();
		$attr['id'] = $this->id;
		
		$result = $this->dbc->execute($dbq, $attr);

		// Delete group directory
		if (!$result)
			return FALSE;
		
		// also need to remove group from repository!!!
		return folderManager::remove(systemRoot.$this->directory);
	}
	
	/**
	 * Gets the module group's directory name
	 * 
	 * @param	integer	$id
	 * 		The group's id
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getDirectoryName($id = "")
	{
		$id = ($id == "" ? $this->id : $id);
		return "_grp_".$id;
	}
}
//#section_end#
?>