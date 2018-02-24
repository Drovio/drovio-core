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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Literals", "literal");
importer::import("UI", "Forms", "formReport/formNotification");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Navigation", "treeView");
importer::import("UI", "Presentation", "notification");

use \API\Literals\literal;
use \UI\Forms\formReport\formNotification;
use \UI\Html\DOM;
use \UI\Navigation\treeView;
use \UI\Presentation\notification;

/**
 * Form Error Notification
 * 
 * Builds an error notification and inserts messages.
 * 
 * @version	2.0-3
 * @created	March 11, 2013, 14:16 (EET)
 * @updated	May 19, 2015, 22:31 (EEST)
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
	 * 		The formErrorNotification object.
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
		$this->errorList = $this->treeView->build($id = "", $class = "errors")->get();
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
	 * 		The error header (string or DOMElement).
	 * 
	 * @return	DOMElement
	 * 		The header DOMElement object.
	 * 
	 * @deprecated	Use addHeader() instead.
	 */
	public function addErrorHeader($id, $header)
	{
		$this->addHeader($header, $id);
	}
	
	/**
	 * Inserts an error header to the error treeView.
	 * 
	 * @param	mixed	$header
	 * 		The error header (string or DOMElement).
	 * 
	 * @param	string	$id
	 * 		The header's id.
	 * 		Leave empty for random (if you don't care).
	 * 		It is empty by default.
	 * 
	 * @return	DOMElement
	 * 		The header DOMElement object.
	 */
	public function addHeader($header, $id = "")
	{
		// Set id (if empty)
		$id = (empty($id) ? "errh".mt_rand() : $id);
		
		// Insert list item
		return $this->treeView->insertExpandableTreeItem($id, $header);
	}
	
	/**
	 * Inserts an error description to the given error header.
	 * 
	 * @param	DOMElement	$container
	 * 		The error header element.
	 * 
	 * @param	string	$id
	 * 		The description's id.
	 * 
	 * @param	mixed	$description
	 * 		The error description (string or DOMElement).
	 * 
	 * @param	string	$extra
	 * 		Extra description content.
	 * 
	 * @return	DOMElement
	 * 		The description DOMElement object.
	 * 
	 * @deprecated	Use addDescription() instead.
	 */
	public function addErrorDescription($container, $id, $description, $extra = "")
	{
		$this->addDescription($container, $description, $extra, $id);
	}
	
	/**
	 * Inserts an error description to the given error header.
	 * 
	 * @param	DOMElement	$container
	 * 		The error header element.
	 * 
	 * @param	mixed	$description
	 * 		The error description (string or DOMElement).
	 * 
	 * @param	string	$extra
	 * 		Extra description content.
	 * 
	 * @param	string	$id
	 * 		The description's id.
	 * 		Leave empty for random (if you don't care).
	 * 		It is empty by default.
	 * 
	 * @return	DOMElement
	 * 		The description DOMElement object.
	 */
	public function addDescription($container, $description, $extra = "", $id = "")
	{
		// Build Error Description Holder
		$errHolder = DOM::create("div", $description, "", "errd");
		
		// Create Extra Span
		if (!empty($extra))
		{
			$span = DOM::create('span', $extra);
			DOM::append($errHolder, $span);
		}
		
		// Append List Item
		$id = (empty($id) ? "errdsc".mt_rand() : $id);
		$containerID = DOM::attr($container, "id");
		return $this->treeView->insertTreeItem($id, $errHolder, $containerID);
	}
	
	/**
	 * Creates a span with an error message for the report.
	 * 
	 * @param	string	$messageID
	 * 		The error message id.
	 * 
	 * @return	DOMElement
	 * 		The message DOMElement object.
	 */
	public function getErrorMessage($messageID)
	{
		return literal::get("global.forms.validator.error", $messageID);
	}
	
	/**
	 * Builds a specific report with the form vaidation error message embedded.
	 * 
	 * @return	string
	 * 		Returns the report string.
	 */
	public function getValidateErrorReport()
	{
		// Add form post error header
		$err_header = DOM::create("div", $this->getErrorMessage("err.form_invalidate_title"));
		$err = $this->addHeader($err_header);
		$this->addDescription($err, $this->getErrorMessage("err.form_invalidate"));
		
		// Return report
		return $this->getReport();
	}
	
	/**
	 * Get the form error notification report.
	 * It prevents the reset action of the form.
	 * 
	 * @return	string
	 * 		The server report output.
	 */
	public function getReport()
	{
		return parent::getReport(FALSE);
	}
}
//#section_end#
?>