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
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "cookies");
importer::import("API", "Model", "modules::module");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("API", "Literals", "moduleLiteral");
importer::import("API", "Security", "privileges");
importer::import("API", "Profile", "account");
importer::import("UI", "Core", "components::RNavToolbar");
importer::import("UI", "Core", "components::RPageFooter");
importer::import("UI", "Modules", "MPage");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Presentation", "notification");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Profile", "tester");
importer::import("DEV", "Core", "sdk::sdkLibrary");
importer::import("DEV", "Core", "sdk::sdkPackage");
importer::import("DEV", "Core", "test::sdkTester");

use \ESS\Protocol\BootLoader;
use \ESS\Protocol\loaders\ModuleLoader;
use \ESS\Prototype\content\ModuleContainerPrototype;
use \ESS\Templates\HTMLPageTemplate;
use \ESS\Environment\url;
use \ESS\Environment\cookies;
use \API\Model\modules\module;
use \API\Geoloc\locale;
use \API\Literals\literal;
use \API\Literals\moduleLiteral;
use \API\Security\privileges;
use \API\Profile\account;
use \UI\Core\components\RNavToolbar;
use \UI\Core\components\RPageFooter;
use \UI\Modules\MPage;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Forms\templates\simpleForm;
use \UI\Presentation\notification;
use \DEV\Prototype\sourceMap;
use \DEV\Profile\tester;
use \DEV\Core\sdk\sdkLibrary;
use \DEV\Core\sdk\sdkPackage;
use \DEV\Core\test\sdkTester;

/**
 * Redback Core HTML Page
 * 
 * It's a singleton pattern implementation for Redback Core page.
 * Builds the redback's core spine of an HTML page and sets the events to fill the blanks (modules).
 * 
 * @version	0.5-6
 * @created	June 11, 2014, 9:03 (EEST)
 * @revised	November 4, 2014, 11:09 (EET)
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
		parent::build("Redback", $localeInfo['languageCode_ISO1_A2'], $this->pageAttributes['meta'], $this->pageAttributes['og']);
		
		// Set page title
		$pageTitle = moduleLiteral::get($this->moduleID, "title", array(), FALSE);
		$pageTitle = (empty($pageTitle) || $pageTitle == "N/A" ? "Redback" : $pageTitle);
		$this->setTitle($pageTitle);
		
		// Get page description (if any)
		$pageDescription = moduleLiteral::get($this->moduleID, "description", array(), FALSE);
		
		// Add extra open graph data
		if ($this->pageAttributes['og'])
		{
			$ogData = array();
			$ogData['title'] = "Redback - ".$pageTitle;
			$pageDescription = moduleLiteral::get($this->moduleID, "description", array(), FALSE);
			if (!empty($pageDescription))
				$ogData['description'] = $pageDescription;
			$this->addOpenGraphMeta($ogData);
		}
		
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
		$resource = $this->addScript(url::resource("/Library/Resources/js/q/jq.jquery.js"));
		$rsrcID = BootLoader::getRsrcID("redback", "jquery");
		BootLoader::setResourceAttributes("js", $resource, "Packages", $rsrcID, TRUE);
		$resource = $this->addScript(url::resource("/Library/Resources/js/q/jq.jquery.ba-dotimeout.min.js"));
		$rsrcID = BootLoader::getRsrcID("redback", "jquery.ba-dotimeout");
		BootLoader::setResourceAttributes("js", $resource, "Packages", $rsrcID, TRUE);
		
		// Add Layouts style
		$layoutRsrc = $this->addStyle(url::resource("/Library/Resources/css/l/lt.6d70ed26edd8bdcef1a4d6d17630ac79.css"));
		$rsrcID = BootLoader::getRsrcID("redback", "layouts");
		BootLoader::setResourceAttributes("css", $layoutRsrc, "Packages", $rsrcID, TRUE);
		
		// Add SDK Resources
		
		// Get development libraries
		$devLibraries = array();
		try
		{
			$sdkLib = new sdkLibrary();
			$devLibraries = $sdkLib->getList();
		}
		catch (Exception $ex)
		{
			$devLibraries = array();
		}
		
		// Get production libraries
		$sourceMap = new sourceMap(systemRoot.systemSDK);
		$prdLibraries = $sourceMap->getLibraryList();
		
		// Merge all libraries
		$allLibraries = array_merge($prdLibraries, $devLibraries);
		foreach ($allLibraries as $library)
		{
			// Get all packages from development and from production
			if (in_array($library, $prdLibraries))
				$prdPackages = $sourceMap->getPackageList($library);
			$devPackages = array();
			if (in_array($library, $devLibraries))
				$devPackages = $sdkLib->getPackageList($library);
				
			// Merge all packages together
			$allPackages = array_merge($prdPackages, $devPackages);
			foreach ($allPackages as $package)
			{
				// Get package tester status
				$testerStatus = sdkTester::libPackageStatus($library, $package);
				
				// If package is not in tester mode, load only if it is on production
				if (!$testerStatus && !in_array($library, $prdLibraries) && !in_array($package, $prdPackages))
					continue;
				
				// Get css resource
				$cssResource = BootLoader::getResource("css", "Packages", $library, $package, $testerStatus);
				if (!is_null($cssResource))
				{
					$resource = $this->addStyle($cssResource);
					$rsrcID = BootLoader::getRsrcID($library, $package);
					BootLoader::setResourceAttributes("css", $resource, "Packages", $rsrcID, !$testerStatus);
				}
				
				// Get js resource
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
			$ntf = $notification->build($type = "warning", $header = TRUE, $timeout = FALSE, $disposable = FALSE)->get();
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