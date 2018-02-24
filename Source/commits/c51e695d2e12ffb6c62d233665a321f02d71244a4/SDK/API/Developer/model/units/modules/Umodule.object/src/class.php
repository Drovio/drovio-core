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
importer::import("API", "Content", "resources");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Security", "privileges::modules::manage::developer");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "model::units::module");
importer::import("API", "Developer", "model::version::repository");
importer::import("API", "Developer", "model::units::modules::Ugroup");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\folderManager;
use \API\Content\resources;
use \API\Geoloc\locale;
use \API\Model\units\modules\Smodule;
use \API\Security\privileges\modules\manage\developer;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\model\units\module;
use \API\Developer\model\version\repository;
use \API\Developer\model\units\modules\Ugroup;

/**
 * Module Manager
 * 
 * The system's module (not auxiliary) handler.
 * 
 * @version	{empty}
 * @created	March 16, 2013, 10:51 (EET)
 * @revised	March 20, 2013, 10:27 (EET)
 * 
 * @deprecated	Use \API\Developer\components\modules\ instead.
 */
class Umodule extends module
{
	/**
	 * Initialize an existing module
	 * 
	 * @param	integer	$id
	 * 		The module's id
	 * 
	 * @return	void
	 */
	public function initialize($id)
	{
		$this->id = $id;
		$this->inner = array();
		$this->name = $this->_get_fileName();
		
		// Load Info
		$this->load();
	}
	
	/**
	 * Update the module's content
	 * 
	 * @param	string	$title
	 * 		The module's title
	 * 
	 * @param	string	$description
	 * 		The module's description
	 * 
	 * @param	string	$code
	 * 		The module's source code
	 * 
	 * @param	string	$branch
	 * 		The vcs branch where the update will be performed
	 * 
	 * @return	boolean
	 */
	public function update($title, $description, $code = "", $branch = "")
	{
		if (!developer::get_moduleStatus($this->id))
			return FALSE;
		
		// Update Database
		$dbq = new dbQuery("918340463", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$result = $this->dbc->execute_query($dbq, $attr);

		if (!$result)
			return FALSE;
		
		// Update Code files
		return parent::update($title, $description, $code, $branch);
	}
	
	/**
	 * Create a new module
	 * 
	 * @param	integer	$parent_id
	 * 		The module group's id
	 * 
	 * @param	string	$title
	 * 		The module's title
	 * 
	 * @param	string	$description
	 * 		The module's description
	 * 
	 * @return	boolean
	 */
	public function create($parent_id, $title, $description)
	{
		if (!developer::get_groupStatus($parent_id))
			return FALSE;
			
		// Register to Database
		$dbq = new dbQuery("499426318", "units.modules");
		
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['group_id'] = $parent_id;
		$result = $this->dbc->execute_query($dbq, $attr);
		
		if (!$result)
			return FALSE;
			
		$module = $this->dbc->fetch($result);
		$this->initialize($module['last_id']);
		$this->load_databaseInfo();
		
		// Initialize Information
		$this->initialize_info();
			
		// Create vcs repository structure
		$this->VCS_create_structure();
		
		// Create Object
		$this->VCS_create_object();
		
		// Update Content
		$this->update($title, $description);
		
		// Commit
		$this->commit("Initial Master Commit");
		
		return TRUE;
	}
	
	/**
	 * Delete a module
	 * 
	 * @return	boolean
	 */
	public function delete()
	{
		if (!developer::get_moduleStatus($this->id))
			return FALSE;

		// Delete Module from Database
		$dbq = new dbQuery("1968995495", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		$result = $this->dbc->execute_query($dbq, $attr);

		if (!$result)
			return FALSE;
			
		// Remove module directory
		$removed = folderManager::remove_full(systemRoot.$this->directory);

		// Remove Repository
		repository::remove("/Library/Modules/".$this->vcsDirectory);
		
		// Remove Locale Difectories for Module
		$dbq = new dbQuery("1827904056", "geo");
		
		$attr = array();
		$available_locale = $this->dbc->execute_query($dbq, $attr);

		while ($row = $this->dbc->fetch($available_locale))
		{
			$path = resources::STATIC_PATH."/Literals/".$row['locale']."/"."modules/m.".$this->id."/";
			echo "literals ".systemRoot.$path."\n";
			if (file_exists(systemRoot.$path))
				folderManager::remove_full(systemRoot.$path);
		}
		
		return TRUE;
	}
	
	/**
	 * Update the module index information
	 * 
	 * @param	string	$branch
	 * 		The vcs branch
	 * 
	 * @return	void
	 */
	protected function update_indexInfo($branch = "")
	{
		$builder = new DOMParser();
		$newBase = $this->get_indexInfo($builder);
		
		// Update index file
		$this->vcsTrunk->update_indexBase($builder, $newBase, $branch);
	}
		
	/**
	 * Get the filename of the module
	 * 
	 * @return	string
	 */
	protected function _get_fileName()
	{
		return Smodule::fileName($this->id);
	}
}
//#section_end#
?>