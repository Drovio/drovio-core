<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "resources");
importer::import("API", "Developer", "content::document::parsers::phpParser");
importer::import("INU", "Developer", "codeEditor");
importer::import("INU", "Developer", "documentation::classDocumentor");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "gridSplitter");
importer::import("UI", "Presentation", "expander");

use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\DOMParser;
use \API\Resources\resources;
use \API\Developer\content\document\parsers\phpParser;
use \INU\Developer\codeEditor;
use \INU\Developer\documentation\classDocumentor;
use \UI\Html\DOM;
use \UI\Presentation\gridSplitter;
use \UI\Presentation\expander;
/**
 * Documentor
 * 
 * Handles the documentation process of the classes.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 14:53 (EEST)
 * @revised	June 28, 2013, 10:43 (EEST)
 * 
 * @deprecated	Use \INU\Developer\documentation\classDocumentor instead
 */
class documentor extends classDocumentor {}
//#section_end#
?>