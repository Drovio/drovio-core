<?php
//#section#[header]
// Namespace
namespace API\Developer\components;

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
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "components::units::modules::module");
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\components\units\modules\module;
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
 * @revised	October 25, 2013, 10:33 (EEST)
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
	 * Exports all modules to latest.
	 * 
	 * @return	void
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