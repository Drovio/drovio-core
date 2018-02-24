<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\loaders;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\loaders
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

// Engine Start
importer::import("API", "Platform", "engine");
use \API\Platform\engine;
engine::start();

importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Content", "analytics::collectors::pageLoads");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "pages::page");
importer::import("UI", "Core", "RCPage");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\environment\Url;
use \ESS\Protocol\loaders\ModuleLoader;
use \API\Comm\database\connections\interDbConnection;
use \API\Content\analytics\collectors\pageLoads;
use \API\Model\units\sql\dbQuery;
use \API\Resources\pages\page;
use \UI\Core\RCPage;
use \UI\Html\DOM;

/**
 * Page Loader
 * 
 * Loads a system's page according to page settings and parameters. It attaches the module receptor.
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 11:42 (EET)
 * @revised	September 13, 2014, 10:58 (EEST)
 */
class PageLoader
{
	/**
	 * Loads a page with the defined module
	 * 
	 * @param	int	$pageID
	 * 		The page's id from the database.
	 * 
	 * @return	void
	 */
	public static function load($pageID)
	{
		// Get page info
		$pageInfo = page::info($pageID);
		if (empty($pageInfo))
		{
			// Build Not-Found HTML Page
			$htmlPage = RCPage::getInstance(NULL);
			echo $htmlPage->build("", TRUE)->get();
			return;
		}

		// Check for subdomain and redirect if necessary
		$status = url::checkSubdomain($pageInfo['domain_description'], $pageInfo['domain_path']);
		if (!$status)
			return;
		
		// Check if there attributes for data only
		if ($pageInfo['attributes']['data'])
		{
			DOM::initialize();
			echo ModuleLoader::load($pageInfo['module_id']);
			return;
		}
		
		// Build HTML Page
		$startTime = microtime(TRUE);
		$htmlPage = RCPage::getInstance($pageInfo['module_id'], $pageInfo['attributes']);
		$htmlPage->build($pageInfo['domain_description'], $pageInfo['attributes']['dynamic']);
		$endTime = microtime(TRUE);
		
		// Calculate and log paage analytics data			
		$rbData = array();
		$rbData['moduleID'] = $pageInfo['module_id'];
		$rbData['static'] = $pageInfo['static'];
		$rbData['dDesc'] = $pageInfo['domain_description'];
		$rbData['dPath'] = $pageInfo['domain_path'];
		$rbData['execTime'] = $startTime - $endTime;		
		pageLoads::log($rbData);
		
		echo $htmlPage->get();
	}
}
//#section_end#
?>