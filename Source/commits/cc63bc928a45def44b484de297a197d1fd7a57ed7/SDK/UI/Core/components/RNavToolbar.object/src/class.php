<?php
//#section#[header]
// Namespace
namespace UI\Core\components;

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
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Protocol", "client/NavigatorProtocol");
importer::import("ESS", "Prototype", "ModuleActionFactory");
importer::import("ESS", "Environment", "url");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("API", "Model", "core/resource");
importer::import("API", "Model", "modules/module");
importer::import("API", "Profile", "person");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Security", "privileges");
importer::import("UI", "Core", "components/RTRibbon");
importer::import("UI", "Core", "components/toolbar/toolbarItem");
importer::import("UI", "Core", "components/ribbon/rPanel");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Prototype", "UIObjectPrototype");
importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Resources", "paths");

use \ESS\Protocol\ModuleProtocol;
use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Prototype\ModuleActionFactory;
use \ESS\Environment\url;
use \API\Geoloc\locale;
use \API\Literals\literal;
use \API\Model\core\resource;
use \API\Model\modules\module;
use \API\Profile\person;
use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\DOMParser;
use \API\Security\privileges;
use \UI\Core\components\RTRibbon;
use \UI\Core\components\toolbar\toolbarItem;
use \UI\Core\components\ribbon\rPanel;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Prototype\UIObjectPrototype;
use \DEV\Profiler\logger;
use \DEV\Projects\projectLibrary;
use \DEV\Core\coreProject;
use \DEV\Resources\paths;

/**
 * Redback Core Navigation Toolbar
 * 
 * It's a singleton pattern implementation for Redback Core Toolbar.
 * Builds the system's top navigation toolbar with all the needed items.
 * It is a global builder for all redback across all subdomains.
 * 
 * @version	0.1-31
 * @created	June 11, 2014, 7:15 (BST)
 * @updated	December 17, 2015, 10:37 (GMT)
 */
class RNavToolbar extends UIObjectPrototype
{
	/**
	 * The singleton's instance.
	 * 
	 * @type	RNavToolbar
	 */
	private static $instance;
	
	/**
	 * The ribbon object
	 * 
	 * @type	RTRibbon
	 */
	private $ribbon;
	/**
	 * The user's navigation section.
	 * 
	 * @type	DOMElement
	 */
	private $userNav;
	/**
	 * The domain's navigation section.
	 * 
	 * @type	DOMElement
	 */
	private $domainNav;
	/**
	 * The navigation's domain
	 * 
	 * @type	string
	 */
	private $domain;
	
	/**
	 * Initializes the toolbar for the given domain (where the page is built).
	 * 
	 * @param	string	$domain
	 * 		The navigation's domain.
	 * 
	 * @return	void
	 */
	protected function __construct($domain)
	{
		// Put your constructor method code here.
		$this->domain = $domain;
	}
	
	/**
	 * Gets the instance of the RNavToolbar.
	 * 
	 * @param	string	$domain
	 * 		The navigation's domain.
	 * 
	 * @return	RNavToolbar
	 * 		The RNavToolbar unique instance.
	 */
	public static function getInstance($domain)
	{
		if (!isset(self::$instance))
			self::$instance = new RNavToolbar($domain);
		
		return self::$instance;
	}
	
	/**
	 * Builds the entire toolbar.
	 * 
	 * @return	RNavToolbar
	 * 		The RNabToolbar object.
	 */
	public function build()
	{
		// Create toolbarContainer
		$toolbarHolder = DOM::create("div", "", "", "uiMainToolbar");
		$this->set($toolbarHolder);
		
		// Toolbar
		$toolbarNav = DOM::create("div", "", "", "uiToolbarNav");
		DOM::append($toolbarHolder, $toolbarNav);
		
		// Loading bar
		$loadingBar = DOM::create("div", "", "", "loadingBar");
		DOM::append($toolbarNav, $loadingBar);
		
		// Left Domain Navigation
		$domainNav = DOM::create("div", "", "domainNav", "tlbNav leftNav");
		DOM::append($toolbarNav, $domainNav);
		$this->domainNav = $domainNav;
		
		// Left Page Navigation
		$pageNav = DOM::create("div", "", "pageNav", "tlbNav leftNav");
		DOM::append($toolbarNav, $pageNav);
		
		// Right User Navigation
		$userNav = DOM::create("div", "", "myNav", "tlbNav rightNav");
		DOM::append($toolbarNav, $userNav);
		$this->userNav = $userNav;
		
		// Build HTMLRibbon
		$this->buildRibbon();
		
		// Build User Navigation
		$this->buildUserNavigation();
	
		return $this;
	}
	
	/**
	 * Gets the toolbar's clear action array.
	 * It sets an action to clear page's items.
	 * 
	 * @return	array
	 * 		An array containing the action "type" and the action "value" according to ModuleProtocol.
	 */
	public static function getClearAction()
	{
		$action = array();
		$action['type'] = "clear.toolbar";
		$action['value'] = "";
		
		return $action;
	}
	
	/**
	 * Builds the toolbar's ribbon.
	 * 
	 * @return	void
	 */
	private function buildRibbon()
	{
		// Create Ribbon
		$this->ribbon = RTRibbon::getInstance();
		$ribbonElement = $this->ribbon->build()->get();
		DOM::append($this->get(), $ribbonElement);
	}
	
	/**
	 * Builds the user's navigation (whether the user is logged in or guest).
	 * 
	 * @return	void
	 */
	private function buildUserNavigation()
	{
		// Toolbar Navigation Builder
		$toolbarItem = new toolbarItem();

		// Profile Navigation
		if (!account::getInstance()->validate())
		{
			$item = $toolbarItem->build(literal::dictionary("guest"), "", "userStarter guest", toolbarItem::SCOPE_GLOBAL)->get();
			
			// Set login action on click
			ModuleActionFactory::setModuleAction($item, 319, $viewName = "", $holder = "", $attr = array(), $loading = FALSE);
		}
		else
		{
			$fullName = account::getInstance()->getAccountTitle();
			$extra = NULL;
			
			// Get account image
			$accountInfo = account::getInstance()->info();
			$img = TRUE;
			if (isset($accountInfo['profile_image_url']))
			{
				$img = DOM::create("img");
				DOM::attr($img, "src", $accountInfo['profile_image_url']);
			}
			$item = $toolbarItem->build($fullName, "", "userStarter ".$extraClass, toolbarItem::SCOPE_GLOBAL, $img, $extra)->get();
			
			// Add account info ribbon collection
			$this->ribbon->addCollection("userNav", "Account Info", 154, "", TRUE);
			
			// Build Domain Nav
			$this->buildProfileTools();
			$this->buildDomainNavigation();
		}
		
		RTRibbon::addNavigation($item, "userNav", "float", "obedient toggle");
		DOM::append($this->userNav, $item);
		
		// Region Selector
		$this->buildRegionNav();
	}
	
	/**
	 * Builds the profile tools (for developers and other groups).
	 * 
	 * @return	void
	 */
	private function buildProfileTools()
	{
		// Check if there is a logged in account
		if (!account::getInstance()->validate())
			return;
		
		// Create toolbar item
		$toolbarItem = new toolbarItem();
		
		// Tools Container
		$userTools = DOM::create("div", "", "userTools");
		DOM::append($this->userNav, $userTools);
		
		// Insert Developer's Tools if user is a developer
		if (privileges::accountToGroup("PROJECT_ADMIN") || privileges::accountToGroup("PROJECT_DEVELOPER"))
		{
			$item = $toolbarItem->build("", "", "userDeveloper", "global")->get();
			RTRibbon::addNavigation($item, "devPanel", "inline", "obedient toggle");
			DOM::append($userTools, $item);
			
			// Build Developer's Collection Group
			$devCollection = $this->ribbon->addCollection("devPanel", "Developer's Control Panel", 208, "", logger::status());
		}
	}
	
	/**
	 * Builds the region/language selector navigation.
	 * 
	 * @return	void
	 */
	private function buildRegionNav()
	{
		// Get Region info
		$localeInfo = locale::info();

		// Toolbar Navigation Builder
		$toolbarItem = new toolbarItem();
		
		// Locale Icon
		$srcUrl = url::resolve("cdn", "/media/geo/flags/".$localeInfo['imageName']);
		
		// Create region image
		$snippet_img = DOM::create("span", "", "", "region");
		$style = "background: url('".$srcUrl."') no-repeat center;";
		$style .= "background-size: cover";
		DOM::attr($snippet_img, "style", $style);
		DOM::attr($snippet_img, "title", $localeInfo['friendlyName']);
		DOM::attr($snippet_img, "alt", $localeInfo['friendlyName']);
		
		$item = $toolbarItem->build("", "", "regionSelector", "global", $snippet_img)->get();
		RTRibbon::addNavigation($item, "regionNav", "float", "obedient toggle");
		DOM::append($this->userNav, $item);
		
		// Region Collection
		$this->ribbon->addCollection("regionNav", "Language Settings", 258, "", TRUE);
	}
	
	/**
	 * Builds the Domain Navigation
	 * 
	 * @return	void
	 */
	private function buildDomainNavigation()
	{
		// Add global item
		$toolbarItem = new toolbarItem();
		$item = $toolbarItem->build("", "", "drovio platform", toolbarItem::SCOPE_GLOBAL)->get();
		RTRibbon::addNavigation($item, "platformNav", "float", "obedient toggle");
		DOM::append($this->domainNav, $item);
		
		// Platform Main Toolbar Collection
		$this->ribbon->addCollection("platformNav", "Drovio", 366, "teamSelector", TRUE);
		
		// Initialize Toolbar Menu
		$toolbarItem = new toolbarItem();
		$domainNav = $this->domainNav;
		$parser = new DOMParser();
		try
		{
			$navPath = resource::getPath("/resources/subdomains/navigation/".$this->domain.".xml", $rootRelative = TRUE);
			$parser->load($navPath);
		}
		catch (Exception $ex)
		{
			return;
		}
		
		// Get Groups of Navigation
		$groups = $parser->evaluate("//group");
		foreach ($groups as $group)
		{
			// Get Reference ID
			$refId = $group->getAttribute('id');
			$menuItem = $parser->evaluate("menu", $group)->item(0);
			$menuTitle = $parser->evaluate("title", $menuItem)->item(0);
			$literal = $parser->evaluate("literal", $menuTitle)->item(0);
			if (!is_null($literal))
			{
				$scope = $parser->evaluate("scope", $literal)->item(0)->nodeValue;
				$name = $parser->evaluate("name", $literal)->item(0)->nodeValue;
				$title = literal::get($scope, $name);
			}
			else
				$title = $menuTitle->nodeValue;
				
			$item = $toolbarItem->build($title, $menuItem->getAttribute('id'), $menuItem->getAttribute('class'), "domain")->get();
			
			// Get Navigations
			$navigations = $parser->evaluate("navigation", $menuItem);
			foreach ($navigations as $navigation)
				$this->setToolbarItemNavigation($parser, $tlbMenu, $item, $navigation, $refId);
			
			DOM::append($domainNav, $item);
			
			// Get Collection
			$menuCollection = $parser->evaluate("collection", $group)->item(0);
			if (is_null($menuCollection))
				continue;
				
			// Form Collection
			$colID = $parser->attr($menuCollection, "id");
			$colTitle = $parser->attr($menuCollection, "title");
			$colModule = $parser->evaluate("module", $menuCollection)->item(0)->nodeValue;
			$colModuleAction = $parser->evaluate("action", $menuCollection)->item(0)->nodeValue;
			$colStartup = $parser->evaluate("startup", $menuCollection)->item(0)->nodeValue;
			$collection = $this->ribbon->addCollection($colID, $colTitle, $colModule, $colModuleAction, $colStartup, TRUE);
			DOM::append($this->ribbon->getCollectionGroup(), $collection);

			// Add ribbon navigation
			$colRibbon = $parser->attr($menuCollection, "ribbon");
			$colType = $parser->attr($menuCollection, "type");
			RTRibbon::addNavigation($item, $colID, $colRibbon, $colType);
		}
	}
	
	/**
	 * Sets the navigation on the toolbar items.
	 * 
	 * @param	DOMParser	$parser
	 * 		The xml parser for the domain navigation xml file.
	 * 
	 * @param	toolbarItem	$tlbMenu
	 * 		The toolbarItem Object.
	 * 
	 * @param	DOMElement	$item
	 * 		The menu item where the navigation will be applied.
	 * 
	 * @param	DOMDocumentFragment	$navigation
	 * 		The fragment of file that indicates the navigation.
	 * 
	 * @param	string	$refId
	 * 		The ribbon's collection reference id.
	 * 
	 * @return	void
	 */
	private function setToolbarItemNavigation($parser, $tlbMenu, $item, $navigation, $refId)
	{
		
		// Get type of navigation (ribbon | web | static | dynamic)
		$type = $navigation->getAttribute("type");

		switch ($type)
		{
			case "ribbon":
				$nav = array();
				$nav['ribbon'] = $parser->evaluate("ribbon", $navigation)->item(0)->nodeValue;
				$nav['type'] = $parser->evaluate("type", $navigation)->item(0)->nodeValue;
				$nav['pinnable'] = $parser->evaluate("pinnable", $navigation)->item(0)->nodeValue;
				NavigatorProtocol::staticNav($item, $refId, $type = $nav);
				break;
			case "web":
				$href = $parser->evaluate("href", $navigation)->item(0)->nodeValue;
				$parts = explode("::", $href);
				$href = url::resolve($parts[0], $parts[1]);
				$target = $parser->evaluate("target", $navigation)->item(0)->nodeValue;
				NavigatorProtocol::web($item, $href, $target);
				break;
			case "static":
				$nav_ref = $parser->evaluate("ref", $navigation)->item(0)->nodeValue;
				$nav_targetcontainer = $parser->evaluate("targetcontainer", $navigation)->item(0)->nodeValue;
				$nav_targetgroup = $parser->evaluate("targetgroup", $navigation)->item(0)->nodeValue;
				$nav_navgroup = $parser->evaluate("navgroup", $navigation)->item(0)->nodeValue;
				$nav_display = $parser->evaluate("display", $navigation)->item(0)->nodeValue;
				NavigatorProtocol::staticNav($item, $nav_ref, $nav_targetcontainer, $nav_targetgroup, $nav_navgroup, $nav_display);
				break;
			case "dynamic":
				$action_id = $parser->evaluate("module", $navigation)->item(0)->nodeValue;
				$action_file = $parser->evaluate("action", $navigation)->item(0)->nodeValue;
				$action_holder = $parser->evaluate("holder", $navigation)->item(0)->nodeValue;
				ModuleProtocol::addActionGET($item, $action_id, $action_file, $action_holder);
				break;
		}
	}
}
//#section_end#
?>