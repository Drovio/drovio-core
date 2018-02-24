<?php
//#section#[header]
// Namespace
namespace API\Developer\components\units\modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "components::units::modules::moduleGroup");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "DOMParser");

use \ESS\Protocol\client\BootLoader;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\misc\vcs;
use \API\Developer\components\units\modules\moduleGroup;
use \API\Developer\resources\paths;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;

class module
{
	private $id;
	
	private $title;
	
	private $description;
	
	private $groupID;
	
	private $repositoryPath;
	
	private $vcs;
	
	// Constructor Method
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
		$this->vcs = new vcs(paths::getDevPath()."/Repository/Modules/", FALSE);
		$this->vcs->createStructure();
	}
	
	private function loadModuleInfo()
	{
		// Get module info
		$dbc = new interDbConnection();
		$dbq = new dbQuery("361601426", "units.modules");
		
		$attr = array();
		$attr['id'] = $this->id;
		$result = $this->dbc->execute($dbq, $attr);
		$module = $this->dbc->fetch($result);
		
		// Initialize variables
		$this->title = $module['module_title'];
		$this->description = $module['module_description'];
		$this->groupID = $module['group_id'];
		
		// Get module full directory
		$this->repositoryPath = moduleGroup::getTrail($this->groupID).Smodule::directoryName($this->id);
	}
	
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
		$result = $this->dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	public function create($title, $parentID, $description = "")
	{
		// Create database entry
		$dbc = new interDbConnection();
		$dbq = new dbQuery("499426318", "units.modules");
		
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['group_id'] = $parentID;
		$result = $this->dbc->execute($dbq, $attr);

		if (!$result)
			return FALSE;
		
		// Fetch module and load database info
		$module = $this->dbc->fetch($result);
		$this->id = $module['last_id'];
		$this->loadModuleInfo();
		
		// Create vcs item
		$itemID = $this->getItemID();
		$itemPath = "/".$this->repositoryPath."/";
		$itemName = $this->getDirectoryName($this->id);
		$this->vcs->createItem($itemID, $itemPath, $itemName, $isFolder = TRUE);
		
		// Create structure
		$this->createStructure();
		
		// Create main view
		$this->createView();
	}
	
	private function createStructure()
	{
		// Get trunk path
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		// Create index
		$parser = new DOMParser();
		$root = $parser->create("module", "", $this->id);
		$parser->append($root);
		
		$sqlRoot = $parser->create("sql");
		$parser->append($root, $sqlRoot);
		
		$viewsRoot = $parser->create("views");
		$parser->append($root, $viewsRoot);
		
		$parser->save($itemTrunkPath, "index.xml");
		
		folderManager::create($itemTrunkPath."/views/", TRUE);
		folderManager::create($itemTrunkPath."/sql/", TRUE);
	}
	
	public function createView($name = "")
	{
		// Get item path
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// View ids
		if (!empty($name))
			$name = $this->title;
		$seed = $this->getSeed("s", $this->id);
		$viewID = $this->getViewID($seed);
		
		// Create index entry
		$parser = new DOMParser();
		$parser->load($itemTrunkPath."/index.xml", FALSE);
		$viewsRoot = $parser->evaluate("//views")->item(0);
		$viewEntry = $parser->create("view", "", $viewID, "");
		$parser->attr($viewEntry, "name", $name);
		$parser->append($viewsRoot, $viewEntry);
		$parser->update();
		
		// Create view
		$viewPath = $itemTrunkPath."/views/";
		$mView = new mView($this->vcs, $itemID, $viewPath);
		return $mView->create($viewID);
	}
	
	public function getView($name = "", $viewID = "")
	{
		// Get item path
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Get seed by view name
		if (!empty($name))
		{
			// Get seed
			$parser = new DOMParser();
			$parser->load($itemTrunkPath."/index.xml", FALSE);
			$viewEntry = $parser->evaluate("//view[@name='$name']")->item(0);
			$viewID = $parser->attr($viewEntry, "id");
		}
		
		// Create view
		$viewPath = $itemTrunkPath."/views/";
		return new mView($this->vcs, $viewPath, $viewID);
	}
	
	public function getViews()
	{
		$parser = new DOMParser();
		$parser->load($itemTrunkPath."/index.xml", FALSE);
		$views = $parser->evaluate("//view");
		
		$moduleViews = array();
		foreach ($views as $view)
			$moduleViews[$parser->attr($view, "seed")] = $parser->attr($view, "name");
		
		return $moduleViews;
	}
	
	private static function getViewID($seed = "")
	{
		return "v".md5("moduleView".$seed);
	}
	
	public function createQuery($name)
	{
		// Get item path
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->getItemTrunkPath($itemID);
		
		// View ids
		$seed = $this->getSeed("q", $this->id);
		$queryID = $this->getQueryID($seed);
		
		// Create index entry
		$parser = new DOMParser();
		$parser->load($itemTrunkPath."/index.xml", FALSE);
		$sqlRoot = $parser->evaluate("//sql")->item(0);
		$query = $parser->create("query", "", $queryID, "");
		$parser->attr($query, "name", $name);
		$parser->append($sqlRoot, $query);
		$parser->update();
		
		// Create view
		$sqlPath = $itemTrunkPath."/sql/";
		$mQuery = new mQuery($this->vcs, $itemID, $sqlPath);
		return $mQuery->create($queryID);
	}
	
	public function getQuery($name)
	{
		// Get item path
		$itemID = $this->getItemID();
		$itemTrunkPath = $this->vcs->updateItem($itemID);
		
		// Get seed by view name
		$parser = new DOMParser();
		$parser->load($itemTrunkPath."/index.xml", FALSE);
		$queryEntry = $parser->evaluate("//query[@name='$name']")->item(0);
		$seed = $parser->attr($queryEntry, "seed");
		
		// Create view
		$sqlPath = $itemTrunkPath."/sql/";
		$queryID = $this->getQueryID($seed);
		return new mQuery($this->vcs, $itemID, $sqlPath, $queryID);
	}
	
	public function getQueries()
	{
		$parser = new DOMParser();
		$parser->load($itemTrunkPath."/index.xml", FALSE);
		$views = $parser->evaluate("//query");
		
		$moduleViews = array();
		foreach ($views as $view)
			$moduleViews[$parser->attr($view, "seed")] = $parser->attr($view, "name");
		
		return $moduleViews;
	}
	
	private static function getSeed($prefix, $id)
	{
		return $prefix.md5("m".$id.time());
	}
	
	private static function getDirectoryName($id)
	{
		return "m".$id.".module";
	}
	
	private function getItemID()
	{
		return "m".md5("module".$this->id);
	}
}
//#section_end#
?>