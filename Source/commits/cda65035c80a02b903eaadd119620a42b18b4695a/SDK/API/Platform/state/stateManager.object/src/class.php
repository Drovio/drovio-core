<?php
//#section#[header]
// Namespace
namespace API\Platform\state;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Platform
 * @namespace	\state
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	December 16, 2013, 14:03 (EET)
 * @revised	December 16, 2013, 14:03 (EET)
 * 
 * @deprecated	Use \ESS\Protocol\client\environment\State instead.
 */
class stateManager
{
	/**
	 * {description}
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Put your constructor method code here.
	}
}
//#section_end#
?>