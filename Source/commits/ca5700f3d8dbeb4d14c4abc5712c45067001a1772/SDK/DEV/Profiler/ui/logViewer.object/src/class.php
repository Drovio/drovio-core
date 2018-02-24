<?php
//#section#[header]
// Namespace
namespace DEV\Profiler\ui;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Profiler
 * @namespace	\ui
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Literals", "literal");
importer::import("UI", "Presentation", "togglers::toggler");
importer::import("UI", "Forms", "Form");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Profiler", "logger");

use \ESS\Prototype\UIObjectPrototype;
use \API\Literals\literal;
use \UI\Presentation\togglers\toggler;
use \UI\Forms\Form;
use \UI\Html\DOM;
use \DEV\Profiler\logger;

/**
 * System's log record viewer.
 * 
 * This is a ui element for viewing all the system's logs.
 * 
 * @version	0.1-6
 * @created	September 8, 2014, 16:41 (EEST)
 * @revised	December 10, 2014, 14:20 (EET)
 */
class logViewer extends UIObjectPrototype
{
	/**
	 * The ui control's class for landing the log data.
	 * 
	 * @type	string
	 */
	const POOL = ".loggerPool";
	
	/**
	 * Builds a toggler containing all the logs.
	 * 
	 * @param	string	$id
	 * 		The log viewer element id.
	 * 
	 * @return	logViewer
	 * 		The logViewer element.
	 */
	public function build($id = "logViewer")
	{
		// Build outer container
		$holder = DOM::create("div", "", $id, "logViewer");
		$this->set($holder);
		
		// Build Logger Controls
		$this->buildControls();
		
		// Create logger Data Holder
		$loggerData = DOM::create("div", "", "logData");
		DOM::append($holder, $loggerData);
		
		$loggerPool = DOM::create("div", "", "", "loggerPool");
		DOM::append($holder, $loggerPool);
		
		return $this;
	}
	
	/**
	 * Builds the ui logger controls.
	 * 
	 * @return	void
	 */
	private function buildControls()
	{
		$controlPanel = DOM::create("div", "", "", "controls");
		DOM::append($this->get(), $controlPanel);
		
		// Emergency Logs
		$emergencyBox = $this->getFilterBox("Threats", "threats", TRUE);
		DOM::append($controlPanel, $emergencyBox);
		
		// Emergency Logs
		$emergencyBox = $this->getFilterBox("Harms", "harms", TRUE);
		DOM::append($controlPanel, $emergencyBox);
		
		// Debug Logs
		$emergencyBox = $this->getFilterBox("Debugging", "debug");
		DOM::append($controlPanel, $emergencyBox);
		
		// Create clear box
		$clearBtn = DOM::create("span", "x", "", "clear");
		DOM::append($controlPanel, $clearBtn);
	}
	
	/**
	 * Creates a checkbox filter box.
	 * 
	 * @param	string	$title
	 * 		The checkbox title.
	 * 
	 * @param	string	$name
	 * 		The checkbox name.
	 * 
	 * @param	boolean	$checked
	 * 		Sets the checkbox checked.
	 * 
	 * @return	DOMElement
	 * 		The filterbox DOMElement.
	 */
	private function getFilterBox($title, $name, $checked = FALSE)
	{
		// Create form Factory
		$form = new Form();
		
		$filterBox = DOM::create("div", "", "", "filterBox");
		$checkbox = $form->getInput($type = "checkbox", $name, $value = "", $class = "filterCheck", $autofocus = FALSE);
		if ($checked)
			DOM::attr($checkbox, "checked", "checked");
		DOM::attr($checkbox, "data-cat", $name);
		DOM::append($filterBox, $checkbox);
		$checkTitle = $form->getLabel($title, $for = DOM::attr($checkbox, "id"), $class = "");
		DOM::append($filterBox, $checkTitle);
		
		return $filterBox;
	}
	
	/**
	 * Creates a toggler containing all the logs.
	 * 
	 * @param	string	$id
	 * 		The toggler id.
	 * 
	 * @param	string	$head
	 * 		The toggler head.
	 * 
	 * @return	DOMElement
	 * 		A toggler element.
	 */
	public static function getLogs($id, $head = "")
	{
		// Create logBody
		$logBody = DOM::create("div", "", "", "logBody");
		
		// Get logs
		$logs = logger::flush();
		foreach ($logs as $log)
		{
			// Create Log Element
			$logElement = DOM::create("div", "", "", "logItem ".logger::getLevelName($log['level']));
			DOM::append($logBody, $logElement);
			
			// Create Log Element Header
			$logHeader = DOM::create("div", "", "", "logHeader");
			DOM::append($logElement, $logHeader);
			
			if ($log['level'] <= logger::CRITICAL)
				DOM::attr($logElement, "data-cat", "threats");
			else if ($log['level'] <= logger::INFO)
				DOM::attr($logElement, "data-cat", "harms");
			else
				DOM::attr($logElement, "data-cat", "debug");
			
			$logTime = date('H:i:s', $log['time']);
			$logTimeSpan = DOM::create("span", "[".$logTime."] - ");
			DOM::append($logHeader, $logTimeSpan);
			
			$logMessage = DOM::create("span", $log['message']);
			DOM::append($logHeader, $logMessage);
			
			if (!empty($log['description']) || !empty($log['trace']))
			{
				// Add plus sign
				$plus = DOM::create("span", "[+]", "", "exp plus");
				DOM::append($logHeader, $plus);
				
				$minus = DOM::create("span", "[-]", "", "exp minus");
				DOM::append($logHeader, $minus);
				
				// Add logItemDetails
				$details = DOM::create("div", "", "", "logDetails");
				DOM::append($logElement, $details);
			}
			
			// Check if there is description
			if (!empty($log['description']))
			{
				$descHeader = DOM::create("h4", "Log Description");
				DOM::append($details, $descHeader);
				
				$descTitle = DOM::create("p", $log['description'], "", "logDesc");
				DOM::append($details, $descTitle);
			}
			
			// Check if there is stack backtrace
			if (!empty($log['trace']))
			{
				$traceHeader = DOM::create("h4", "Debug Backtrace");
				DOM::append($details, $traceHeader);
				
				$logTrace = self::traceToDom($log['trace']);
				DOM::append($details, $logTrace);
			}
		}
		
		// Create wrapper
		$logWrapper = DOM::create("div", "", "", "logWrapper");
		
		// Build the toggler
		$toggler = new toggler();
		$header = DOM::create("span", $head);
		$logToggler = $toggler->build($id, $header, $logBody, $open = FALSE)->get();
		DOM::append($logWrapper, $logToggler);
		
		return $logWrapper;
	}
	
	/**
	 * Creates a DOMElement from a given backtrace.
	 * 
	 * @param	array	$trace
	 * 		The debug backtrace.
	 * 
	 * @return	DOMElement
	 * 		The backtrace DOMElement for the logViewer.
	 */
	private static function traceToDom($trace)
	{
		// Create traceDiv
		$traceDiv = DOM::create("ol", "", "", "logTrace");
		foreach ($trace as $item)
		{
			// Create Item Content
			$itemContent = "[".$item['line']."]";
			$itemContent .= " ";
			$itemContent .= "[".$item['file']."]";
			// Create traceItem
			$traceItem = DOM::create("li", $itemContent, "", "traceitem");
			DOM::append($traceDiv, $traceItem);
		}
		
		return $traceDiv;
	}
}
//#section_end#
?>