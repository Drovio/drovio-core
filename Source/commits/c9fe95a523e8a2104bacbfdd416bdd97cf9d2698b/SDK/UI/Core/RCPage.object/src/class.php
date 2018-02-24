<?php
//#section#[header]
// Namespace
namespace UI\Core;

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
 * @package	Core
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("ESS", "Prototype", "content::ModuleContainerPrototype");
importer::import("ESS", "Templates", "HTMLPageTemplate");
importer::import("SYS", "Resources", "url");
importer::import("API", "Model", "modules::module");
importer::import("API", "Profile", "tester");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("API", "Literals", "moduleLiteral");
importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Security", "privileges");
importer::import("API", "Security", "account");
importer::import("UI", "Core", "components::RNavToolbar");
importer::import("UI", "Core", "components::RPageFooter");
importer::import("UI", "Modules", "MPage");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Presentation", "notification");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Profiler", "test::sdkTester");

use \ESS\Protocol\BootLoader;
use \ESS\Protocol\loaders\ModuleLoader;
use \ESS\Prototype\content\ModuleContainerPrototype;
use \ESS\Templates\HTMLPageTemplate;
use \SYS\Resources\url;
use \API\Model\modules\module;
use \API\Profile\tester;
use \API\Geoloc\locale;
use \API\Literals\literal;
use \API\Literals\moduleLiteral;
use \API\Resources\storage\cookies;
use \API\Security\privileges;
use \API\Security\account;
use \UI\Core\components\RNavToolbar;
use \UI\Core\components\RPageFooter;
use \UI\Modules\MPage;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Forms\templates\simpleForm;
use \UI\Presentation\notification;
use \DEV\Prototype\sourceMap;
use \DEV\Profiler\test\sdkTester;

/**
 * Redback Core HTML Page
 * 
 * It's a singleton pattern implementation for Redback Core page.
 * Builds the redback's core spine of an HTML page and sets the events to fill the blanks (modules).
 * 
 * @version	0.5-1
 * @created	June 11, 2014, 9:03 (EEST)
 * @revised	September 13, 2014, 10:59 (EEST)
 */
class RCPage extends HTMLPageTemplate
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
	 * The singleton's instance.
	 * 
	 * @type	RCPage
	 */
	private static $instance;

	/**
	 * The module that the page will load.
	 * 
	 * @type	integer
	 */
	private $moduleID;
	
	/**
	 * All the page's extra attributes as fetched from the database.
	 * 
	 * @type	array
	 */
	private $pageAttributes;
	
	/**
	 * Initializes the Redback Core Page with the module id to load.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id that the page will load.
	 * 
	 * @param	array	$pageAttributes
	 * 		The page's attributes from database.
	 * 
	 * @return	void
	 */
	protected function __construct($moduleID, $pageAttributes = array())
	{
		// Put your constructor method code here.
		parent::__construct();
		
		$this->moduleID = $moduleID;
		$this->pageAttributes = $pageAttributes;
	}
	
	/**
	 * Gets the instance of the RCPage.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id that the page will load.
	 * 
	 * @param	array	$pageAttributes
	 * 		The page's attributes from database.
	 * 
	 * @return	RCPage
	 * 		The RCPage unique instance.
	 */
	public static function getInstance($moduleID, $pageAttributes = array())
	{
		if (!isset(self::$instance))
			self::$instance = new RCPage($moduleID, $pageAttributes);
		
		return self::$instance;
	}
	
	/**
	 * Builds the spine of the page
	 * 
	 * @param	string	$subdomain
	 * 		The subdomain where the page will be built.
	 * 
	 * @param	boolean	$dynamic
	 * 		Defines whether the module will be loaded asynchronously (dynamic) in the page.
	 * 
	 * @return	RCPage
	 * 		The page object.
	 */
	public function build($subdomain = "", $dynamic = FALSE)
	{
		// Build the PageTemplate 
		$localeInfo = locale::info();
		parent::build("Redback", $localeInfo['languageCode_ISO1_A2'], $this->pageAttributes['meta']);
		
		// Add initial resources (styles and scripts)
		$this->addResources();

		// Populate Body
		$this->populateBody($subdomain, $dynamic);

		return $this;
	}
	
	/**
	 * Adds the initial static resources to the page header (including initial packages for styles and javascript).
	 * 
	 * @return	void
	 */
	private function addResources()
	{
		// Add primary dependencies (jQuery and others)
		$resource = $this->addScript(Url::resource("/Library/Resources/js/q/jq.jquery.js"));
		$rsrcID = BootLoader::getRsrcID("redback", "jquery");
		BootLoader::setResourceAttributes("js", $resource, "Packages", $rsrcID, TRUE);
		$resource = $this->addScript(Url::resource("/Library/Resources/js/q/jq.jquery.ba-dotimeout.min.js"));
		$rsrcID = BootLoader::getRsrcID("redback", "jquery.ba-dotimeout");
		BootLoader::setResourceAttributes("js", $resource, "Packages", $rsrcID, TRUE);
		
		// Add Layouts style
		$layoutRsrc = $this->addStyle(Url::resource("/Library/Resources/css/l/lt.6d70ed26edd8bdcef1a4d6d17630ac79.css"));
		$rsrcID = BootLoader::getRsrcID("redback", "layouts");
		BootLoader::setResourceAttributes("css", $layoutRsrc, "Packages", $rsrcID, TRUE);
		
		// Add SDK Resources
		$sourceMap = new sourceMap(systemRoot.systemSDK);
		$libraries = $sourceMap->getLibraryList();
		foreach ($libraries as $library)
		{
			$packages = $sourceMap->getPackageList($library);
			foreach ($packages as $package)
			{
				$testerStatus = sdkTester::libPackageStatus($library, $package);
				$cssResource = BootLoader::getResource("css", "Packages", $library, $package, $testerStatus);
				if (!is_null($cssResource))
				{
					$resource = $this->addStyle($cssResource);
					$rsrcID = BootLoader::getRsrcID($library, $package);
					BootLoader::setResourceAttributes("css", $resource, "Packages", $rsrcID, !$testerStatus);
				}
			}
			
			foreach ($packages as $package)
			{
				$testerStatus = sdkTester::libPackageStatus($library, $package);
				$jsResource = BootLoader::getResource("js", "Packages", $library, $package, $testerStatus);
				if (!is_null($jsResource))
				{
					$resource = $this->addScript($jsResource, FALSE, $rsrcID, !$testerStatus);
					$rsrcID = BootLoader::getRsrcID($library, $package);
					BootLoader::setResourceAttributes("js", $resource, "Packages", $rsrcID, !$testerStatus);
				}
			}
		}
	}
	
	/**
	 * Builds the body container.
	 * 
	 * @param	string	$subdomain
	 * 		The subdomain where the page will be built
	 * 
	 * @param	boolean	$dynamic
	 * 		Defines whether the module will be loaded asynchronously (dynamic) in the page.
	 * 
	 * @return	void
	 */
	private function populateBody($subdomain, $dynamic)
	{
		// Check Privileges
		if (empty($this->moduleID))
			$access = "na";
		else
			$access = privileges::moduleAccess($this->moduleID);
		
		// Check whether to build main toolbar
		if (!($access == "open" && !account::validate() && isset($this->pageAttributes['no_toolbar'])))
		{
			// Build Main Toolbar
			$toolbar = RNavToolbar::getInstance($subdomain);
			$toolbarElement = $toolbar->build()->get();
			$this->appendToBody($toolbarElement);
		}
		
		// Populate Body Base
		$mainContainer = DOM::create("div", "", "", "uiMainContainer");
		$this->appendToBody($mainContainer);
		
		// Report Container
		$pageReport = DOM::create("div", "", "pageReport");
		DOM::append($mainContainer, $pageReport);
		
		// Helper Container
		$pageHelper = DOM::create("div", "", "pageHelper");
		DOM::append($mainContainer, $pageHelper);
		
		// Create page container
		$modulePageContainer = DOM::create("div", "", "pageContainer", "uiPageContainer");
		DOM::append($mainContainer, $modulePageContainer);
		
		// Set page title
		$pageTitle = moduleLiteral::get($this->moduleID, "title", array(), FALSE);
		$pageTitle = (empty($pageTitle) || $pageTitle == "N/A" ? "Redback" : $pageTitle);
		$this->setTitle($pageTitle);
		
		// Get page content
		if ($access != "user" && $access != "open")
		{
			// Create the page
			$page = new MPage();
			$title = $this->getPageTitle($access);
			$page->build($title, "protectedAccess");
			
			// Append the notification
			$notification = $this->getPageNotification($access);
			$page->append($notification);
			
			$pageContainer = $page->get();
			DOM::append($modulePageContainer, $pageContainer);
		}
		else if ($dynamic)
		{
			$moduleContainer = new ModuleContainerPrototype($this->moduleID);
			$moduleContent = $moduleContainer->build(array(), TRUE, "", TRUE)->get();
			DOM::append($modulePageContainer, $moduleContent);
		}
		else
		{
			// Load Module
			$mOutput = ModuleLoader::load($this->moduleID);
			$output = json_decode($mOutput, TRUE);

			// Set static resources
			$this->setStaticResources($output['head']);
			
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
					if (!empty($holder))
					{
						$holderElement = HTML::select($holder)->item(0);
						DOM::innerHTML($holderElement, $context);
					}
				}
			}
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
		$footer = RPageFooter::getInstance();
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
			case "uc":
				$title = literal::get("sdk.UI.Html", "title_underConstruction", array(), FALSE);
				break;
			case "off":
				$title = literal::get("sdk.UI.Html", "title_pageNotFound", array(), FALSE);
				break;
			case "no":
				$title = literal::get("sdk.UI.Html", "title_noPrivileges", array(), FALSE);
				break;
			case "na":
				$title = literal::get("sdk.UI.Html", "title_pageNotFound", array(), FALSE);
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
			case "uc":
				// Build Notification
				$ntf = $notification->build($type = notification::WARNING, $header = TRUE, $timeout = FALSE, $disposable = FALSE)->get();
				$message = $notification->getMessage("warning", "wrn.content_uc");
				$notification->appendCustomMessage($message);
				break;
			case "off":
				// Build Notification
				$ntf = $notification->build($type = notification::WARNING, $header = TRUE, $timeout = FALSE, $disposable = FALSE)->get();
				$message = $notification->getMessage("warning", "wrn.content_off");
				$notification->appendCustomMessage($message);
				break;
			case "no":
				// Build Notification
				$ntf = $notification->build($type = notification::ERROR, $header = TRUE, $timeout = FALSE, $disposable = FALSE)->get();
				$message = $notification->getMessage("info", "info.mdl_auth");
				$notification->appendCustomMessage($message);
				break;
			case "na":
				// Build Notification
				$ntf = $notification->build($type = notification::ERROR, $header = TRUE, $timeout = FALSE, $disposable = FALSE)->get();
				$message = $notification->getMessage("debug", "dbg.content_off");
				$notification->appendCustomMessage($message);
				break;
		}
		
		return $ntf;
	}
	
	/**
	 * Sets the page's resources when the page is static.
	 * 
	 * @param	string	$resources
	 * 		The resources generated by the module loaded for this page.
	 * 
	 * @return	void
	 */
	private function setStaticResources($resources)
	{
		foreach ($resources as $rsrc)
		{
			$category = $rsrc['attributes']['category'];
			$package = $rsrc['attributes']['package'];
			if ($rsrc['css'])
			{
				if (empty($category))
				{
					$rsrcCssHref = url::resolve("www", "/Library/Resources/css/".$rsrc['css'].".css");
					$static = TRUE;
				}
				else
				{
					$rsrcCssHref = BootLoader::getResource("css", $category, $category, $package, tester::testerModule($package));
					$static = FALSE;
				}
				
				$resource = $this->addStyle($rsrcCssHref);
				BootLoader::setResourceAttributes("css", $resource, $category, $rsrc['id'], $static);
			}
			
			if ($rsrc['js'])
			{
				if (empty($category))
				{
					$rsrcJsHref = url::resolve("www", "/Library/Resources/js/".$rsrc['js'].".js");
					$static = TRUE;
				}
				else
				{
					$rsrcJsHref = BootLoader::getResource("js", $category, $category, $package, tester::testerModule($package));
					$static = FALSE;
				}
				
				$resource = $this->addScript($rsrcJsHref);
				BootLoader::setResourceAttributes("css", $resource, $category, $rsrc['id'], $static);
			}
		}
	}
}
//#section_end#
?>