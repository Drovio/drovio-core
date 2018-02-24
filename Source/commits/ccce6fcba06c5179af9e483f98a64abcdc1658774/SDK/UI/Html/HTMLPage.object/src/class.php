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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("ESS", "Prototype", "html::ModuleContainerPrototype");
importer::import("ESS", "Templates", "HTMLPageTemplate");
importer::import("API", "Model", "units::modules::Smodule");
importer::import("API", "Profile", "tester");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Resources", "literals::literal");
importer::import("API", "Resources", "literals::moduleLiteral");
importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Security", "privileges");
importer::import("UI", "Html", "HTMLModulePage");
importer::import("UI", "Html", "pageComponents::HTMLNavToolbar");
importer::import("UI", "Html", "pageComponents::HTMLNavFooter");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Forms", "templates::authForm");
importer::import("UI", "Presentation", "notification");

use \ESS\Protocol\client\BootLoader;
use \ESS\Protocol\loaders\ModuleLoader;
use \ESS\Prototype\html\ModuleContainerPrototype;
use \ESS\Templates\HTMLPageTemplate;
use \API\Model\units\modules\Smodule;
use \API\Profile\tester;
use \API\Resources\geoloc\locale;
use \API\Resources\literals\literal;
use \API\Resources\literals\moduleLiteral;
use \API\Resources\storage\cookies;
use \API\Security\privileges;
use \UI\Html\HTMLModulePage;
use \UI\Html\pageComponents\HTMLNavToolbar;
use \UI\Html\pageComponents\HTMLNavFooter;
use \UI\Html\DOM;
use \UI\Forms\templates\authForm;
use \UI\Presentation\notification;

/**
 * System HTML Page
 * 
 * Builds the spine of an HTML page and sets the events to fill the blanks (modules).
 * 
 * @version	{empty}
 * @created	March 17, 2013, 11:18 (EET)
 * @revised	January 23, 2014, 10:56 (EET)
 */
class HTMLPage extends HTMLPageTemplate
{
	/**
	 * The page holder.
	 * 
	 * @type	string
	 */
	const HOLDER = "#pageContainer";
	
	/**
	 * The page report holder.
	 * 
	 * @type	string
	 */
	const REPORT = "#pageReport";
	
	/**
	 * The page helper holder.
	 * 
	 * @type	string
	 */
	const HELPER = "#pageHelper";

	/**
	 * The module that the page will load.
	 * 
	 * @type	int
	 */
	private $moduleID;
	
	/**
	 * Constructor Method
	 * 
	 * @param	int	$moduleID
	 * 		The module that the page will load
	 * 
	 * @return	void
	 */
	public function __construct($moduleID)
	{
		// Put your constructor method code here.
		parent::__construct();
		
		$this->moduleID = $moduleID;
	}
	
	/**
	 * Builds the spine of the page
	 * 
	 * @param	string	$domain
	 * 		The domain where the page will be built
	 * 
	 * @param	boolean	$static
	 * 		Defines whether the module will be loaded asynchronously or statically
	 * 
	 * @return	HTMLPage
	 * 		{description}
	 */
	public function build($domain = "", $static = FALSE)
	{
		// Build the PageTemplate 
		$localeInfo = locale::info();
		parent::build("Redback", $localeInfo['languageCode_ISO1_A2']);

		// Populate Body
		$this->populateBody($domain, $static);

		return $this;
	}
	
	/**
	 * Builds the body container
	 * 
	 * @param	string	$domain
	 * 		The domain where the page will be built
	 * 
	 * @param	boolean	$static
	 * 		Defines whether the module will be loaded asynchronously or statically
	 * 
	 * @return	void
	 */
	private function populateBody($domain, $static)
	{
		// Build HTMLNavToolbar
		$toolbar = new HTMLNavToolbar($domain);
		$toolbarElement = $toolbar->build()->get();
		$this->appendToBody($toolbarElement);
		
		// Populate Body Base
		$mainContainer = DOM::create("div", "", "", "uiMainContainer");
		$this->appendToBody($mainContainer);
		
		// Report Container
		$pageReport = DOM::create("div", "", "pageReport");
		DOM::append($mainContainer, $pageReport);
		
		// Helper Container
		$pageHelper = DOM::create("div", "", "pageHelper");
		DOM::append($mainContainer, $pageHelper);
		
		// Check Privileges
		if (empty($this->moduleID))
			$access = "na";
		else
			$access = privileges::moduleAccess($this->moduleID);
		
		// Create page container
		$modulePageContainer = DOM::create("div", "", "pageContainer", "uiPageContainer");
		DOM::append($mainContainer, $modulePageContainer);
		
		// Get page content
		if ($access != "user" && $access != "open")
		{
			// Create the page
			$page = new HTMLModulePage("OneColumnCentered");
			$title = $this->getPageTitle($access);
			$page->build($title, "protectedAccess");
			
			// Append the notification
			$notification = $this->getPageNotification($access);
			$page->appendToSection("mainContent", $notification);
			
			$pageContainer = $page->get();
			DOM::append($modulePageContainer, $pageContainer);
		}
		else if ($static)
		{
			// Set Resources
			$this->setStaticResources($this->moduleID);

			// Set Title
			$title = moduleLiteral::get($this->moduleID, "title", FALSE);
			$this->setTitle($title);
			
			// Load Module
			$output = ModuleLoader::load($this->moduleID);
			
			// Remove page content
			$pageContent = DOM::find("pageContent");
			DOM::replace($pageContent, NULL);

			// Get body contexts
			foreach ($output['body'] as $body)
			{
				$type = $body['type'];
				$context = $body['context'];
				
				if ($type == "action")
				{
					$pageHelper = DOM::find("pageHelper");
					$actionContainer = DOM::create("span", "", "", "actionContainer");
					DOM::data($actionContainer, "action", $body['context']);
					DOM::append($pageHelper, $actionContainer);
				}
				else if ($type == "data")
				{
					// Get method and holder
					$method = $body['method'];
					$holder = $body['holder'];
					
					// TEMP append to module page container
					DOM::innerHTML($modulePageContainer, $context);
				}
			}
		}
		else
		{
			$moduleContainer = new ModuleContainerPrototype($this->moduleID);
			$moduleContent = $moduleContainer->build()->get();
			DOM::append($modulePageContainer, $moduleContent);
		}
				
		// If there is cookie or _GET variable for noscript, insert Notification div
		if (cookies::get("noscript") || isset($_GET['_rb_noscript']))
		{
			// Create Notification
			$notification = new notification();
			$ntf = $notification->build($type = "warning", $header = TRUE, $footer = TRUE)->get();
			DOM::append($pageReport, $ntf);
			
			// Load Warning Message
			$javascript_warning = $notification->getMessage("warning", "wrn.javascript");
			$notification->appendCustomMessage($javascript_warning);
		}
		
		// Build HTMLNavFooter
		$footer = new HTMLNavFooter();
		$footerElement = $footer->build()->get();
		$this->appendToBody($footerElement);
	}
	
	/**
	 * Gets the page title according to access status.
	 * 
	 * @param	string	$access
	 * 		The module access status.
	 * 
	 * @return	string
	 * 		The page title.
	 */
	private function getPageTitle($access)
	{
		switch ($access)
		{
			case "auth":
				$title = literal::dictionary("authentication", FALSE);
				break;
			case "onauth":
				$title = literal::dictionary("authentication", FALSE);
				break;
			case "uc":
				$title = literal::get("sdk.UI.Html", "title_underConstruction", FALSE);
				break;
			case "off":
				$title = literal::get("sdk.UI.Html", "title_pageNotFound", FALSE);
				break;
			case "login":
				$title = literal::dictionary("login", FALSE);
				break;
			case "no":
				$title = literal::get("sdk.UI.Html", "title_noPrivileges", FALSE);
				break;
			case "na":
				$title = literal::get("sdk.UI.Html", "title_pageNotFound", FALSE);
				break;
		}
		
		return "Redback - ".$title;
	}
	
	/**
	 * Creates a page notification for each access status.
	 * 
	 * @param	string	$access
	 * 		The module access status.
	 * 
	 * @return	DOMElement
	 * 		The notification element.
	 */
	private function getPageNotification($access)
	{
		// Create Notification
		$notification = new notification();
		
		// Set page title
		$title = $this->getPageTitle($access);
		$this->setTitle($title);

		// Build Notification
		switch ($access)
		{
			case "auth":
				// Build Notification
				$ntf = $notification->build($type = "error", $header = FALSE, $footer = TRUE)->get();
				$message = $notification->getMessage("info", "info.mdl_auth");
				$notification->appendCustomMessage($message);
				
				// Build Form
				$aForm = new authForm();
				$form = $aForm->build()->get();
				$notification->append($form);
				break;
			case "onauth":
				// Build Notification
				$ntf = $notification->build($type = "warning", $header = FALSE, $footer = TRUE)->get();
				$message = $notification->getMessage("info", "info.mdl_onauth");
				$notification->appendCustomMessage($message);
				
				// Build Form
				$aForm = new authForm();
				$form = $aForm->build(TRUE)->get();
				$notification->append($form);
				break;
			case "uc":
				// Build Notification
				$ntf = $notification->build($type = "warning", $header = TRUE, $footer = TRUE)->get();
				$message = $notification->getMessage("warning", "wrn.content_uc");
				$notification->appendCustomMessage($message);
				break;
			case "off":
				// Build Notification
				return;
				break;
			case "login":
				// Build Notification
				$ntf = $notification->build($type = "info", $header = FALSE, $footer = TRUE)->get();
				$message = $notification->getMessage("info", "info.user_login");
				$notification->appendCustomMessage($message);
				
				// Build Form
				$aForm = new authForm();
				$form = $aForm->build(TRUE)->get();
				$notification->append($form);
				break;
			case "no":
				// Build Notification
				$ntf = $notification->build($type = "error", $header = FALSE, $footer = TRUE)->get();
				$message = $notification->getMessage("info", "info.mdl_auth");
				$notification->appendCustomMessage($message);
				break;
			case "na":
				// Build Notification
				$ntf = $notification->build($type = "error", $header = TRUE, $footer = TRUE)->get();
				$message = $notification->getMessage("debug", "dbg.content_off");
				$notification->appendCustomMessage($message);
				break;
		}
		
		return $ntf;
	}
	
	/**
	 * Sets the page's resources when the page is static.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id from which the resources will be loaded.
	 * 
	 * @return	void
	 */
	private function setStaticResources($moduleID)
	{
		// Set Module Resources
		if (Smodule::hasCSS($moduleID))
		{
			$mdlCssHref = BootLoader::getCSSUrl("Modules", $moduleID, tester::testerModule($moduleID));
			$this->addStyle($mdlCssHref);
		}
		if (Smodule::hasJS($moduleID))
		{
			$mdlJsHref = BootLoader::getJSUrl("Modules", $moduleID, tester::testerModule($moduleID));
			$this->addScript($mdlJsHref);
		}
	}
}
//#section_end#
?>