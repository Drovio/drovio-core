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
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Modules", "moduleGroup");

use \ESS\Protocol\client\BootLoader;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\profiler\tester;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Modules\module;
use \DEV\Modules\moduleGroup;

/**
 * General Module Manager
 * 
 * Manages all modules including publish and some analysis.
 * 
 * @version	{empty}
 * @created	April 2, 2014, 11:02 (EEST)
 * @revised	June 12, 2014, 12:30 (EEST)
 */
class moduleManager
{
	/**
	 * Publish all modules (views and queries) to latest from the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to deploy.
	 * 
	 * @return	void
	 */
	public static function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$project = new project(2);
		$vcs = new vcs(2);
		$releasePath = $vcs->getCurrentRelease($branchName);
		
		// Get all modules
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1464459212", "units.modules");
		
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
			$mParser = new DOMParser();
			$mRoot = $mParser->create("module");
			$mParser->append($mRoot);
			
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
			
			// Init inner modules array
			$innerModules = array();
			
			// Deploy Views
			$viewIndexRootEntry = $mParser->create("views");
			$mParser->append($mRoot, $viewIndexRootEntry);
			foreach ($moduleViews as $viewID => $viewName)
			{
				$viewFolder = $releaseModulePath."/views/".$viewID.".view/";
				
				// Copy view code
				$contents = fileManager::get($viewFolder."view.html");
				/*$parser = new DOMParser();
				$parser->loadContent($contents, DOMParser::TYPE_XML);
				$mViewHTML = $parser->getHTML();*/
				fileManager::create($modulePath."/".$viewID.".html", $contents, TRUE);
				fileManager::create($modulePath."/v/".$viewID.".html", $contents, TRUE);
				
				$contents = fileManager::get($viewFolder."view.php");
				fileManager::create($modulePath."/".$viewID.".php", $contents, TRUE);
				fileManager::create($modulePath."/v/".$viewID.".php", $contents, TRUE);
				
				// Gather css and js
				$moduleCSS .= trim(fileManager::get($viewFolder."style.css"))."\n";
				$moduleJS .= trim(fileManager::get($viewFolder."script.js"))."\n";
				
				// Create index entry
				$viewIndexEntry = $mParser->create("view", "", $viewID);
				$mParser->attr($viewIndexEntry, "name", $viewName);
				$mParser->append($viewIndexRootEntry, $viewIndexEntry);
				
				// Load inner modules
				$ip = new DOMParser();
				$ip->load($viewFolder."index.xml", FALSE);
				$innerEntries = $ip->evaluate("//module");
				foreach ($innerEntries as $entry)
					$innerModules[] = $ip->nodeValue($entry);
			}
			
			
			// Load queries
			$parser = new DOMParser();
			$parser->load($releaseModulePath."/index.xml", FALSE);
			$queries = $parser->evaluate("//query");
		
			$moduleQueries = array();
			foreach ($queries as $query)
				$moduleQueries[$parser->attr($query, "id")] = $parser->attr($query, "name");
			
			// Deploy queries
			$queryIndexRoodEntry = $mParser->create("queries");
			$mParser->append($mRoot, $queryIndexRoodEntry);
			foreach ($moduleQueries as $queryID => $queryName)
			{
				$queryFolder = $releaseModulePath."/sql/".$queryID.".query/";
				
				// Copy view code
				$contents = fileManager::get($queryFolder."query.sql");
				/*$parser = new DOMParser();
				$parser->loadContent($contents, DOMParser::TYPE_XML);
				$mViewHTML = $parser->getHTML();*/
				fileManager::create($modulePath."/q/".$queryID.".sql", $contents, TRUE);
				
				// Create index entry
				$queryIndexEntry = $mParser->create("query", "", $queryID);
				$mParser->attr($queryIndexEntry, "name", $queryName);
				$mParser->append($queryIndexRoodEntry, $queryIndexEntry);
			}
			
			if (!empty($moduleCSS))
				$mParser->attr($mRoot, "css", "1");
			
			if (!empty($moduleJS))
				$mParser->attr($mRoot, "js", "1");
			
			// Add inner modules
			$imRootEntry = $mParser->create("inner");
			$mParser->append($mRoot, $imRootEntry);
			$innerModules = array_unique($innerModules);
			foreach ($innerModules as $iModuleID)
			{
				$mEntry = $mParser->create("im", $iModuleID);
				$mParser->append($imRootEntry, $mEntry);
			}
			
			// Write index file
			fileManager::create($modulePath."/index.xml", "", TRUE);
			$mParser->save($modulePath."/index.xml");
			
			// Replace resources vars in css
			$resourcePath = "/Library/Media/m";
			$moduleCSS = str_replace("%resources%", $resourcePath, $moduleCSS);
			
			// Export View CSS and JS
			BootLoader::exportCSS("Modules", "Modules", $moduleID, $moduleCSS);
			BootLoader::exportJS("Modules", "Modules", $moduleID, $moduleJS);
		}
	}
}
//#section_end#
?>