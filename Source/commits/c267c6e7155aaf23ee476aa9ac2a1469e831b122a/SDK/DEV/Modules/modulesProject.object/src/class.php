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
importer::import("ESS", "Protocol", "BootLoader2");
importer::import("ESS", "Environment", "url");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("SYS", "Resources", "pages/sitemap");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Modules", "moduleGroup");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");

use \ESS\Protocol\BootLoader;
use \ESS\Protocol\BootLoader2;
use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \SYS\Resources\pages\sitemap;
use \API\Model\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;
use \DEV\Version\vcs;
use \DEV\Modules\module;
use \DEV\Modules\moduleGroup;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;

/**
 * Modules Project
 * 
 * It is the modules project class.
 * Manages module publishing and analysis.
 * 
 * @version	0.3-5
 * @created	December 18, 2014, 11:04 (EET)
 * @updated	January 12, 2015, 10:44 (EET)
 */
class modulesProject extends project
{
	/**
	 * The modules project id.
	 * 
	 * @type	integer
	 */
	const PROJECT_ID = 2;
	
	/**
	 * The modules project type id.
	 * 
	 * @type	integer
	 */
	const PROJECT_TYPE = 2;

	/**
	 * The vcs manager object for the project.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initialize Modules Project
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Construct project
		parent::__construct(self::PROJECT_ID);
		
		// Initialize vcs
		$this->vcs = new vcs($this->getID());
	}
	
	/**
	 * Publish the modules project.
	 * Publish all modules (views and queries) to latest from the given branch.
	 * Generate sitemap.
	 * Publish all resources (media and files)
	 * 
	 * @param	string	$version
	 * 		The module project release version.
	 * 
	 * @param	string	$branchName
	 * 		The source's branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Check version
		if (empty($version))
			return FALSE;
			
		// Validate account
		if (!$this->validate())
			return FALSE;
		
		// Check branch name
		$branchName = (empty($branchName) ? vcs::MASTER_BRANCH : $branchName);
		
		// Create Publish Folder (if doesn't already exist)
		$publishFolder = projectLibrary::getPublishedPath($this->getID(), $version);
		folderManager::create(systemRoot.$publishFolder);
			
		// Publish Modules Source
		$status = $this->publishSource($version, $branchName);
		if (!$status)
			return FALSE;
		
		// Generate sitemap
		sitemap::generate();
		
		// Publish Resources
		$this->publishResources($publishFolder."/media/", $innerFolder = "/media/");
		$this->publishResources("/System/Resources/Modules/", $innerFolder = "/resources/");
		
		return TRUE;
	}
	
	/**
	 * Publish all modules (views and queries) to latest from the given branch.
	 * 
	 * @param	string	$version
	 * 		The module project release version.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function publishSource($version, $branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$releasePath = $this->vcs->getCurrentRelease($branchName);
		
		// Get publish folder
		$publishFolder = projectLibrary::getPublishedPath(self::PROJECT_ID, $version);
		
		// Get all modules
		$dbc = new dbConnection();
		$dbq = new dbQuery("1464459212", "units.modules");
		
		$result = $dbc->execute($dbq);
		while ($row = $dbc->fetch($result))
		{
			$moduleID = $row['id'];
			
			// Release paths
			$moduleInnerPath = moduleGroup::getTrail($row['group_id']).module::getDirectoryName($moduleID);
			$releaseModulePath = $releasePath.$moduleInnerPath;
			$modulePath = systemRoot."/System/Library/Modules/".$moduleInnerPath;
			
			// Resource url
			$resourcePath = $publishFolder."/media";
			$resourcePath = str_replace(paths::getPublishedPath(), "", $resourcePath);
			$resourceUrl = url::resolve("lib", $resourcePath);
			
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
			try
			{
				$parser->load($releaseModulePath."/index.xml", FALSE);
			}
			catch (Exception $ex)
			{
				// Module hasn't been committed yet, proceed
				continue;
			}
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
				
				// View html
				$contents = fileManager::get($viewFolder."view.html");
				
				// Replace resources vars in view html (if any)
				$contents = str_replace("%resources%", $resourceUrl, $contents);
				$contents = str_replace("%{resources}", $resourceUrl, $contents);
				$contents = str_replace("%media%", $resourceUrl, $contents);
				$contents = str_replace("%{media}", $resourceUrl, $contents);
				
				// Format html to be compact and save to file
				/*$parser = new DOMParser();
				$parser->loadContent($contents, DOMParser::TYPE_XML);
				$mViewHTML = $parser->getHTML();*/
				fileManager::create($modulePath."/v/".$viewID.".html", $contents, TRUE);
				
				// View php code
				$contents = fileManager::get($viewFolder."view.php");
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
				try
				{
					$ip->load($viewFolder."index.xml", FALSE);
					$innerEntries = $ip->evaluate("//module");
					foreach ($innerEntries as $entry)
						$innerModules[] = $ip->nodeValue($entry);
				}
				catch (Exception $ex)
				{
				}				
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
				
				// SQL Query
				$contents = fileManager::get($queryFolder."query.sql");
				fileManager::create($modulePath."/q/".$queryID.".sql", $contents, TRUE);
				
				// Create index entry
				$queryIndexEntry = $mParser->create("query", "", $queryID);
				$mParser->attr($queryIndexEntry, "name", $queryName);
				$mParser->append($queryIndexRoodEntry, $queryIndexEntry);
			}
			
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
			$moduleCSS = str_replace("%resources%", $resourceUrl, $moduleCSS);
			$moduleCSS = str_replace("%{resources}", $resourceUrl, $moduleCSS);
			$moduleCSS = str_replace("%media%", $resourceUrl, $moduleCSS);
			$moduleCSS = str_replace("%{media}", $resourceUrl, $moduleCSS);
			
			// Format css (protected full urls (http://, https://))
			$moduleCSS = str_replace("http://", "httpslsl", $moduleCSS);
			$moduleCSS = str_replace("https://", "httpsslsl", $moduleCSS);
			$moduleCSS = cssParser::format($moduleCSS);
			$moduleCSS = str_replace("httpslsl", "http://", $moduleCSS);
			$moduleCSS = str_replace("httpsslsl", "https://", $moduleCSS);
			
			// Export css to publish folder
			BootLoader2::exportCSS(self::PROJECT_ID, "Modules", $moduleID, $moduleCSS);
			
			// Replace resources vars in js
			$moduleJS = str_replace("%resources%", $resourceUrl, $moduleJS);
			$moduleJS = str_replace("%{resources}", $resourceUrl, $moduleJS);
			$moduleJS = str_replace("%media%", $resourceUrl, $moduleJS);
			$moduleJS = str_replace("%{media}", $resourceUrl, $moduleJS);
			
			// Format js
			$moduleJS = jsParser::format($moduleJS);
			
			// Export css and js to publish folder
			BootLoader2::exportJS(self::PROJECT_ID, "Modules", $moduleID, $moduleJS);
			
			// Write index file
			fileManager::create($modulePath."/index.xml", "", TRUE);
			$mParser->save($modulePath."/index.xml");
			
			
			// COMPATIBILITY
			
			// Export Module CSS
			BootLoader::exportCSS("Modules", "Modules", $moduleID, $moduleCSS);
			if (!empty($moduleCSS))
				$mParser->attr($mRoot, "css", "1");
			
			// Export Module JS
			$moduleJS = jsParser::format($moduleJS);
			BootLoader::exportJS("Modules", "Modules", $moduleID, $moduleJS);
			if (!empty($moduleJS))
				$mParser->attr($mRoot, "js", "1");
		}
		
		return TRUE;
	}
}
//#section_end#
?>