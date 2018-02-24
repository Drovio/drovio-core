<?php
//#section#[header]
// Namespace
namespace API\Developer\components;

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
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "misc::vcs");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Developer", "components::units::modules::moduleGroup");
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \ESS\Protocol\client\BootLoader;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\projects\project;
use \API\Developer\misc\vcs;
use \API\Developer\components\units\modules\module;
use \API\Developer\components\units\modules\moduleGroup;
use \API\Developer\profiler\tester;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * General Module Manager
 * 
 * Manages all modules including export, release and other options.
 * 
 * @version	{empty}
 * @created	March 28, 2013, 14:14 (EET)
 * @revised	January 20, 2014, 14:13 (EET)
 */
class moduleManager
{
	/**
	 * Set the tester mode status for the given module id.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
	 * 
	 * @param	boolean	$status
	 * 		The tester mode status.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function setTesterStatus($moduleID, $status = TRUE)
	{
		if ($status)
			return tester::activatePackage("mdl_".$moduleID);
		else
			return tester::deactivatePackage("mdl_".$moduleID);
	}
	
	/**
	 * Get the tester mode status for the given module id.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
	 * 
	 * @return	void
	 */
	public static function getTesterStatus($moduleID)
	{
		return tester::packageStatus("mdl_".$moduleID);
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use exportModules() instead.
	 */
	public static function checkoutModules()
	{
		self::exportModules();
	}
	
	/**
	 * Deploys all modules (views and queries) to latest from the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to deploy.
	 * 
	 * @return	void
	 */
	public static function deploy($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$repository = project::getRepository(2);
		$vcs = new vcs($repository, FALSE);
		$releasePath = $vcs->getCurrentRelease($branchName);
		
		// Get all modules
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1464459212", "units.modules");
		
		/*
		// Prepare xml for imports
		$importsParser = new DOMParser();
		$importsRoot = $importsParser->create("modules");
		$importsParser->append($importsRoot);
		
		// Prepare xml for inner
		$innerParser = new DOMParser();
		$innerRoot = $innerParser->create("modules");
		$innerParser->append($innerRoot);
		*/
		
		$result = $dbc->execute($dbq);
		while ($row = $dbc->fetch($result))
		{
			$moduleID = $row['id'];
			
			// Release paths
			$moduleInnerPath = moduleGroup::getTrail($row['group_id']).module::getDirectoryName($moduleID);
			$releaseModulePath = $releasePath.$moduleInnerPath;
			$modulePath = systemRoot."/System/Library/Modules/".$moduleInnerPath;
			
			// Check if module has been committed
			if (!file_exists($releaseModulePath))
				continue;
			
			
			// Create export index
			$parser = new DOMParser();
			$root = $parser->create("module");
			$parser->append($root);
			
			// Init Module CSS and JS
			$moduleCSS = "";
			$moduleJS = "";
			
			// Load views
			$parser = new DOMParser();
			$parser->load($releaseModulePath."/index.xml", FALSE);
			$views = $parser->evaluate("//view");
		
			$moduleViews = array();
			foreach ($views as $view)
				$moduleViews[$parser->attr($view, "id")] = $parser->attr($view, "name");
			
			// Deploy Views
			$vParser = new DOMParser();
			$mRoot = $vParser->create("module");
			$vParser->append($mRoot);
			foreach ($moduleViews as $viewID => $viewName)
			{
				$viewFolder = $releaseModulePath."/views/".$viewID.".view/";
				
				// Copy view code
				$contents = fileManager::get($viewFolder."view.html");
				fileManager::create($modulePath."/".$viewID.".html", $contents, TRUE);
				
				$contents = fileManager::get($viewFolder."view.php");
				fileManager::create($modulePath."/".$viewID.".php", $contents, TRUE);
				
				// Gather css and js
				$moduleCSS .= trim(fileManager::get($viewFolder."style.css"))."\n";
				$moduleJS .= trim(fileManager::get($viewFolder."script.js"))."\n";
				
				// Create index entry
				$viewIndexEntry = $vParser->create("view", "", $viewID);
				$vParser->attr($viewIndexEntry, "name", $viewName);
				$vParser->append($mRoot, $viewIndexEntry);
			}
			
			if (!empty($moduleCSS))
				$vParser->attr($mRoot, "css", "1");
			
			if (!empty($moduleJS))
				$vParser->attr($mRoot, "js", "1");
			
			// Write index file
			$output = $vParser->getXML();
			fileManager::create($modulePath."/index.xml", $output, TRUE);
			
			// Deploy Queries
			
			
			
			// Save Index
			fileManager::create($modulePath."/index.xml", "", TRUE);
			$parser->save($modulePath."/index.xml");
			
			// Export View CSS and JS
			BootLoader::exportCSS("Modules", "Modules", $moduleID, $moduleCSS);
			BootLoader::exportJS("Modules", "Modules", $moduleID, $moduleJS);
		}
/*
		// Save Dependencies
		fileManager::create(systemRoot."/System/Resources/Analytics/metrics/modules/corePackages.xml", "", $format = TRUE);
		fileManager::create(systemRoot."/System/Resources/Analytics/metrics/modules/innerModules.xml", "", $format = TRUE);
		$importsParser->save(systemRoot."/System/Resources/Analytics/metrics/modules/", "corePackages.xml", $format = TRUE);
		$innerParser->save(systemRoot."/System/Resources/Analytics/metrics/modules/", "innerModules.xml", $format = TRUE);
		*/
	}
	
	/**
	 * Exports all modules to latest.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use deploy() instead.
	 */
	public static function exportModules()
	{
		// Get all modules
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1464459212", "units.modules");
		
		// Prepare xml for imports
		$importsParser = new DOMParser();
		$importsRoot = $importsParser->create("modules");
		$importsParser->append($importsRoot);
		
		// Prepare xml for inner
		$innerParser = new DOMParser();
		$innerRoot = $innerParser->create("modules");
		$innerParser->append($innerRoot);
		
		
		$result = $dbc->execute_query($dbq);
		while ($row = $dbc->fetch($result))
		{
			$moduleObj = new module($row['id']);
			$moduleObj->export();
			
			// Export Dependencies and inner modules
			$views = $moduleObj->getViews();
			foreach ($views as $viewID => $viewName)
			{
				$viewObject = $moduleObj->getView($viewName, $viewID);
			
				// Get Imports
				$imports = $viewObject->getDependencies();
				if (!empty($imports))
				{
					$normalImports = self::normalizeImports($imports);
					$moduleNode = $importsParser->create("view", "", "v".$viewID);
					$importsParser->attr($moduleNode, "mid", $row['id']);
					$importsParser->append($importsRoot, $moduleNode);
					foreach ($normalImports as $import)
					{
						$importNode = $importsParser->create("package", $import);
						$importsParser->append($moduleNode, $importNode);
					}
				}
				
				// Get Inner Modules
				$innerModules = $viewObject->getInnerModules();
				if (!empty($innerModules))
				{
					$moduleNode = $innerParser->create("view", "", "v".$viewID);
					$innerParser->attr($moduleNode, "mid", $row['id']);
					$innerParser->append($innerRoot, $moduleNode);
					foreach ($innerModules as $name => $inId)
					{
						$innerNode = $innerParser->create("inner", "", "i.".$inId);
						$innerParser->attr($innerNode, "name", $name);
						$innerParser->attr($innerNode, "inid", $inId);
						$innerParser->append($moduleNode, $innerNode);
					}
				}
			}
		}

		// Save Dependencies
		fileManager::create(systemRoot."/System/Resources/Analytics/metrics/modules/corePackages.xml", "", $format = TRUE);
		fileManager::create(systemRoot."/System/Resources/Analytics/metrics/modules/innerModules.xml", "", $format = TRUE);
		$importsParser->save(systemRoot."/System/Resources/Analytics/metrics/modules/", "corePackages.xml", $format = TRUE);
		$innerParser->save(systemRoot."/System/Resources/Analytics/metrics/modules/", "innerModules.xml", $format = TRUE);
	}
	
	/**
	 * Normalizes all the module imports into a single array with strings (the full import as item).
	 * 
	 * @param	array	$imports
	 * 		The module imports (Lib -> Pkg/Obj).
	 * 
	 * @return	array
	 * 		{description}
	 */
	private static function normalizeImports($imports)
	{
		$finalImports = array();
		foreach ($imports as $lib => $objects)
			foreach ($objects as $obj)
				$finalImports[] = "\\".$lib."\\".$obj;
			
		return $finalImports;
	}
}
//#section_end#
?>