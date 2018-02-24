<?php
//#section#[header]
// Namespace
namespace API\Developer\components\modules;

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
 * @namespace	\components\modules
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "components::modules::AbstractModule");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "developer");

use \API\Resources\filesystem\folderManager;
use \API\Developer\components\modules\AbstractModule;
use \API\Model\units\modules\Smodule;
use \API\Model\units\sql\dbQuery;
use \API\Profile\developer;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Module Manager
 * 
 * The system's module (not auxiliary) handler.
 * 
 * @version	{empty}
 * @created	March 15, 2013, 12:49 (EET)
 * @revised	January 15, 2014, 10:11 (EET)
 * 
 * @deprecated	Use units\modules\module instead.
 */
class module extends AbstractModule
{
	/**
	 * Initialize an existing module
	 * 
	 * @param	string	$id
	 * 		The module's id
	 * 
	 * @return	module
	 * 		{description}
	 */
	public function initialize($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * Initialize Info and VCS.
	 * 
	 * @return	void
	 */
	protected function initializeInfo()
	{
		// Initialize Parent Info
		parent::initializeInfo();
		
		// Initialize VCS
		$this->VCS_initialize(paths::getDevPath().parent::REPOSITORY, parent::REPOSITORY_PATH."/".$this->repositoryDir, $this->name, parent::FILE_TYPE);
		$this->initialized = TRUE;
	}
	
	/**
	 * Creates a new module
	 * 
	 * @param	integer	$parentID
	 * 		The module group's id.
	 * 
	 * @param	string	$title
	 * 		The module's title.
	 * 
	 * @param	string	$description
	 * 		The module's description
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($parentID, $title, $description = "")
	{
		if (!developer::moduleGroupInWorkspace($parentID))
			return FALSE;

		// Register to Database
		$dbq = new dbQuery("499426318", "units.modules");
		
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['group_id'] = $parentID;
		$result = $this->dbc->execute_query($dbq, $attr);

		if (!$result)
			return FALSE;

		$module = $this->dbc->fetch($result);
		$this->initialize($module['last_id']);
		$this->loadDatabaseInfo();
		
		// Initialize Information
		$this->initializeInfo();
			
		// Create vcs repository structure
		$this->VCS_createStructure();
		
		// Create Object
		$this->VCS_createObject();
		
		// Update Content
		$this->update($title, $description);
		
		// Commit
		$this->commit("Initial Master Commit");
		
		return TRUE;
	}
	
	/**
	 * Deletes an existing module.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function delete()
	{
		if (!developer::moduleInWorkspace($this->id))
			return FALSE;

		// Delete Module from Database
		$dbq = new dbQuery("1968995495", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		$result = $this->dbc->execute_query($dbq, $attr);

		if (!$result)
			return FALSE;
			
		// Remove module directory
		$removed = folderManager::remove_full(systemRoot.$this->exportDir);

		// Remove Repository
		$this->VCS_removeRepository(paths::getDevPath().parent::REPOSITORY, "/Library/Modules/".$this->repositoryDir);
		
		return TRUE;
	}
	
	/**
	 * Updates the information of a module.
	 * 
	 * @param	string	$title
	 * 		The new title.
	 * 
	 * @param	string	$description
	 * 		The new description
	 * 
	 * @param	string	$code
	 * 		The source code of the module.
	 * 
	 * @param	array	$imports
	 * 		An array of package imports.
	 * 
	 * @param	array	$inner
	 * 		An array of inner modules.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function update($title, $description, $code = "", $imports = array(), $inner = array())
	{
		if (!developer::moduleInWorkspace($this->id))
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
		$this->updateIndexInfo($title, $description, $imports, $inner);
		return $this->updateSourceCode($code);
	}
	
	/**
	 * Gets the hashed filename of the module.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getFileName()
	{
		return Smodule::fileName($this->id);
	}
}
//#section_end#
?>