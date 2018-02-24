<?php
//#section#[header]
// Namespace
namespace UI\Presentation\frames;

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
 * @package	Presentation
 * @namespace	\frames
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "html::PopupPrototype");
importer::import("ESS", "Prototype", "html::WindowFramePrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\html\PopupPrototype;
use \ESS\Prototype\html\WindowFramePrototype;
use \UI\Html\DOM;

/**
 * Simple Window Frame
 * 
 * Creates a window frame popup to display content to the user.
 * 
 * @version	0.1-1
 * @created	May 15, 2013, 13:49 (EEST)
 * @revised	July 17, 2014, 16:59 (EEST)
 */
class windowFrame extends WindowFramePrototype
{
	/**
	 * The PopupPrototype object for setting the behavior.
	 * 
	 * @type	PopupPrototype
	 */
	protected $pp;
	
	/**
	 * Initializes the object and its variables.
	 * 
	 * @param	string	$id
	 * 		The frame's id.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		$this->pp = new PopupPrototype();
		parent::__construct($id);
	}
	
	/**
	 * Builds the window frame as a prototype and builds the popup as well.
	 * 
	 * @param	string	$title
	 * 		The frame's title.
	 * 
	 * @param	string	$class
	 * 		The windowFrame's class.
	 * 
	 * @return	windowFrame
	 * 		The windowFrame object.
	 */
	public function build($title = "Window Frame", $class = "windowFrame")
	{
		// Build frame
		parent::build($title, $class)->get();
		
		// Build popup
		$this->pp->type("persistent");
		
		return $this;
	}
	
	/**
	 * Returns the server report containing the frame popup.
	 * 
	 * @return	string
	 * 		The server's frame report.
	 */
	public function getFrame()
	{
		// Create frame popup and set the object
		$frame = $this->pp->build($this->get())->get();
		$this->set($frame);
		
		// Return frame report
		return parent::getFrame();
	}
}
//#section_end#
?>