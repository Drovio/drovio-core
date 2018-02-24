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
importer::import("API", "Resources", "url");
importer::import("API", "Security", "privileges");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "HTMLModulePage");
importer::import("UI", "Html", "pageComponents::HTMLNavToolbar");
importer::import("UI", "Html", "pageComponents::HTMLNavFooter");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "templates::authForm");
importer::import("UI", "Forms", "templates::simpleForm");
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
use \API\Resources\url;
use \API\Security\privileges;
use \API\Security\account;
use \UI\Html\HTMLModulePage;
use \UI\Html\pageComponents\HTMLNavToolbar;
use \UI\Html\pageComponents\HTMLNavFooter;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Forms\templates\authForm;
use \UI\Forms\templates\simpleForm;
use \UI\Presentation\notification;

/**
 * System HTML Page
 * 
 * Builds the spine of an HTML page and sets the events to fill the blanks (modules).
 * 
 * @version	{empty}
 * @created	March 17, 2013, 11:18 (EET)
 * @revised	March 24, 2014, 12:18 (EET)
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
	 * @param	boolean	$meta
	 * 		Indicates whether to include meta tags in the html head.
	 * 
	 * @return	HTMLPage
	 * 		{description}
	 */
	public function build($domain = "", $static = FALSE, $meta = FALSE)
	{
		// Build the PageTemplate 
		$localeInfo = locale::info();
		parent::build("Redback", $localeInfo['languageCode_ISO1_A2'], $meta);

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
		
		// If access is open, build connection toolbar
		
		if ($access == "open" && !account::validate())
			$this->buildConnectionToolbar($mainContainer);
		
		// Create page container
		$modulePageContainer = DOM::create("div", "", "pageContainer", "uiPageContainer");
		DOM::append($mainContainer, $modulePageContainer);
		
		// Set page title
		$pageTitle = moduleLiteral::get($this->moduleID, "title", FALSE);
		$pageTitle = (empty($pageTitle) || $pageTitle == "N/A" ? "Redback" : $pageTitle);
		$this->setTitle($pageTitle);
		
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
					
					// Get holder and append context
					$holderElement = HTML::select($holder)->item(0);
					DOM::innerHTML($holderElement, $context);
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
	 * Builds the page account connection toolbar on the top of the page.
	 * 
	 * @param	DOMElement	$mainContainer
	 * 		The content container inside the page container.
	 * 
	 * @return	void
	 */
	private function buildConnectionToolbar($mainContainer)
	{
		$toolbar = DOM::create("div", "", "", "connectionToolbar");
		DOM::append($mainContainer, $toolbar);
		
		$centerContainer = DOM::create("div", "", "", "centerContainer");
		DOM::append($toolbar, $centerContainer);
		
		// Registration Container
		$regContainer = DOM::create("div", "", "", "regContainer");
		DOM::append($centerContainer, $regContainer);
		
		$regTitleContent = literal::get("global.temp", "lbl_registrationBarTitle");
		$regTitle = DOM::create("h4");
		DOM::append($regTitle, $regTitleContent);
		DOM::append($regContainer, $regTitle);
		
		// Create Redback Account
		$regButtonTitle = literal::get("sdk.UI.Html", "lbl_createRbAccount");
		$regButton = DOM::create("a", "", "", "regBtn");
		$regURL = url::resolve("my", "/register/");
		DOM::attr($regButton, "href", $regURL);
		DOM::attr($regButton, "target", "_blank");
		DOM::append($regButton, $regButtonTitle);
		DOM::append($regContainer, $regButton);
		
		
		// Login Container
		$loginContainer = DOM::create("div", "", "", "loginContainer");
		DOM::append($centerContainer, $loginContainer);
		
		$form = new simpleForm();
		$url = url::resolve("www", "/ajax/account/login.php");
		$loginForm = $form->build(NULL, $url, FALSE)->get();
		DOM::append($loginContainer, $loginForm);
		
		// Sub and origin
		$input = $form->getInput($type = "hidden", $name = "logintype", $value = "page", $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($input);
		
		$uDiv = DOM::create("div", "", "", "uDiv");
		$form->append($uDiv);
		
		$input = $form->getInput($type = "text", $name = "username", $value = "", $class = "", $autofocus = TRUE, $required = FALSE);
		
		$title = literal::dictionary("username");
		$for = DOM::attr($input, "id");
		$label = $form->getLabel($title, $for, $class = "loginLabel");
		DOM::append($uDiv, $label);
		DOM::append($uDiv, $input);
		
		$forgotTitle = literal::get("sdk.UI.Html", "lbl_revoverPassword");
		$forgotLink = DOM::create("a", $forgotTitle, "", "fLink");
		$fURL = url::resolve("login", "/reset.php");
		DOM::attr($forgotLink, "href", $fURL);
		DOM::attr($forgotLink, "target", "_blank");
		DOM::attr($forgotLink, "tabindex", "-1");
		DOM::append($uDiv, $forgotLink);
		
		$pDiv = DOM::create("div", "", "", "pDiv");
		$form->append($pDiv);
		
		$input = $form->getInput($type = "password", $name = "password", $value = "", $class = "", $autofocus = FALSE, $required = FALSE);
		
		$title = literal::dictionary("password");
		$for = DOM::attr($input, "id");
		$label = $form->getLabel($title, $for, $class = "loginLabel");
		DOM::append($pDiv, $label);
		DOM::append($pDiv, $input);
		
		// Remember me
		$row = DOM::create("span", "", "", "rrow");
		DOM::append($pDiv, $row);
		
		$input = $form->getInput($type = "checkbox", $name = "rememberme", $value = "", $class = "", $autofocus = FALSE, $required = FALSE);
		
		$title = literal::get("sdk.UI.Html", "lbl_login_rememberme");
		$for = DOM::attr($input, "id");
		$label = $form->getLabel($title, $for, $class = "checklabel");
		DOM::append($row, $input);
		DOM::append($row, $label);
		
		$title = literal::dictionary("login");
		$button = $form->getSubmitButton($title, "lgBtn");
		$form->append($button);
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