<?php
//#section#[header]
// Namespace
namespace DEV\Modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Modules
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Modules", "moduleGroup");
importer::import("DEV", "Modules", "components::mView");
importer::import("DEV", "Modules", "components::mQuery");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");

use \ESS\Protocol\client\BootLoader;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \DEV\Modules\moduleGroup;
use \DEV\Modules\components\mView;
use \DEV\Modules\components\mQuery;
use \DEV\Projects\project;
use \DEV\Version\vcs;

/**
 * Module object
 * 
 * System's Module Manager.
 * 
 * @version	{empty}
 * @created	April 2, 2014, 10:41 (EEST)
 * @revised	April 30, 2014, 15:30 (EEST)
 */
class module
{
	/**
	 * The module id.
	 * 
	 * @type	string
	 */
	private $id;
	
	/**
	 * The module title.
	 * 
	 * @type	string
	 */
	private $title;
	
	/**
	 * The module description.
	 * 
	 * @type	string
	 */
	private $description;
	
	/**
	 * The module group id.
	 * 
	 * @type	integer
	 */
	private $groupID;
	
	/**
	 * The module repository path.
	 * 
	 * @type	string
	 */
	private $modulePath;
	
	/**
	 * The vcs object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the module object.
	 * 
	 * @param	integer	$id
	 * 		The module's id.
	 * 		If given, load the module information.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Load module if id not empty
		if (!empty($id))
		{
			// Initialize variable
			$this->id = $id;
			
			// Get module info
			$this->loadModuleInfo();
		}
		
		// Initialize vcs and create structure if not any
		$this->vcs = new vcs(2);
		$this->vcs->createStructure();
	}
	
	/**
	 * Loads all module info from the database.
	 * 
	 * @return	void
	 */
	private function loadModuleInfo()
	{
		// Get module info
		$dbc = new interDbConnection();
		$dbq = new dbQuery("361601426", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		$result = $dbc->execute($dbq, $attr);
		$module = $dbc->fetch($result);
		
		// Initialize variables
		$this->title = $module['module_title'];
		$this->description = $module['module_description'];
		$this->groupID = $module['group_id'];
		
		// Get module full directory
		$this->modulePath = moduleGroup::getTrail($this->groupID).$this->getDirectoryName($this->id);
	}
	
	/**
	 * Updates the basic information for the module.
	 * 
	 * @param	string	$title
	 * 		The new module title.
	 * 
	 * @param	string	$description
	 * 		The new module description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($title, $description = "")
	{
		$this->title = $title;
		$this->description = $description;
		
		// Update Database
		$dbc = new interDbConnection();
		$dbq = new dbQuery("918340463", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Create a new module.
	 * 
	 * @param	string	$title
	 * 		The module title.
	 * 
	 * @param	{type}	$parentID
	 * 		{description}
	 * 
	 * @param	string	$description
	 * 		The module description, if any.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($title, $parentID, $description = "")
	{
		// Create database entry
		$dbc = new interDbConnection();
		$dbq = new dbQuery("499426318", "units.modules");
		
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['group_id'] = $parentID;
		$result = $dbc->execute($dbq, $attr);

		if (!$result)
			return FALSE;
		
		// Fetch module and load database info
		$module = $dbc->fetch($result);
		$this->id = $module['last_id'];
		$this->loadModuleInfo();
		
		// Create structure
		$this->createStructure();
		
		// Create main view
		$this->createView();
		
		return TRUE;
	}
	
	/**
	 * Creates the module structure, including indexes and folders.
	 * 
	 * @return	void
	 */
	private function createStructure()
	{
		// Create index item
		$itemID = $this->getInfoItemID();
		$itemPath = $this->modulePath;
		$itemName = "index.xml";
		$itemTrunkPath = $this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = FALSE);
		
		// Create index
		$parser = new DOMParser();
		$root = $parser->create("module", "", $this->id);
		$parser->append($root);
		
		$sqlRoot = $parser->create("sql");
		$parser->append($root, $sqlRoot);
		
		$viewsRoot = $parser->create("views");
		$parser->append($root, $viewsRoot);
		
		fileManager::create($itemTrunkPath, "", TRUE);
		$parser->save($itemTrunkPath);
	}
	
	/**
	 * Gets the information file path.
	 * 
	 * @param	{type}	$update
	 * 		Indicates whether the item should be updated for the vcs.
	 * 
	 * @return	void
	 */
	private function getInfoItem($update = FALSE)
	{
		$itemID = $this->getInfoItemID();
		if ($update)
			return $this->vcs->updateItem($itemID, $forceCommit = TRUE);
		else
			return $this->vcs->getItemTrunkPath($itemID);
	}
	
	/**
	 * Gets the information file vcs item it.
	 * 
	 * @return	string
	 * 		The item id.
	 */
	private function getInfoItemID()
	{
		return "i".md5("moduleInfo".$this->id);
	}
	
	/**
	 * Create a new module view.
	 * 
	 * @param	string	$name
	 * 		The view's name.
	 * 		If empty, get the module's title (to be avoided twice).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createView($name = "")
	{
		// View ids
		if (empty($name))
			$name = $this->title;
		$viewID = $this->getObjectID("v", $this->getSeed());
		
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem(TRUE);
		
		// Create index entry
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		$viewsRoot = $parser->evaluate("//views")->item(0);
		$viewEntry = $parser->create("view", "", $viewID, "");
		$parser->attr($viewEntry, "name", $name);
		$parser->append($viewsRoot, $viewEntry);
		$parser->update();
		
		// Create view
		$viewPath = $this->modulePath."/views/";
		$mView = new mView($this->vcs, $this->id, $viewPath);
		return $mView->create($viewID);
	}
	
	/**
	 * Gets a module view by name or by id.
	 * In case of two views with the same name, the first is selected.
	 * 
	 * @param	string	$name
	 * 		The view's name.
	 * 
	 * @param	string	$viewID
	 * 		The view id.
	 * 
	 * @return	mixed
	 * 		The mView object or NULL if the view doesn't exist.
	 */
	public function getView($name = "", $viewID = "")
	{
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem();
		
		// Get seed by view name
		if (empty($viewID))
		{
			if (empty($name))
				$name = $this->title;
			
			// Get viewID
			$parser = new DOMParser();
			$parser->load($itemTrunkPath, FALSE);
			$viewEntry = $parser->evaluate("//view[@name='$name']")->item(0);
			if (empty($viewEntry))
				return NULL;
				
			$viewID = $parser->attr($viewEntry, "id");
		}
		
		// Get view
		$viewPath = $this->modulePath."/views/";
		return new mView($this->vcs, $this->id, $viewPath, $viewID);
	}
	
	/**
	 * Runs the view from the trunk.
	 * 
	 * @param	string	$name
	 * 		The view name. If empty, take the module's name.
	 * 
	 * @return	mixed
	 * 		The view result.
	 */
	public function runView($name = "")
	{
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem();
		
		// Get seed by view name
		if (empty($name))
			$name = $this->title;
		
		// Get viewID
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		$viewEntry = $parser->evaluate("//view[@name='$name']")->item(0);
		if (empty($viewEntry))
			return FALSE;
			
		$viewID = $parser->attr($viewEntry, "id");
		
		// Get view
		$viewPath = $this->modulePath."/views/";
		$moduleView = new mView($this->vcs, $this->id, $viewPath, $viewID);
		return $moduleView->run();
	}
	
	/**
	 * Update a view's name by id.
	 * 
	 * @param	string	$viewID
	 * 		The view id to update.
	 * 
	 * @param	string	$name
	 * 		The new view name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateViewName($viewID, $name)
	{
		if (empty($name))
			return FALSE;
			
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem(TRUE);
		
		// Update index
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		$viewEntry = $parser->find($viewID);
		$parser->attr($viewEntry, "name", $name);
		$parser->update();
		
		return TRUE;
	}
	
	/**
	 * Get all module's views.
	 * 
	 * @return	array
	 * 		An array of id => view name.
	 */
	public function getViews()
	{
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem();
		
		// Get views
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		$views = $parser->evaluate("//view");
		
		$moduleViews = array();
		foreach ($views as $view)
			$moduleViews[$parser->attr($view, "id")] = $parser->attr($view, "name");
		
		return $moduleViews;
	}
	
	/**
	 * Creates a new sql query.
	 * 
	 * @param	string	$name
	 * 		The query name.
	 * 
	 * @param	string	$description
	 * 		The query description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createQuery($name, $description)
	{
		// View ids
		$queryID = $this->getObjectID("q", $this->getSeed());
		
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem(TRUE);
		
		// Create index entry
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		$sqlRoot = $parser->evaluate("//sql")->item(0);
		$query = $parser->create("query", "", $queryID, "");
		$parser->attr($query, "name", $name);
		$parser->append($sqlRoot, $query);
		$parser->update();
		
		// Create view
		$sqlPath = $this->modulePath."/sql/";
		$mQuery = new mQuery($this->vcs, $this->id, $sqlPath);
		return $mQuery->create($queryID, $name, $description);
	}
	
	/**
	 * Gets the module query by name or by id.
	 * 
	 * @param	string	$name
	 * 		The query name.
	 * 
	 * @param	string	$queryID
	 * 		The query id.
	 * 
	 * @return	mixed
	 * 		The mQuery object or NULL if query doesn't exist.
	 */
	public function getQuery($name = "", $queryID = "")
	{
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem();
		
		// Get seed by view name
		if (empty($queryID))
		{
			// Get seed by view name
			$parser = new DOMParser();
			$parser->load($itemTrunkPath, FALSE);
			$queryEntry = $parser->evaluate("//query[@name='$name']")->item(0);
			if (empty($queryEntry))
				return NULL;
				
			$queryID = $parser->attr($queryEntry, "id");
		}
		
		// Get Query
		$sqlPath = $this->modulePath."/sql/";
		return new mQuery($this->vcs, $this->id, $sqlPath, $queryID);
	}
	
	/**
	 * Updates a query name.
	 * 
	 * @param	string	$queryID
	 * 		The query id to update.
	 * 
	 * @param	string	$name
	 * 		The query name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateQueryName($queryID, $name)
	{
		// Check if name is empty
		if (empty($name))
			return FALSE;
		
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem(TRUE);
		
		// Update name
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		$queryEntry = $parser->find($queryID);
		$parser->attr($queryEntry, "name", $name);
		$parser->update();
		
		return TRUE;
	}
	
	/**
	 * Get all module queries in an array.
	 * 
	 * @return	array
	 * 		An array of id => query name.
	 */
	public function getQueries()
	{
		// Update index info vcs
		$itemTrunkPath = $this->getInfoItem();
		
		// Get Queries
		$parser = new DOMParser();
		$parser->load($itemTrunkPath, FALSE);
		$queries = $parser->evaluate("//query");
		
		$moduleQueries = array();
		foreach ($queries as $query)
			$moduleQueries[$parser->attr($query, "id")] = $parser->attr($query, "name");
		
		return $moduleQueries;
	}
	
	/**
	 * Get the module title.
	 * 
	 * @return	string
	 * 		The module title.
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Get the module description.
	 * 
	 * @return	string
	 * 		The module description.
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * Gets whether this module has css code.
	 * 
	 * @return	boolean
	 * 		True or False.
	 */
	public function hasCSS()
	{
		$css = $this->loadCSS();
		return !empty($css);
	}
	
	/**
	 * Loads all the module view's css.
	 * 
	 * @return	string
	 * 		The total css code.
	 */
	public function loadCSS()
	{
		// Get all views
		$views = $this->getViews();
		$viewsCSS = "";
		foreach ($views as $viewID => $viewName)
		{
			$viewObject = $this->getView($viewName, $viewID);
			$viewsCSS .= $viewObject->getCSS();
		}
		
		// Replace resource vars
		$project = new project(2);
		$resourcePath = $project->getResourcesFolder();
		$viewsCSS = str_replace("%resources%", $resourcePath, $viewsCSS);
		
		return trim($viewsCSS);
	}
	
	/**
	 * Gets whether this module has js code.
	 * 
	 * @return	boolean
	 * 		True or False.
	 */
	public function hasJS()
	{
		$js = $this->loadJS();
		return !empty($js);
	}
	
	/**
	 * Loads all the module view's jss.
	 * 
	 * @return	string
	 * 		The total js code.
	 */
	public function loadJS()
	{
		// Get all views
		$views = $this->getViews();
		$viewsJS = "";
		foreach ($views as $viewID => $viewName)
		{
			$viewObject = $this->getView($viewName, $viewID);
			$viewsJS .= $viewObject->getJSCode();
		}
		
		return trim($viewsJS);
	}
	
	/**
	 * Gets a random unique seed.
	 * 
	 * @return	string
	 * 		A unique random seed.
	 */
	private function getSeed()
	{
		return md5("m".$this->id.microtime(TRUE).rand());
	}
	
	/**
	 * Gets the module directory name.
	 * 
	 * @param	integer	$id
	 * 		The module id.
	 * 
	 * @return	string
	 * 		The module directory name.
	 */
	public static function getDirectoryName($id)
	{
		return "m".$id.".module";
	}
	
	/**
	 * Gets an internal object id.
	 * 
	 * @param	string	$prefix
	 * 		The id prefix.
	 * 
	 * @param	string	$seed
	 * 		The unique seed.
	 * 
	 * @return	string
	 * 		An object id based on the given seed.
	 */
	private function getObjectID($prefix, $seed)
	{
		return $prefix.md5("moduleObject".$seed);
	}
}
//#section_end#
?>