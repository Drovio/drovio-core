<?php
//#section#[header]
// Namespace
namespace UI\Forms;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Forms", "Form");

use \UI\Forms\Form;

/**
 * Form Item Factory
 * 
 * Builds a form and provides a "factory" for building all the necessary form items.
 * It implements the FormProtocol.
 * 
 * @version	2.0-1
 * @created	April 18, 2013, 11:06 (EEST)
 * @revised	November 14, 2014, 16:50 (EET)
 * 
 * @deprecated	Use \UI\Forms\Form instead.
 */
class formFactory extends Form {}
//#section_end#
?>