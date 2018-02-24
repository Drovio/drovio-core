<?php
//#section#[header]
// Namespace
namespace API\Model\core;

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
 * @package	Model
 * @namespace	\core
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Literals", "literal");
importer::import("API", "Model", "core/resource");
importer::import("API", "Resources", "DOMParser");

use \API\Literals\literal;
use \API\Model\core\resource;
use \API\Resources\DOMParser;

/**
 * Core manifest manager
 * 
 * Reads all the core manifests from the production.
 * 
 * @version	0.1-5
 * @created	May 7, 2015, 11:33 (EEST)
 * @updated	July 3, 2015, 14:35 (EEST)
 */
class manifests
{
	/**
	 * Get all enabled core manifests along with their protected packages.
	 * It includes private manifests.
	 * 
	 * @return	array
	 * 		An array of all manifests by manifest id.
	 * 		Includes name, title, description and packages by library.
	 */
	public static function getManifests()
	{
		// Initialize core manifests
		$coreManifests = array();
		
		// Load core manifests
		$parser = new DOMParser();
		try
		{
			$manifestPath = resource::getPath("/resources/manifests.xml", $rootRelative = TRUE);
			$parser->load($manifestPath);
		}
		catch (Exception $ex)
		{
			return;
		}
		
		// Get manifests
		$manifests = $parser->evaluate("//manifest[@enabled='1']");
		foreach ($manifests as $manifest)
		{
			// Get manifest id
			$mfID = $parser->attr($manifest, "id");
			
			// Get manifest information
			$manifestInfo = array();
			$manifestInfo['info']['id'] = $mfID;
			$manifestInfo['info']['name'] = $parser->attr($manifest, "name");
			$manifestInfo['info']['enabled'] = $parser->attr($manifest, "enabled");
			$manifestInfo['info']['private'] = $parser->attr($manifest, "private");
			// Plans permission
			$manifestInfo['info']['premium'] = $parser->attr($manifest, "premium");
			$manifestInfo['info']['enterprise'] = $parser->attr($manifest, "enterprise");
			
			// Get manifest title and description
			$manifestInfo['info']['title'] = literal::get("sdk.manifest", "mf_".$manifestInfo['info']['name']."_title", array(), FALSE);
			$manifestInfo['info']['description'] = literal::get("sdk.manifest", "mf_".$manifestInfo['info']['name']."_desc", array(), FALSE);
			
			// Get manifest packages
			$packages = $parser->evaluate("plist/library/package", $manifest);
			$packageList = array();
			foreach ($packages as $package)
			{
				// Set names
				$libraryName = $parser->attr($package->parentNode, "name");
				$packageName = $parser->attr($package, "name");
				
				// Append to array
				$manifestInfo['packages'][$libraryName][] = $packageName;
			}
			
			// Add to list
			$coreManifests[$mfID] = $manifestInfo;
		}

		// Return manifest list
		return $coreManifests;
	}
}
//#section_end#
?>