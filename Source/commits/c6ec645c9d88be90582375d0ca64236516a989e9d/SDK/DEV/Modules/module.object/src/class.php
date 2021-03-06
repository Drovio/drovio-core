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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Environment", "url");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Modules", "moduleGroup");
importer::import("DEV", "Modules", "components/mView");
importer::import("DEV", "Modules", "components/mQuery");
importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \ESS\Protocol\BootLoader;
use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \DEV\Modules\moduleGroup;
use \DEV\Modules\components\mView;
use \DEV\Modules\components\mQuery;
use \DEV\Modules\modulesProject;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Resources\paths;

/**
 * Module object
 * 
 * System's Module Manager.
 * 
 * @version	0.1-10
 * @created	April 2, 2014, 10:41 (EEST)
 * @updated	February 12, 2015, 12:25 (EET)
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
		$this->vcs = new vcs(modulesProject::PROJECT_ID);
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
		$dbc = new dbConnection();
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
		$dbc = new dbConnection();
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
	 * @param	integer	$parentID
	 * 		The parent module group id.
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
		$dbc = new dbConnection();
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
	 * @param	boolean	$update
	 * 		Indicates whether the item should be updated for the vcs.
	 * 
	 * @return	string
	 * 		The file path.
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
	 * Removes a given view from the module.
	 * 
	 * @param	string	$viewID
	 * 		The view id to be removed.
	 * 
	 * @return	mixed
	 * 		True on success and false on failure.
	 * 		If there is an error, it will be returned as string.
	 */
	public function removeView($viewID)
	{
		// Delete from source map index
		$status = FALSE;
		try
		{
			// Update index info vcs
			$itemTrunkPath = $this->getInfoItem();
			
			// Create index entry
			$parser = new DOMParser();
			$parser->load($itemTrunkPath, FALSE);
			$viewEntry = $parser->find($viewID);
			if (is_null($viewEntry))
				throw new Exception("View ID does not exist!");
				
			$parser->replace($viewEntry, NULL);
			$status = $parser->update();
		}
		catch (Exception $ex)
		{
			$status = $ex->getMessage();
		}
		
		// If delete is successful, update map file and delete from vcs
		if ($status === TRUE)
		{
			// Update vcs map file
			$this->getInfoItem(TRUE);
			
			// Remove object from vcs
			$view = $this->getView("", $viewID);
			$view->remove();
		}
		
		return $status;
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
	 * Removes a given query from the module.
	 * 
	 * @param	string	$queryID
	 * 		The query id to be removed.
	 * 
	 * @return	mixed
	 * 		True on success and false on failure.
	 * 		If there is an error, it will be returned as string.
	 */
	public function removeQuery($queryID)
	{
		// Delete from source map index
		$status = FALSE;
		try
		{
			// Update index info vcs
			$itemTrunkPath = $this->getInfoItem();
			
			// Create index entry
			$parser = new DOMParser();
			$parser->load($itemTrunkPath, FALSE);
			$queryEntry = $parser->find($queryID);
			if (is_null($queryEntry))
				throw new Exception("Query ID does not exist!");
				
			$parser->replace($queryEntry, NULL);
			$status = $parser->update();
		}
		catch (Exception $ex)
		{
			$status = $ex->getMessage();
		}
		
		// If delete is successful, update map file and delete from vcs
		if ($status === TRUE)
		{
			// Update vcs map file
			$this->getInfoItem(TRUE);
			
			// Remove object from vcs
			$query = $this->getQuery("", $queryID);
			$query->remove();
		}
		
		return $status;
	}
	
	/**
	 * Get all module queries in an array.
	 * 
	 * @return	array
	 * 		An array of id and query name.
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
	 * Remove the module from the database and from the system.
	 * It must be empty of views and queries.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Check if module is empty of views and queries
		$views = $this->getViews();
		if (count($views) > 0)
			return FALSE;
			
		$queries = $this->getQueries();
		if (count($queries) > 0)
			return FALSE;
		
		// Remove module from database
		$dbc = new dbConnection();
		$q = new dbQuery("1968995495", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		return $dbc->execute($q, $attr);
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
			$viewsCSS .= $viewObject->getCSS()."\n";
		}
		
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
			$viewsJS .= $viewObject->getJSCode()."\n";
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