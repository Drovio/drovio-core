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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Popup Prototype
 * 
 * It's the prototype for building any kind of popup.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 12:05 (EET)
 * @revised	April 24, 2014, 11:06 (EEST)
 */
class PopupPrototype extends UIObjectPrototype
{
	/**
	 * "on", "one"
	 * 
	 * @type	string
	 */
	protected $binding = "on";
	/**
	 * "obedient", "persistent", "toggle"
	 * 
	 * @type	string
	 */
	protected $type = "obedient";
	/**
	 * Defines whether the popup will be dismissed after 3 seconds.
	 * 
	 * @type	boolean
	 */
	protected $timeout = FALSE;
	/**
	 * Defines whether the popup will be in a white background.
	 * 
	 * @type	boolean
	 */
	protected $background = FALSE;
	/**
	 * Defines whether the popup will have fade transition for in and out.
	 * 
	 * @type	boolean
	 */
	protected $fade = FALSE;
	
	/**
	 * Defines the position of the popup relative to the window|parent|sender.
	 * 
	 * @type	string
	 */
	protected $position = "user";
	/**
	 * In case of positioning relative to the sender, the offset of the popup in distance from the sender in pixels.
	 * 
	 * @type	integer
	 */
	protected $distanceOffset = 0;
	/**
	 * In case of positioning relative to the sender, the offset of the popup in alignment from the sender in pixels.
	 * 
	 * @type	integer
	 */
	protected $alignOffset = 0;
	
	/**
	 * Defines the docking of the popup.
	 * 
	 * @type	string
	 */
	protected $invertDock = "none";
	
	/**
	 * Popup id
	 * 
	 * @type	string
	 */
	protected $popupId = "";
	
	/**
	 * Parent's id
	 * 
	 * @type	string
	 */
	protected $parent = "";
	
	/**
	 * Builds the popup according to settings given.
	 * 
	 * @param	DOMElement	$content
	 * 		The content of the popup
	 * 
	 * @return	PopupPrototype
	 * 		Returns 'this' object
	 */
	public function build($content = NULL)
	{
		// Create the popup holder
		$holder = DOM::create('div', '', '', 'uiPopup');
		$this->set($holder);
		
		// Create the instructions holder
		$info = DOM::create('div', '', '', 'info init');
		DOM::append($holder, $info);
		
		//_____ Popup Attributes
		$settings = array();
		$settings['binding'] = $this->binding;
		$settings['type'] = $this->type;
		$settings['timeout'] = ($this->timeout ? TRUE : FALSE);
		$settings['background'] = ($this->background ? TRUE : FALSE);
		$settings['fade'] = ($this->fade ? TRUE : FALSE);
		DOM::data($info, "popup-settings", $settings);
		
		$extra = array();
		$extra['id'] = $this->popupId;
		$extra['parentid'] = $this->parent;
		$extra['position'] = $this->position;
		$extra['distanceOffset'] = $this->distanceOffset;
		$extra['alignOffset'] = $this->alignOffset;
		$extra['invertDock'] = $this->invertDock;
		DOM::data($info, "popup-extra", $extra);
		
		// Create the popup content holder
		$innerContent = DOM::create('div', '', '', 'popupContent');
		DOM::append($holder, $innerContent);
		
		// Append Popup Content
		if (!is_null($content))
			DOM::append($innerContent, $content);
		
		return $this;
	}
	
	/**
	 * Gets or defines the binding property
	 * 
	 * @param	string	$binding
	 * 		The binding value. Can be either "on" or "one"
	 * 
	 * @return	mixed
	 * 		The binding property, or 'this'
	 */
	public function binding($binding = "")
	{
		// Return value
		if (empty($binding))
			return $this->binding;
			
		// Set value
		$this->binding = $binding;
		return $this;
	}
	
	/**
	 * Gets or defines the popup's parent id
	 * 
	 * @param	string	$id
	 * 		The parent's id
	 * 
	 * @return	mixed
	 * 		The popup's parent id, or this
	 */
	public function parent($id = "")
	{
		// Return value
		if (empty($id))
			return $this->parent;
			
		// Set value
		$this->parent = $id;
		return $this;
	}
	
	/**
	 * Gets or defines the type property
	 * 
	 * @param	string	$type
	 * 		The type value. Either persistent or obedient combined with toggle.
	 * 
	 * @param	boolean	$toggle
	 * 		Toggle functionality for popup.
	 * 
	 * @return	mixed
	 * 		The type property, or 'this'
	 */
	public function type($type = "", $toggle = FALSE)
	{
		// Return value
		if (empty($type))
			return $this->type;
			
		// Set balue
		$this->type = $type.($toggle === TRUE ? " toggle" : "" );
		return $this;
	}
	
	/**
	 * Gets or defines the timeout property
	 * 
	 * @param	boolean	$timeout
	 * 		The timeout value
	 * 
	 * @return	mixed
	 * 		The timeout property, or 'this'
	 */
	public function timeout($timeout = NULL)
	{
		// Return value
		if (empty($timeout))
			return $this->timeout;
			
		// Set balue
		$this->timeout = $timeout;
		return $this;
	}
	
	/**
	 * Gets or defines the background property
	 * 
	 * @param	boolean	$background
	 * 		The background value
	 * 
	 * @return	mixed
	 * 		The background property, or 'this'
	 */
	public function background($background = NULL)
	{
		// Return value
		if (is_null($background))
			return $this->background;
			
		// Set balue
		$this->background = $background;
		return $this;
	}
	
	/**
	 * Gets or defines the fade property
	 * 
	 * @param	boolean	$fade
	 * 		The fade value
	 * 
	 * @return	mixed
	 * 		The fade property, or 'this'
	 */
	public function fade($fade = NULL)
	{
		// Return value
		if (is_null($fade))
			return $this->fade;
			
		// Set balue
		$this->fade = $fade;
		return $this;
	}
	
	/**
	 * Gets or defines the position property.
	 * If both $position and $alignment are set, those are used accordingly to position the popup relatively with the sender.
	 * Position can be:
	 * [top | bottom | left | right]
	 * and alignment can be:
	 * [top | bottom | left | right | center (wherever this makes sense)].
	 * 
	 * If only $position is set as a string, that is used to position the popup in relation with the window. Here position can be:
	 * [top | bottom | left | right | center | user].
	 * or a number between 1 and 9 [inclusive] that maps the numeric keyboard numbers to places on the screen.
	 * 
	 * Finally, if only the position is set as an array, this is used to position the popup in relation with the window ['fixed'] or the parent ['absolute']. That array can have the following keys:
	 * [top | bottom | left | right | position]
	 * 
	 * @param	string	$position
	 * 		The position value
	 * 
	 * @param	string	$alignment
	 * 		The alignment value
	 * 
	 * @return	mixed
	 * 		The position value, or 'this'
	 */
	public function position($position = "", $alignment = "")
	{
		// Return value
		if (empty($position))
			return $this->position;
		
		// Set value
		if (!empty($alignment))
		{
			$this->position = $position."|".$alignment;
			return $this;
		}
		
		if (!is_array($position))
		{
			$this->position = $position;
			return $this;
		}
		
		$info = array_intersect_key($position, array('top'=>'', 'bottom'=>'', 'left'=>'', 'right'=>'', 'position'=>''));
		
		$this->position = $info;
		return $this;
	}
	
	/**
	 * Gets or defines the distance offset property
	 * 
	 * @param	integer	$offset
	 * 		The distance offset value
	 * 
	 * @return	mixed
	 * 		The distance from sender, or 'this'
	 */
	public function distanceOffset($offset = 0)
	{
		// Return value
		if (empty($offset))
			return $this->distanceOffset;
			
		// Set value
		$this->distanceOffset = $offset;
		return $this;
	}
	
	/**
	 * Gets or defines the alignment offset property
	 * 
	 * @param	integer	$offset
	 * 		The align offset value
	 * 
	 * @return	mixed
	 * 		The alignment with sender, or 'this'
	 */
	public function alignOffset($offset = 0)
	{
		// Return value
		if (empty($offset))
			return $this->alignOffset;
			
		// Set value
		$this->alignOffset = $offset;
		return $this;
	}
	
	/**
	 * Gets or defines the invertDock property.
	 * 
	 * @param	string	$orientation
	 * 		The orientation to invert docking. Default is "none". Available invertions are "horizontal", "vertical", and "both".
	 * 
	 * @return	mixed
	 * 		The inverDock property, or 'this'
	 */
	public function invertDock($orientation = "")
	{
		// Return value
		if (empty($orientation))
			return $this->invertDock;
			
		// Set value
		$this->invertDock = $orientation;
		return $this;
	}
}
//#section_end#
?>