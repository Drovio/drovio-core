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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

// Engine Start
importer::import("API", "Platform", "engine");
use \API\Platform\engine;
engine::start();

importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("UI", "Html", "HTMLPage");
importer::import("API", "Content", "analytics::collectors::pageLoads");

use \API\Content\analytics\collectors\pageLoads;
use \ESS\Protocol\client\environment\Url;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;
use \UI\Html\HTMLPage;

/**
 * Page Loader
 * 
 * Loads a system's page according to page settings and parameters. It attaches the module receptor.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 9:42 (UTC)
 * @revised	March 7, 2013, 9:42 (UTC)
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
		// Get Page Module Id
		$dbc = new interDbConnection();
		$dbq = new dbQuery("739807288", "units.domains.pages");
		
		$attr = array();
		$attr['id'] = $pageID;
		$result = $dbc->execute($dbq, $attr);

		if ($dbc->get_num_rows($result) == 0)
		{
			// Build Not-Found HTML Page
			$htmlPage = new HTMLPage(NULL);
			echo $htmlPage->build("", TRUE)->get();
			return;
		}
		
		// Fetch Page
		$page = $dbc->fetch($result);

		// Check for subdomain and redirect if necessairy
		$status = url::checkSubdomain($page['domain_description'], $page['domain_path']);
		if (!$status)
			return;
		
		// Build HTML Page
		$startTime = microtime(TRUE);
		$htmlPage = new HTMLPage($page['module_id']);
		$htmlPage->build($page['domain_description'], $page['static']);
		$endTime = microtime(TRUE);
		
		// Calculate and log paage analytics data			
		$rbData = array();
		$rbData['moduleID'] = $page['module_id'];
		$rbData['static'] = $page['static'];
		$rbData['dDesc'] = $page['domain_description'];
		$rbData['dPath'] = $page['domain_path'];
		$rbData['execTime'] = $startTime - $endTime;		
		pageLoads::log($rbData);
		
		echo $htmlPage->get();
	}
}
//#section_end#
?>