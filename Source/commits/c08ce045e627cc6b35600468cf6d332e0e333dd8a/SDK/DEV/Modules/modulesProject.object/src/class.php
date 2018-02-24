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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Environment", "url");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("SYS", "Resources", "pages/sitemap");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Modules", "module");
importer::import("DEV", "Modules", "moduleGroup");
importer::import("DEV", "Tools", "parsers/cssParser");
importer::import("DEV", "Tools", "parsers/jsParser");

use \ESS\Protocol\BootLoader;
use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \SYS\Resources\pages\sitemap;
use \API\Model\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \DEV\Projects\project;
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
 * @version	0.2-3
 * @created	December 18, 2014, 11:04 (EET)
 * @revised	December 18, 2014, 11:57 (EET)
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
	 * @param	string	$branchName
	 * 		The source's branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function publish($branchName = vcs::MASTER_BRANCH)
	{
		// Validate account
		if (!$this->validate())
			return FALSE;
		
		// Check branch name
		$branchName = (empty($branchName) ? vcs::MASTER_BRANCH : $branchName);
			
		// Publish Modules Source
		$status = $this->publishSource($branchName);
		if (!$status)
			return FALSE;
		
		// Generate sitemap
		sitemap::generate();
		
		// Publish Resources
		$this->publishResources("/Library/Media/m/", $innerFolder = "/media/");
		$this->publishResources("/System/Resources/Modules/", $innerFolder = "/resources/");
		
		return TRUE;
	}
	
	/**
	 * Publish all modules (views and queries) to latest from the given branch.
	 * 
	 * @param	string	$branchName
	 * 		The branch name to publish.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function publishSource($branchName = vcs::MASTER_BRANCH)
	{
		// Get repository
		$releasePath = $this->vcs->getCurrentRelease($branchName);
		
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
				
				// Copy view code
				$contents = fileManager::get($viewFolder."view.html");
				/*$parser = new DOMParser();
				$parser->loadContent($contents, DOMParser::TYPE_XML);
				$mViewHTML = $parser->getHTML();*/
				fileManager::create($modulePath."/v/".$viewID.".html", $contents, TRUE);
				
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
			
			// Format css (protected full urls (http://, https://))
			$moduleCSS = str_replace("http://", "httpslsl", $moduleCSS);
			$moduleCSS = str_replace("https://", "httpsslsl", $moduleCSS);
			$moduleCSS = cssParser::format($moduleCSS);
			$moduleCSS = str_replace("httpslsl", "http://", $moduleCSS);
			$moduleCSS = str_replace("httpsslsl", "https://", $moduleCSS);
			
			// Replace resources vars in css
			$resourceUrl = url::resource("/Library/Media/m");
			$moduleCSS = str_replace("%resources%", $resourceUrl, $moduleCSS);
			
			// Export Module CSS
			BootLoader::exportCSS("Modules", "Modules", $moduleID, $moduleCSS);
			if (!empty($moduleCSS))
				$mParser->attr($mRoot, "css", "1");
			
			// Export Module JS
			$moduleJS = jsParser::format($moduleJS);
			BootLoader::exportJS("Modules", "Modules", $moduleID, $moduleJS);
			if (!empty($moduleJS))
				$mParser->attr($mRoot, "js", "1");
			
			// Write index file
			fileManager::create($modulePath."/index.xml", "", TRUE);
			$mParser->save($modulePath."/index.xml");
		}
		
		return TRUE;
	}
}
//#section_end#
?>