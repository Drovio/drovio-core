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
importer::import("API", "Content", "filesystem::fileManager");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Security", "privileges::modules::manage::developer");
importer::import("API", "Developer", "model::units::module");

use \API\Platform\DOM\DOMParser;
use \API\Content\filesystem\fileManager;
use \API\Model\units\modules\Smodule;
use \API\Security\privileges\modules\manage\developer;
use \API\Developer\model\units\module;

/**
 * Auxiliary manager
 * 
 * The system's auxiliary handler.
 * 
 * @version	{empty}
 * @created	March 20, 2013, 10:27 (EET)
 * @revised	March 20, 2013, 10:27 (EET)
 * 
 * @deprecated	Use \API\Developer\components\modules\ instead.
 */
class Uauxiliary extends module
{
	/**
	 * The auxiliary seed
	 * 
	 * @type	integer
	 */
	protected $seed;
	/**
	 * The auxiliary title
	 * 
	 * @type	string
	 */
	protected $auxTitle;
	
	/**
	 * Initialize the object
	 * 
	 * @param	integer	$id
	 * 		The module's id
	 * 
	 * @param	integer	$seed
	 * 		The auxiliary seed
	 * 
	 * @return	void
	 */
	public function initialize($id, $seed = "")
	{
		$this->initialize_info();
		$this->id = $id;
		$this->seed = $seed;
		$this->name = $this->_get_fileName();
		$this->inner = array();
		
		// Load Info
		$this->load();
	}
	
	/**
	 * Initializes the auxiliary's index information
	 * 
	 * @return	void
	 */
	protected function initialize_info()
	{
		parent::initialize_info();
		
		if (!isset($this->vcsTrunk))
			return;

		// Get base object
		$info_parser = new DOMParser();
		if ($this->seed != "" && !is_null($this->seed))
		{
			$this->name = $this->_get_fileName();
			$base = $this->vcsTrunk->get_base($info_parser);
		}
		else if ($this->auxTitle != "" && !is_null($this->auxTitle))
			$base = $this->vcsTrunk->get_baseByTitle($info_parser, $this->auxTitle);
		else
			return FALSE;

		if (is_null($base))
			return FALSE;
		
		$this->seed = ($this->seed != "" ? $this->seed : $base->getAttribute('seed'));
		$this->auxTitle = $base->getAttribute('title');
		$this->description = $info_parser->evaluate("description", $base)->item(0)->nodeValue;
		$this->name = $this->_get_fileName();
	}
	
	/**
	 * Sets the auxiliary's title
	 * 
	 * @param	string	$aux_title
	 * 		The title
	 * 
	 * @return	void
	 */
	public function set_title($aux_title)
	{
		$this->auxTitle = $aux_title;
	}
	
	/**
	 * Get the auxiliary's title
	 * 
	 * @return	string
	 */
	public function get_title()
	{
		return $this->auxTitle;
	}
	
	/**
	 * Get the module's title
	 * 
	 * @return	string
	 */
	public function get_parentTitle()
	{
		return $this->title;
	}
	
	/**
	 * Update the auxiliary's content.
	 * 
	 * @param	string	$title
	 * 		The auxiliary's title
	 * 
	 * @param	string	$description
	 * 		The auxiliary's description
	 * 
	 * @param	string	$code
	 * 		The auxiliary's source code
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
		
		// Update Code files
		return parent::update($title, $description, $code, $branch);
	}
	
	/**
	 * Create a new auxiliary module
	 * 
	 * @param	integer	$parent_id
	 * 		The parent module's id
	 * 
	 * @param	string	$title
	 * 		The auxiliary's title
	 * 
	 * @param	string	$description
	 * 		The auxiliary's description
	 * 
	 * @return	boolean
	 */
	public function create($parent_id, $title, $description)
	{
		if (!developer::get_moduleStatus($parent_id))
			return FALSE;

		// Create seed
		$this->seed = rand();
		
		$this->initialize($parent_id, $this->seed);
		$this->load_databaseInfo();

		$this->auxTitle = $title;
		$this->description = $description;
		
		// Initialize Information
		$this->initialize_info();
		
		// Create vcs repository structure
		$this->VCS_create_object();

		// Update Content
		$this->update($title, $description);

		// Deploy
		$this->commit("Initial working file");
		
		return TRUE;
	}
	
	/**
	 * Delete the auxiliary's contents
	 * 
	 * @return	boolean
	 */
	public function delete()
	{
		if (!developer::get_moduleStatus($this->id))
			return FALSE;

		$this->load_databaseInfo();

		// Remove CVS Files
		$this->VCS_remove();

		// _____ Remove [Head] file
		$headFilePath = systemRoot.$this->directory.$this->name.".".$this->fileType;
		return fileManager::remove($headFilePath);
	}
	
	/**
	 * Returns the auxiliary's seed
	 * 
	 * @return	integer
	 */
	public function get_seed()
	{
		return $this->seed;
	}
	
	/**
	 * Update the auxiliary's index information
	 * 
	 * @return	void
	 */
	protected function update_indexInfo()
	{
		$info_parser = new DOMParser();
		$trunkBase = $this->get_indexInfo($info_parser);
		
		$info_parser->attr($trunkBase, "seed", $this->seed);

		$this->vcsTrunk->update_indexBase($info_parser, $trunkBase);
	}
	
	/**
	 * Get the filename of the auxiliary
	 * 
	 * @return	string
	 */
	protected function _get_fileName()
	{
		return Smodule::fileName($this->id, $this->seed);
	}
}
//#section_end#
?>