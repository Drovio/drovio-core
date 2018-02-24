<?php
//#section#[header]
// Namespace
namespace UI\Forms\formControls;

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
 * @namespace	\formControls
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("UI", "Forms", "formControls/formItem");
importer::import("UI", "Html", "DOM");

use \UI\Forms\formControls\formItem;
use \UI\Html\DOM;

/**
 * Form Label
 * 
 * Builds a universal form label
 * 
 * @version	0.1-2
 * @created	March 11, 2013, 15:26 (EET)
 * @updated	May 20, 2015, 15:51 (EEST)
 */
class formLabel extends formItem
{
	/**
	 * Builds the form label
	 * 
	 * @param	mixed	$context
	 * 		The label's context.
	 * 		It  can be string or DOMElement.
	 * 		It is NULL by default.
	 * 
	 * @param	string	$for
	 * 		The element's id that the label is pointing to.
	 * 		The "for" attribute.
	 * 
	 * @return	formLabel
	 * 		The formLabel object.
	 */
	public function build($context = NULL, $for = "")
	{
		// Create label
		parent::build("label", "", "", "", "uiFormLabel", $context);
		
		// Populate label attributes and children
		$label = $this->get();
		DOM::attr($label, "for", $for);
		
		return $this;
	}
}
//#section_end#
?>