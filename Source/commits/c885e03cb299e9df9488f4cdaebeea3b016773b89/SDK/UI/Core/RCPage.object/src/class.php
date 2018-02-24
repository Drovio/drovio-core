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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Protocol", "loaders/ModuleLoader");
importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("ESS", "Prototype", "content/ModuleContainerPrototype");
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "cookies");
importer::import("SYS", "Resources", "settings/genericSettings");
importer::import("SYS", "Resources", "pages/domain");
importer::import("API", "Model", "modules/module");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("API", "Literals", "moduleLiteral");
importer::import("API", "Security", "privileges");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("UI", "Core", "components/RNavToolbar");
importer::import("UI", "Core", "components/RPageFooter");
importer::import("UI", "Modules", "MPage");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Presentation", "notification");
importer::import("UI", "Templates", "HTMLPageTemplate");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Profile", "tester");
importer::import("DEV", "Core", "sdk/sdkLibrary");
importer::import("DEV", "Core", "test/sdkTester");
importer::import("DEV", "Core", "test/jqTester");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Projects", "projectLibrary");

use \ESS\Protocol\BootLoader;
use \ESS\Protocol\loaders\ModuleLoader;
use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Prototype\content\ModuleContainerPrototype;
use \ESS\Environment\url;
use \ESS\Environment\cookies;
use \SYS\Resources\settings\genericSettings;
use \SYS\Resources\pages\domain;
use \API\Model\modules\module;
use \API\Geoloc\locale;
use \API\Literals\literal;
use \API\Literals\moduleLiteral;
use \API\Security\privileges;
use \API\Profile\account;
use \API\Profile\team;
use \UI\Core\components\RNavToolbar;
use \UI\Core\components\RPageFooter;
use \UI\Modules\MPage;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Presentation\notification;
use \UI\Templates\HTMLPageTemplate;
use \DEV\Prototype\sourceMap;
use \DEV\Profile\tester;
use \DEV\Core\sdk\sdkLibrary;
use \DEV\Core\test\sdkTester;
use \DEV\Core\test\jqTester;
use \DEV\Core\coreProject;
use \DEV\Projects\projectLibrary;

/**
 * Core HTML page builder
 * 
 * It's a singleton pattern implementation for drovio core page.
 * Builds the drovio's core spine of an HTML page and sets the events to fill the blanks (modules).
 * 
 * @version	0.8-3
 * @created	June 11, 2014, 7:03 (BST)
 * @updated	December 13, 2015, 18:08 (GMT)
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
	 * Identifies the page that has toolbar.
	 * 
	 * @type	string
	 */
	const HAS_TOOLBAR_CLASS = "hasToolbar";
	
	/**
	 * Identifies the page content that is full screen.
	 * 
	 * @type	string
	 */
	const FULL_SCREEN_CLASS = "fullScreenPage";
	
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
	 * Initializes the core page builder with the module id to load.
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
		
		// Get domain team name
		$domainTeamName = team::getDomainTeamName();
		if (is_null($domainTeamName))
			return;
		
		// Check whether there is an active team ([teamName].drov.io)
		$teamID = team::getTeamID();
		$teamInfo = team::info();
		
		// Check if account is guest and load dashboard login
		// If it is a valid team and set module id to be dashboard
		// Otherwise redirect to www
		if (!account::getInstance()->validate())
			$this->moduleID = 373;
		else if (!empty($teamID) && strtolower($teamInfo['uname']) == $domainTeamName)
		{
			// Check for efplayer
			$requestURI = $_SERVER['REQUEST_URI'];
			if (strpos($requestURI, "efplay") > 0)
				$this->moduleID = 384;
			else
				$this->moduleID = 226;
		}
		else
			header("Location: ".url::resolve("www", "/"));
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
		// Build the Page Template
		$localeInfo = locale::info();
		parent::build("Drovio", $localeInfo['languageCode_ISO1_A2'], $this->pageAttributes['meta'], $this->pageAttributes['og']);
		
		// Set page title
		$pageTitle = moduleLiteral::get($this->moduleID, "title", array(), FALSE);
		$pageTitle = (empty($pageTitle) || $pageTitle == "N/A" ? "Drovio" : $pageTitle);
		$this->setTitle($pageTitle);
		
		// Get page description (if any)
		$pageDescription = moduleLiteral::get($this->moduleID, "description", array(), FALSE);
		
		// Add social meta
		$this->addSocialMeta($this->pageAttributes['og']);
		
		// Add initial resources (styles and scripts)
		$this->addResources();

		// Populate Body
		$this->populateBody($subdomain, $dynamic);

		return $this;
	}
	
	/**
	 * Add all social meta to the page.
	 * 
	 * @param	boolean	$openGraph
	 * 		Whether to add the default og meta tags.
	 * 
	 * @return	void
	 */
	private function addSocialMeta($openGraph = FALSE)
	{
		// Set facebook id
		$this->addMeta($name = "fb:app_id", $content = "1528296957491399", $httpEquiv = "", $charset = "");
		
		// Add extra open graph data
		if ($openGraph)
		{
			// Set open graph
			$ogData = array();
			$ogData['title'] = $pageTitle;
			$pageDescription = moduleLiteral::get($this->moduleID, "description", array(), FALSE);
			if (!empty($pageDescription))
				$ogData['description'] = $pageDescription;
			$this->addOpenGraphMeta($ogData);
		}
	}
	
	/**
	 * Adds the initial static resources to the page header (including initial packages for styles and javascript).
	 * 
	 * @return	void
	 */
	private function addResources()
	{
		// Get selected version of jQuery
		$st = new genericSettings();
		$jQueryFile = $st->get("jquery");
		
		// Check if there is a tester activated
		$jqTesterStatus = jqTester::status();
		if ($jqTesterStatus !== FALSE)
			$jQueryFile = $jqTesterStatus;
			
		// Add primary dependencies (jQuery and jQuery plugins)
		$jqResources = array();
		$jqResources[] = "jquery/".$jQueryFile;
		$jqResources[] = "jquery/plugins/jquery.easing-1.3.js";
		$jqResources[] = "jquery/plugins/jquery.ba-dotimeout-1.0.min.js";
		foreach ($jqResources as $rsrcName)
		{
			$resource = $this->addScript(url::resolve("cdn", "/js/".$rsrcName));
			$rsrcID = BootLoader::getRsrcID("cdn", "js_lib_".$rsrcName);
			BootLoader::setResourceAttributes("js", $resource, coreProject::PROJECT_TYPE, $rsrcID, TRUE);
		}
		
		// Add reset.css
		$resource = $this->addStyle(url::resolve("cdn", "/css/reset.css"));
		$rsrcID = BootLoader::getRsrcID("cdn", "css_reset");
		BootLoader::setResourceAttributes("css", $resource, coreProject::PROJECT_TYPE, $rsrcID, TRUE);
		
		// Add fonts
		$resource = $this->addStyle("https://fonts.googleapis.com/css?family=Open+Sans&subset=latin,greek");
		$rsrcID = BootLoader::getRsrcID("fonts", "OpenSans");
		BootLoader::setResourceAttributes("css", $resource, coreProject::PROJECT_TYPE, $rsrcID, TRUE);
		
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
				
				if (!$testerStatus)
				{
					$cssResourceUrl = BootLoader::getResourceUrl(BootLoader::RSRC_CSS, coreProject::PROJECT_ID, $library, $package);
					$jsResourceUrl = BootLoader::getResourceUrl(BootLoader::RSRC_JS, coreProject::PROJECT_ID, $library, $package);
				}
				else
				{
					$cssResourceUrl = BootLoader::getTesterResourceUrl("/ajax/sdk/css.php", $library, $package);
					$jsResourceUrl = BootLoader::getTesterResourceUrl("/ajax/sdk/js.php", $library, $package);
				}
				
				if (!empty($cssResourceUrl))
				{
					$resource = $this->addStyle($cssResourceUrl);
					$rsrcID = BootLoader::getRsrcID($library, $package);
					BootLoader::setResourceAttributes("css", $resource, coreProject::PROJECT_TYPE, $rsrcID, !$testerStatus);
				}
				
				
				if (!empty($jsResourceUrl))
				{
					$resource = $this->addScript($jsResourceUrl);
					$rsrcID = BootLoader::getRsrcID($library, $package);
					BootLoader::setResourceAttributes("js", $resource, coreProject::PROJECT_TYPE, $rsrcID, !$testerStatus);
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
		$noToolbar_guest = ($access == "open" && !account::getInstance()->validate() && (isset($this->pageAttributes['no_toolbar']) || isset($this->pageAttributes['no_toolbar_guest'])));
		$embedded_content = isset($this->pageAttributes['embedded']);
		if (!$embedded_content && !$noToolbar_guest)
		{
			// Add class to declare toolbar existence
			HTML::addClass($this->getBody(), "hasToolbar");
			
			// Build Main Toolbar
			$toolbar = RNavToolbar::getInstance($subdomain);
			$toolbarElement = $toolbar->build()->get();
			$this->appendToBody($toolbarElement);
		}
		
		// Add embedded flag
		if ($embedded_content)
			HTML::addClass($this->getBody(), "embedded");
		
		// Build platform versioning
		$this->buildPlatformVersioning();
		
		// Populate Body Base
		$mainContainer = DOM::create("div", "", "", "uiMainContainer");
		$this->appendToBody($mainContainer);
		
		// Add guest identifier if guest
		if (!account::getInstance()->validate())
			HTML::addClass($mainContainer, "guest");
		
		// Report Container
		$pageReport = DOM::create("div", "", "pageReport");
		DOM::append($mainContainer, $pageReport);
		
		// Helper Container
		$pageHelper = DOM::create("div", "", "pageHelper");
		DOM::append($mainContainer, $pageHelper);
		
		// Create page container
		$modulePageContainer = DOM::create("div", "", "pageContainer", "uiPageContainer");
		DOM::append($mainContainer, $modulePageContainer);
		
		// Check page access
		if ($access != "user" && $access != "open")
		{
			// Get error module id to load
			$this->moduleID = $this->getErrorModuleID($access);
			$dynamic = FALSE;
		}
		
		// Decide how to load the module
		if ($dynamic)
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
			$this->setStaticResources($output['head'][BootLoader::RSRC_HD_KEY]);
			
			// Remove page content
			$pageContent = DOM::find(MPage::PAGE_CONTAINER_ID);
			DOM::replace($pageContent, NULL);
			
			// Parse report and get actions
			HTMLServerReport::parseReportContent($output, $defaultHolder = "", $actions);
			
			// Parse actions
			foreach ($actions as $key => $action)
			{
				// Find page helper and append actions
				$pageHelper = DOM::find("pageHelper");
				$actionContainer = DOM::create("span", "", "", "actionContainer");
				DOM::data($actionContainer, "action", $action);
				DOM::append($pageHelper, $actionContainer);
			}
		}
		
		// If there is cookie or _GET variable for noscript, insert Notification div
		if (cookies::get("noscript") || isset($_GET['_rb_noscript']))
		{
			// Create Notification
			$notification = new notification();
			$ntf = $notification->build($type = notification::WARNING, $header = TRUE, $timeout = FALSE, $disposable = FALSE)->get();
			DOM::append($pageReport, $ntf);
			
			// Load Warning Message
			$javascript_warning = $notification->getMessage("warning", "wrn.javascript");
			$notification->appendCustomMessage($javascript_warning);
		}
		
		// Build HTMLNavFooter
		if (!$embedded_content)
		{
			$footer = RPageFooter::getInstance();
			$footerElement = $footer->build()->get();
			$this->appendToBody($footerElement);
		}
	}
	
	/**
	 * Builds special meta data in the document body to notify if there are platform updates.
	 * 
	 * @return	void
	 */
	private function buildPlatformVersioning()
	{
		// Get platform live project versions
		$projects = array(1, 2);
		$platformVersions = array();
		foreach ($projects as $projectID)
			$platformVersions[$projectID] = projectLibrary::getLastProjectVersion($projectID, $live = TRUE);
		
		// Add data attribute to body element
		DOM::data($this->getBody(), "pv", $platformVersions);
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
	private function getErrorModuleID($access)
	{
		// Initialize system generic settings
		$settings = new genericSettings();

		// Get module id according to access
		$errorModuleID = NULL;
		switch ($access)
		{
			case "uc":
				$errorModuleID = $settings->get("page_uc");
				break;
			case "off":
			case "na":
				$errorModuleID = $settings->get("page_nf");
				break;
			case "no":
				$errorModuleID = $settings->get("page_ad");
				break;
		}
		
		// Return error module id
		return $errorModuleID;
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
		$loaded = array();
		foreach ($resources as $resourceInfo)
		{
			$category = $resourceInfo['attributes']['category'];
			$package = $resourceInfo['attributes']['package'];
			if ($resourceInfo['css'] && !$loaded[$resourceInfo['id']."_css"])
			{
				$resource = $this->addStyle($resourceInfo['css']);
				BootLoader::setResourceAttributes("css", $resource, $category, $resourceInfo['id'], $static);
				$loaded[$resourceInfo['id']."_css"] = 1;
			}
			
			if ($resourceInfo['js'] && !$loaded[$resourceInfo['id']."_js"])
			{
				$resource = $this->addScript($resourceInfo['js']);
				BootLoader::setResourceAttributes("css", $resource, $category, $resourceInfo['id'], $static);
				$loaded[$resourceInfo['id']."_js"] = 1;
			}
		}
	}
}
//#section_end#
?>