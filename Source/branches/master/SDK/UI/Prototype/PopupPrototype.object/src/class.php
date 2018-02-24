<?php
//#section#[header]
// Namespace
namespace UI\Prototype;

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
 * @package	Prototype
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Html", "DOM");
importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Html\DOM;
use \UI\Prototype\UIObjectPrototype;

/**
 * Popup Prototype
 * 
 * This is the prototype for building any kind of popup.
 * 
 * @version	0.1-2
 * @created	July 28, 2015, 10:27 (BST)
 * @updated	October 24, 2015, 15:25 (BST)
 */
class PopupPrototype extends UIObjectPrototype
{
	/**
	 * Obedient type popup value.
	 * 
	 * @type	string
	 */
	const TP_OBEDIENT = "obedient";
	/**
	 * Persistent type popup value.
	 * 
	 * @type	string
	 */
	const TP_PERSISTENT = "persistent";
	/**
	 * Toggle type popup value.
	 * 
	 * @type	string
	 */
	const TP_TOGGLE = "toggle";
	
	/**
	 * The horizontal orientation value.
	 * 
	 * @type	string
	 */
	const OR_HORIZONTAL = "horizontal";
	/**
	 * The vertical orientation value.
	 * 
	 * @type	string
	 */
	const OR_VERTIVAL = "vertical";
	/**
	 * The both vertical and horizontal orientation value.
	 * 
	 * @type	string
	 */
	const OR_BOTH = "both";
	
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
	 * The settings must be defined before the build function.
	 * 
	 * @param	DOMElement	$content
	 * 		The content of the popup.
	 * 
	 * @return	PopupPrototype
	 * 		The PopupPrototype object.
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
	 * Gets or defines the binding property.
	 * 
	 * @param	string	$binding
	 * 		The binding value.
	 * 		Can be either "on" or "one", like jQuery listeners.
	 * 		"On" listens all the time.
	 * 		"One" listens only the first time.
	 * 
	 * @return	mixed
	 * 		The binding property, or the PopupPrototype object.
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
	 * Gets or defines the popup's parent id.
	 * 
	 * @param	string	$id
	 * 		The parent's id.
	 * 
	 * @return	mixed
	 * 		The popup's parent id, or the PopupPrototype object.
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
	 * Gets or defines the type property.
	 * 
	 * @param	string	$type
	 * 		The type value.
	 * 		You can use TP_OBEDIENT or TP_PERSISTENT.
	 * 
	 * @param	boolean	$toggle
	 * 		Toggle functionality for popup.
	 * 		This defines that the popup will be showed and hide by the same listener.
	 * 
	 * @return	mixed
	 * 		The type property, or the PopupPrototype object.
	 */
	public function type($type = "", $toggle = FALSE)
	{
		// Return value
		if (empty($type))
			return $this->type;
			
		// Set balue
		$this->type = $type.($toggle === TRUE ? " ".self::PP_TOGGLE : "" );
		return $this;
	}
	
	/**
	 * Gets or defines the timeout property.
	 * 
	 * @param	boolean	$timeout
	 * 		True to set timeout, false otherwise.
	 * 
	 * @return	mixed
	 * 		The timeout property, or the PopupPrototype object.
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
	 * Gets or defines the background property.
	 * 
	 * @param	boolean	$background
	 * 		True to set background overlay, false otherwise.
	 * 
	 * @return	mixed
	 * 		The background property, or the PopupPrototype object.
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
	 * Gets or defines the fade property.
	 * 
	 * @param	boolean	$fade
	 * 		True to set fade animation in and out, false otherwise.
	 * 
	 * @return	mixed
	 * 		The fade property, or the PopupPrototype object.
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
	 * 
	 * @param	string	$position
	 * 		Position can be:
	 * 		[top | bottom | left | right].
	 * 		
	 * 		If only $position is set as a string, that is used to position the popup in relation with the window. Here position can be:
	 * 		[top | bottom | left | right | center | user].
	 * 		or a number between 1 and 9 [inclusive] that maps the numeric keyboard numbers to places on the screen.
	 * 		
	 * 		Finally, if only the position is set as an array, this is used to position the popup in relation with the window ['fixed'] or the parent ['absolute']. That array can have the following keys:
	 * 		[top | bottom | left | right | position]
	 * 
	 * @param	string	$alignment
	 * 		Alignment can be:
	 * 		[top | bottom | left | right | center (wherever this makes sense)].
	 * 
	 * @return	mixed
	 * 		The position value, or the PopupPrototype object.
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
	 * Gets or defines the distance offset property.
	 * 
	 * @param	integer	$offset
	 * 		The distance offset value
	 * 
	 * @return	mixed
	 * 		The distance from sender, or the PopupPrototype object.
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
	 * Gets or defines the alignment offset property.
	 * 
	 * @param	integer	$offset
	 * 		The align offset value
	 * 
	 * @return	mixed
	 * 		The alignment with sender, or the PopupPrototype object.
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
	 * 		The orientation to invert docking.
	 * 		Default is "none".
	 * 		Available invertions are OR_HORIZONTAL, OR_VERTICAL and OR_BOTH.
	 * 
	 * @return	mixed
	 * 		The inverDock property, or the PopupPrototype object.
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