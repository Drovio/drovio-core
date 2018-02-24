<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\html;

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
 * @package	Prototype
 * @namespace	\html
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("UI", "Prototype", "FormPrototype");

use \UI\Prototype\FormPrototype as newFormPrototype;

/**
 * Form Builder Prototype
 * 
 * It's the prototype for building every form in the system.
 * All the form objects must inherit this FormPrototype in order to build the spine of a form well-formed.
 * It implements the FormProtocol.
 * 
 * @version	2.0-1
 * @created	March 7, 2013, 12:05 (EET)
 * @updated	July 28, 2015, 12:53 (EEST)
 * 
 * @deprecated	Use \UI\Prototype\FormPrototype instead.
 */
class FormPrototype extends newFormPrototype {}
//#section_end#
?>