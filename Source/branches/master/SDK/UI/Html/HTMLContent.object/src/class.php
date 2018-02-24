<?php
//#section#[header]
// Namespace
namespace UI\Html;

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
 * @package	Html
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("UI", "Content", "HTMLContent");

use \UI\Content\HTMLContent;

/**
 * HTML Content Object
 * 
 * Creates an html content object for the modules.
 * 
 * @version	2.0-1
 * @created	April 5, 2013, 12:41 (EEST)
 * @updated	May 20, 2015, 13:19 (EEST)
 * 
 * @deprecated	Use \UI\Content\HTMLContent instead.
 */
class HTMLContent extends HTMLContent {}
//#section_end#
?>