<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder\extComponents;

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
 * @namespace	\ebuilder\extComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "ebuilder::extComponents::extSrcObject");

use \API\Developer\ebuilder\extComponents\extSrcObject;

/**
 * appSrcObjec
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	October 9, 2013, 19:44 (EEST)
 * @revised	January 14, 2014, 21:24 (EET)
 * 
 * @deprecated	Not In Use
 */
class appSrcObject extends extSrcObject
{ 
	/**
	 * {description}
	 * 
	 * @param	{type}	$importsArray
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function buildImports($importsArray = array())
	{
		// Build Imports
		$imports = "\n\n";
		$imports .= phpParser::get_comment("Import Packages", $multi = FALSE)."\n";
		$startups = "\n";
		foreach ($this->imports as $lib => $packages)
			foreach ($packages as $pkg)
			{
				$imports .= 'echo "'.$lib.'", "'.$pkg.'";'."\n";
				//$imports .= 'importer::import("'.$lib.'", "'.$pkg.'");'."\n";
				//$startups .= 'BootLoader::addStartupPackage("'.$lib.'", "'.$pkg.'");'."\n";
			}
		
		// Merge and Clear
		$imports .= $startups;
		return preg_replace('/\n$/', '', $imports);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$parser
	 * 		{description}
	 * 
	 * @param	{type}	$base
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function loadImports($parser, $base)
	{
		$this->imports = array();
		$importsBase = $parser->evaluate("imports", $base)->item(0);
		
		if (is_null($importsBase))
			return;
			
		$importObject = $parser->evaluate("package", $importsBase);
		foreach ($importObject as $object)
			$this->imports[$parser->attr($object, "lib")][] = $parser->attr($object, "name");
	}
	
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	private function updateIndexInfo()
	{
		// Get Index Base
		$builder = new DOMParser();
		$newBase = $this->buildIndexBase($builder);
		
		// Update Trunk Base
		$this->vcsTrunk->updateBase($this->getWorkingBranch(), $newBase);
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @return	void
	 */
	protected function buildIndexBase($builder)
	{
		// Get Index Base
		$newBase = $builder->create("item", "", $this->getFileName());
		
		// Update Description
		//$code_description = $builder->create("description", $this->description);
		//$builder->append($newBase, $code_description);
		
		// Update Imports
		$imports = $this->buildImportsIndex($builder);
		$builder->append($newBase, $imports);
		
		// Update Dimensions
		$dimensions = $this->buildDimensionIndex($builder);
		$builder->append($newBase, $dimensions);
		
		return $newBase;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$builder
	 * 		{description}
	 * 
	 * @return	void
	 */
	protected function buildImportsIndex($builder)
	{
		// Create import div
		$base = $builder->create("imports");
		
		// Set imports
		foreach ($this->imports as $lib => $packages)
		{
			foreach ($packages as $pkg)
			{
				// Build item
				$object = $builder->create("package");
				$builder->attr($object, "lib", $lib);
				$builder->attr($object, "name", $pkg);
				$builder->append($base, $object);
			}
		}
		
		return $base;
	}
}
//#section_end#
?>