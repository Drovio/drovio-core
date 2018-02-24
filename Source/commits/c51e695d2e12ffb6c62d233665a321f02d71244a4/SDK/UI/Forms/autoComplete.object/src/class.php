<?php
//#section#[header]
// Namespace
namespace UI\Forms;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Interactive", "forms::formAutoComplete");
importer::import("UI", "Html", "DOM");

use \UI\Interactive\forms\formAutoComplete;
use \UI\Html\DOM;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	empty}
 * @created	March 11, 2013, 11:11 (EET)
 * @revised	March 11, 2013, 11:11 (EET)
 * 
 * @deprecated	Use \UI\Interactive\forms\formAutoComplete instead.
 */
class autoComplete extends formAutoComplete
{
	public function add_ac_elements($elem, $fill_elem = array(), $hide_elem = array(), $populate_elem = array(), $type = "strict")
	{
		return parent::engage($elem, $fill_elem, $hide_elem, $populate_elem, $type);
	}

	public function add_ac_path($elem, $path)
	{
		DOM::attr($elem, 'data-ajax-ac', $path );
		
		return $elem;
	}
}
//#section_end#
?>