<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\pageComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::ModuleProtocol");
importer::import("ESS", "Protocol", "client::NavigatorProtocol");
importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "literals::literal");
importer::import("API", "Developer", "profiler::logger");
importer::import("API", "Resources", "DOMParser"); 
importer::import("API", "Resources", "geoloc::region");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Security", "privileges");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "pageComponents::HTMLRibbon");
importer::import("UI", "Html", "pageComponents::ribbonComponents::ribbonPanel");
importer::import("UI", "Html", "pageComponents::toolbarComponents::toolbarItem");

use \ESS\Protocol\server\ModuleProtocol;
use \ESS\Protocol\client\NavigatorProtocol;
use \ESS\Protocol\client\environment\Url;
use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\literals\literal;
use \API\Developer\profiler\logger;
use \API\Resources\DOMParser;
use \API\Resources\geoloc\region;
use \API\Resources\geoloc\locale;
use \API\Security\privileges;
use \API\Security\account;
use \UI\Html\DOM;
use \UI\Html\pageComponents\HTMLRibbon;
use \UI\Html\pageComponents\ribbonComponents\ribbonPanel;
use \UI\Html\pageComponents\toolbarComponents\toolbarItem;

/**
 * HTML Navigation Toolbar
 * 
 * Builds the system's top navigation toolbar
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:02 (EEST)
 * @revised	August 17, 2013, 13:18 (EEST)
 */
class HTMLNavToolbar extends UIObjectPrototype
{
	/**
	 * The ribbon object
	 * 
	 * @type	HTMLRibbon
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
	 * @type	string
	 */
	private $domainNav;
	/**
	 * The navigation's domain
	 * 
	 * @type	string
	 */
	private $domain;
	
	/**
	 * Constructor Method
	 * 
	 * @param	string	$domain
	 * 		The navigation's domain
	 * 
	 * @return	void
	 */
	public function __construct($domain)
	{
		// Put your constructor method code here.
		$this->domain = $domain;
	}
	
	/**
	 * Builds the entire toolbar
	 * 
	 * @return	void
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
	 * Builds the ribbon
	 * 
	 * @return	void
	 */
	private function buildRibbon()
	{
		// Create Ribbon
		$this->ribbon = new HTMLRibbon();
		$ribbonElement = $this->ribbon->build()->get();
		DOM::append($this->get(), $ribbonElement);
		
		// Build user collection
		$this->ribbon->insertCollection("userNav", 154, "", TRUE);
		
		// Temp Region Collection
		$this->ribbon->insertCollection("regionNav", 8, "", TRUE);
	}
	
	/**
	 * Builds the user's navigation (whether the user is logged in or guest)
	 * 
	 * @return	void
	 */
	private function buildUserNavigation()
	{
		// Toolbar Navigation Builder
		$toolbarItem = new toolbarItem();
				
		// Profile Navigation
		$loggedIn = account::validate();
		if (!$loggedIn)
			$item = $toolbarItem->build(literal::dictionary("guest"), "", "userStarter", "global")->get();
		else
		{
			$fullName = account::getFirstname()." ".account::getLastname();
			$item = $toolbarItem->build($fullName, "", "userStarter", "global")->get();
			
			// Build Domain Nav
			$this->buildProfileTools();
			$this->buildDomainNavigation();
		}
		
		HTMLRibbon::addNavigation($item, "userNav", "float", "obedient toggle");
		DOM::append($this->userNav, $item);
		
		// Region Selector
		$this->buildRegionNav();
	}
	
	/**
	 * Builds the profile tools (for developers and other groups)
	 * 
	 * @return	void
	 */
	private function buildProfileTools()
	{
		$toolbarItem = new toolbarItem();
		
		// Tools Container
		$userTools = DOM::create("div", "", "userTools");
		DOM::append($this->userNav, $userTools);
		
		// Developer's tools
		// Insert Developer's Tools if user is a developer
		if (privileges::accountToGroup("DEVELOPER"))
		{
			$item = $toolbarItem->build("", "", "userDeveloper", "global")->get();
			HTMLRibbon::addNavigation($item, "devPanel", "inline", "obedient toggle");
			DOM::append($userTools, $item);
			
			// Build Developer's Collection Group
			$devCollection = $this->ribbon->insertCollection("devPanel", 106, "", logger::status());
		}
		/*
		// User's Messages
		$item = $toolbarItem->build("", "", "userMessages", "global")->get();
		HTMLRibbon::addNavigation($item, "userMessages", "float", "obedient toggle", FALSE);
		DOM::append($userTools, $item);
		
		// Build Messenger's Collection Group
		$message_collection = $this->ribbon->insertCollection("userMessages", 48);
		*/
	}
	
	/**
	 * Builds the region/language selector navigation.
	 * 
	 * @return	void
	 */
	private function buildRegionNav()
	{
		// Get Region info
		$regionInfo = region::getInfo();
		$localeInfo = locale::info();
		
		// Toolbar Navigation Builder
		$toolbarItem = new toolbarItem();
		
		// Create region image
		$snippet_img = DOM::create("img", "", "", "region");
		$src = Url::resource("/Library/Media/repository/geo/flags/".$regionInfo['imageName']);
		DOM::attr($snippet_img, "src", $src);
		DOM::attr($snippet_img, "title", $localeInfo['friendlyName']);
		DOM::attr($snippet_img, "alt", $localeInfo['friendlyName']);
		
		$item = $toolbarItem->build("", "", "regionSelector", "global", $snippet_img)->get();
		HTMLRibbon::addNavigation($item, "regionNav", "float", "obedient toggle");
		DOM::append($this->userNav, $item);
	}
	
	/**
	 * Builds the Domain Navigation
	 * 
	 * @return	void
	 */
	private function buildDomainNavigation()
	{		
		// Initialize Toolbar Menu
		$toolbarItem = new toolbarItem();
		$domainNav = $this->domainNav;
		$domain_parser = new DOMParser();
		try
		{
			$domain_parser->load("/System/Resources/Domains/".$this->domain."/navigation.xml", true);
		}
		catch (Exception $ex)
		{
			// If navigation information doesn't exist, exit
			return;
		}
		
		// Get Groups of Navigation
		$groups = $domain_parser->evaluate("//group");
		foreach ($groups as $group)
		{
			// Get Reference ID
			$refId = $group->getAttribute('id');
			
			// Load Menu Item
			$menuItem = $domain_parser->evaluate("menu", $group)->item(0);
			
			//__________ Get Title
			$menuTitle = $domain_parser->evaluate("title", $menuItem)->item(0);
			//__________ Get Resource Title (if any)
			$menuTitleRsrc = $domain_parser->evaluate("resource", $menuTitle);
			if ($menuTitleRsrc->length > 0)
			{
				$rsrc = $domain_parser->evaluate("rsrc", $menuTitleRsrc->item(0))->item(0)->nodeValue;
				$rsrc_id = $domain_parser->evaluate("id", $menuTitleRsrc->item(0))->item(0)->nodeValue;
				$title = literal::get($rsrc, $rsrc_id);
			}
			else
				$title = $menuTitle->nodeValue;
				
			$item = $toolbarItem->build($title, $menuItem->getAttribute('id'), $menuItem->getAttribute('class'), "domain")->get();
			
			// Get Navigations
			$navigations = $domain_parser->evaluate("navigation", $menuItem);
			foreach ($navigations as $navigation)
				$this-> setToolbarItemNavigation($domain_parser, $tlbMenu, $item, $navigation, $refId);
			
			DOM::append($domainNav, $item);
			
			// Load Collection
			$collection_item = $domain_parser->evaluate("collection", $group)->item(0);
			if (is_null($collection_item))
				continue;
				
			// Form Collection
			$collection = $this->ribbon->insertCollection($refId);
			DOM::append($this->ribbon->getCollectionGroup(), $collection);
			
			// Get Panels
			$panels = $domain_parser->evaluate("panel", $collection_item);
			foreach ($panels as $panel)
			{
				$container = $panel;
				// Login
				$ribbonPanel = new ribbonPanel();
				$collectionPanel = $ribbonPanel->build()->get();
				
				// Get Group and set as panel if exists
				$group = $domain_parser->evaluate("group", $container)->item(0);
				if (!is_null($group))
				{
					$collectionPanel = $ribbonPanel->build("", TRUE)->get();
					$container = $group;
				}
				
				// Get Panel Items
				$panel_items = $domain_parser->evaluate("panel_item", $container);
				foreach ($panel_items as $panel_item)
				{
					// Get Size
					$size = $panel_item->getAttribute("size");
					
					// Get Title
					$itemTitle = $domain_parser->evaluate("title", $panel_item)->item(0);
					//_____ Get Resource Title (if any)
					$itemTitleRsrc = $domain_parser->evaluate("resource", $itemTitle);
					if ($itemTitleRsrc->length > 0)
					{
						$rsrc = $domain_parser->evaluate("rsrc", $itemTitleRsrc->item(0))->item(0)->nodeValue;
						$rsrc_id = $domain_parser->evaluate("id", $itemTitleRsrc->item(0))->item(0)->nodeValue;
						$title = literal::get($rsrc, $rsrc_id);
					}
					else
						$title = $itemTitle->nodeValue;
						
					// Get Image
					$imgURL = $domain_parser->evaluate("image", $panel_item)->item(0)->nodeValue;
					
					// Insert Panel Item
					$ribbonPanel->insertPanelItem($size, $title, $imgURL);
					$this->ribbon->insertItem($collection, $collectionPanel);
					
					// Get Navigations
					$navigations = $domain_parser->evaluate("navigation", $panel_item);
					foreach ($navigations as $navigation)
						$this->setToolbarItemNavigation($domain_parser, $tlbMenu, $coll_panel->get_panel(), $navigation, $refId);
				}
			}
		}
	}
	
	/**
	 * Sets the navigation on the toolbar items.
	 * 
	 * @param	DOMParser	$domain_parser
	 * 		The parser that was used to parse the xml file
	 * 
	 * @param	toolbarItem	$tlbMenu
	 * 		The toolbarItem Object
	 * 
	 * @param	DOMElement	$item
	 * 		The menu item where the navigation will be applied
	 * 
	 * @param	DOMDocumentFragment	$navigation
	 * 		The fragment of file that indicates the navigation
	 * 
	 * @param	string	$refId
	 * 		The ribbon's collection reference id
	 * 
	 * @return	void
	 */
	private function setToolbarItemNavigation($domain_parser, $tlbMenu, $item, $navigation, $refId)
	{
		
		// Get type of navigation (ribbon | web | static | dynamic)
		$type = $navigation->getAttribute("type");

		switch ($type)
		{
			case "ribbon":
				$nav = array();
				$nav['ribbon'] = $domain_parser->evaluate("ribbon", $navigation)->item(0)->nodeValue;
				$nav['type'] = $domain_parser->evaluate("type", $navigation)->item(0)->nodeValue;
				$nav['pinnable'] = $domain_parser->evaluate("pinnable", $navigation)->item(0)->nodeValue;
				NavigatorProtocol::staticNav($item, $refId, $type = $nav);
				break;
			case "web":
				$href = $domain_parser->evaluate("href", $navigation)->item(0)->nodeValue;
				$parts = explode("::", $href);
				$href = Url::resolve($parts[0], $parts[1]);
				$target = $domain_parser->evaluate("target", $navigation)->item(0)->nodeValue;
				NavigatorProtocol::web($item, $href, $target);
				break;
			case "static":
				$nav_ref = $domain_parser->evaluate("ref", $navigation)->item(0)->nodeValue;
				$nav_targetcontainer = $domain_parser->evaluate("targetcontainer", $navigation)->item(0)->nodeValue;
				$nav_targetgroup = $domain_parser->evaluate("targetgroup", $navigation)->item(0)->nodeValue;
				$nav_navgroup = $domain_parser->evaluate("navgroup", $navigation)->item(0)->nodeValue;
				$nav_display = $domain_parser->evaluate("display", $navigation)->item(0)->nodeValue;
				NavigatorProtocol::staticNav($item, $nav_ref, $nav_targetcontainer, $nav_targetgroup, $nav_navgroup, $nav_display);
				break;
			case "dynamic":
				$action_id = $domain_parser->evaluate("policy", $navigation)->item(0)->nodeValue;
				$action_file = $domain_parser->evaluate("action", $navigation)->item(0)->nodeValue;
				$action_holder = $domain_parser->evaluate("holder", $navigation)->item(0)->nodeValue;
				ModuleProtocol::addActionGET($item, $action_id, $action_file, $action_holder);
				break;
		}
	}
}
//#section_end#
?>