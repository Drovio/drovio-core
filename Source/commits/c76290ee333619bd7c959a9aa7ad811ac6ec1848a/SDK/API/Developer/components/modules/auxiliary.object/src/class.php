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

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Developer", "components::modules::AbstractModule");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Profile", "developer");

use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Developer\components\modules\AbstractModule;
use \API\Model\units\modules\Smodule;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Profile\developer;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Module Auxiliary Manager
 * 
 * The system's auxiliary handler.
 * 
 * @version	{empty}
 * @created	March 15, 2013, 12:46 (EET)
 * @revised	January 15, 2014, 10:11 (EET)
 * 
 * @deprecated	Use units\modules\module instead.
 */
class auxiliary extends AbstractModule
{
	/**
	 * The auxiliary's seed
	 * 
	 * @type	integer
	 */
	private $seed;
	/**
	 * The object's title
	 * 
	 * @type	string
	 */
	private $auxTitle;
	
	/**
	 * Inherited method. Initializes the object's variables.
	 * 
	 * @param	integer	$id
	 * 		The module's id
	 * 
	 * @param	integer	$seed
	 * 		The auxiliary seed.
	 * 
	 * @param	string	$title
	 * 		The auxiliary's title.
	 * 
	 * @return	auxiliary
	 * 		{description}
	 */
	public function initialize($id, $seed = "", $title = "")
	{
		$this->id = $id;
		$this->setTitle($title);
		if (!empty($seed))
			$this->seed = $seed;
		return $this;
	}
	
	/**
	 * Initializes all the object's information and variables.
	 * 
	 * @return	void
	 */
	protected function initializeInfo()
	{
		// Initialize Parent Info
		parent::initializeInfo();
		
		// Initialize VCS (to get seed)
		$this->VCS_initialize(paths::getDevPath().parent::REPOSITORY, parent::REPOSITORY_PATH."/".$this->repositoryDir, $this->name, parent::FILE_TYPE);
		
		// Get Base Object
		$parser = new DOMParser();
		$title = $this->getTitle();
		if (!empty($this->seed))
			$base = $this->vcsTrunk->getBase($parser, $this->getWorkingBranch());
		else
			$base = $this->vcsTrunk->getBaseByTitle($parser, $this->getWorkingBranch(), $this->getTitle());
		
		// If base doesn't exist yet, return false
		if (is_null($base))
			return FALSE;
			
		// Set Properties
		$this->seed = ($this->seed != "" ? $this->seed : $base->getAttribute('seed'));
		$this->name = $this->getFileName();
		$this->setTitle($base->getAttribute('title'));
		$this->description = $parser->evaluate("description", $base)->item(0)->nodeValue;
		$this->initialized = TRUE;
	}
	
	/**
	 * Updates the entire object.
	 * 
	 * @param	string	$title
	 * 		The new title.
	 * 
	 * @param	string	$description
	 * 		The new description.
	 * 
	 * @param	string	$code
	 * 		The new source code.
	 * 
	 * @param	array	$imports
	 * 		The array of imports.
	 * 
	 * @param	array	$inner
	 * 		The array of inner modules used by this object.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function update($title, $description, $code = "", $imports = array(), $inner = array())
	{
		if (!developer::moduleInWorkspace($this->id))
			return FALSE;
		
		// Update Code files
		$this->updateIndexInfo($title, $description, $imports, $inner);
		return $this->updateSourceCode($code);
	}
	
	/**
	 * Create a new auxiliary module.
	 * 
	 * @param	integer	$moduleID
	 * 		The parent's (module) id.
	 * 
	 * @param	string	$title
	 * 		The auxiliary's title.
	 * 
	 * @param	string	$description
	 * 		The auxiliary's description.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function create($moduleID, $title, $description = "")
	{
		if (!developer::moduleInWorkspace($moduleID))
			return FALSE;

		// Create seed
		$this->seed = rand();
		$this->description = $description;
		
		// Initialize
		$this->initialize($moduleID, $seed, $title);
		$this->loadDatabaseInfo();

		// Initialize Information
		$this->initializeInfo();

		// Create vcs repository structure
		$this->VCS_createObject();

		// Update Content
		$this->update($title, $description);

		// Deploy
		$this->commit("Initial working file");

		return TRUE;
	}
	
	/**
	 * Deletes the auxiliary completely
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public function delete()
	{
		if (!developer::moduleInWorkspace($this->id))
			return FALSE;

		$this->loadDatabaseInfo();

		// Remove VCS Files
		$this->VCS_removeObject();
		
		// Remove Repository File
		$repositoryFilePath = systemRoot.$this->repositoryDir.$this->name.".".$this->fileType;
		fileManager::remove($repositoryFilePath);

		// _____ Remove [Head] file
		$headFilePath = systemRoot.$this->exportDir.$this->name.".".$this->fileType;
		return fileManager::remove($headFilePath);
	}
	
	/**
	 * Creates the index base element.
	 * 
	 * @param	DOMParser	$builder
	 * 		The parser used to create the element.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected function getIndexBase($builder)
	{
		$newItem = parent::getIndexBase($builder);
		$builder->attr($newItem, "seed", $this->seed);
		
		return $newItem;
	}
	
	/**
	 * Returns the auxiliary's seed.
	 * 
	 * @return	integer
	 * 		{description}
	 */
	public function getSeed()
	{
		return $this->seed;
	}
	
	/**
	 * Returns the auxiliary's title.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getTitle()
	{
		return $this->auxTitle;
	}
	
	/**
	 * Returns the auxiliary parent's title.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getParentTitle()
	{
		return $this->title;
	}
	
	/**
	 * Sets the title of the object.
	 * 
	 * @param	string	$title
	 * 		The new title.
	 * 
	 * @return	void
	 */
	public function setTitle($title)
	{
		$this->auxTitle = $title;
	}
	
	/**
	 * Gets the hashed filename of the object.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected function getFileName()
	{
		return Smodule::fileName($this->id, $this->seed);
	}
}
//#section_end#
?>