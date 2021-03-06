<?php
//#section#[header]
// Namespace
namespace UI\Forms\formReport;

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
 * @namespace	\formReport
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "literals::literal");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "treeView");
importer::import("UI", "Presentation", "notification");
importer::import("UI", "Forms", "formReport::formNotification");

use \API\Resources\literals\literal;
use \UI\Html\DOM;
use \UI\Navigation\treeView;
use \UI\Presentation\notification;
use \UI\Forms\formReport\formNotification;

/**
 * Form Error Notification
 * 
 * Builds an error notification and inserts messages.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 14:16 (EET)
 * @revised	June 28, 2014, 21:50 (EEST)
 */
class formErrorNotification extends formNotification
{
	/**
	 * The error treeView that contains the errors.
	 * 
	 * @type	treeView
	 */
	private $treeView;
	/**
	 * The treeView's outer list
	 * 
	 * @type	DOMElement
	 */
	private $errorList;
	
	/**
	 * Builds the error notification
	 * 
	 * @return	formErrorNotification
	 * 		{description}
	 */
	public function build()
	{
		// Build Notification
		parent::build($type = parent::ERROR, $header = TRUE, $timeout = FALSE, $disposable = TRUE);
		
		// Create Header Title
		$errorMessage = $this->getMessage("error", "err.invalid_data");
		parent::append($errorMessage);
		
		// Build Error Container
		$errorContainer = DOM::create("div", "", "", "errorNotifications");
		parent::append($errorContainer);
		
		// Build Error List
		$this->treeView = new treeView();
		$this->errorList = $this->treeView->get_view($id = "", $class = "errors");
		DOM::append($errorContainer, $this->errorList);
		
		return $this;
	}
	
	/**
	 * Inserts an error header to the error treeView.
	 * 
	 * @param	string	$id
	 * 		The header's id.
	 * 
	 * @param	mixed	$header
	 * 		The error header (string or DOMElement)
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function addErrorHeader($id, $header)
	{
		// Normalize error header
		$errHeader = $header;
		if (gettype($header) == "string")
			$errHeader = DOM::create('span', $header);
		
		// Insert list item
		return $this->treeView->insert_expandableTreeItem($this->errorList, $id, $errHeader);
	}
	
	/**
	 * Inserts an error description to the given error header.
	 * 
	 * @param	DOMElement	$container
	 * 		The error header
	 * 
	 * @param	string	$id
	 * 		The description's id
	 * 
	 * @param	mixed	$description
	 * 		The error description (string or DOMElement)
	 * 
	 * @param	string	$extra
	 * 		Extra description content.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function addErrorDescription($container, $id, $description, $extra = "")
	{
		// Build Error Description Holder
		$errHolder = DOM::create("div");
		
		// Set description
		$errDescription = $description;
		if (gettype($description) == "string")
			$errDescription = DOM::create('span', $description);
		DOM::append($errHolder, $errDescription);
		
		// Create Extra Span
		$span = DOM::create('span', $extra);
		DOM::append($errHolder, $span);
		
		// Append List Item
		return $this->treeView->insert_treeItem($container, $id, $errHolder);
	}
	
	/**
	 * Creates a span with an error message for the report.
	 * 
	 * @param	string	$messageID
	 * 		The error message id.
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	public function getErrorMessage($messageID)
	{
		return literal::get("global::forms::validator::error", $messageID, TRUE);
	}
	
	/**
	 * Returns a form error notification report.
	 * It prevents the reset action of the form.
	 * 
	 * @return	string
	 * 		{description}
	 */
	public function getReport()
	{
		return parent::getReport(FALSE);
	}
}
//#section_end#
?>