<?php
//#section#[header]
// Namespace
namespace BSS\WebDocs;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("API", "Profile", "tester");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Geoloc", "locale");

use \API\Profile\tester as profileTester;
use \API\Profile\team;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \API\Geoloc\locale;

class wDoc
{
	private $directory = "";
	private $docName = "";
	
	public function __construct($directory, $name = "")
	{
		$this->directory = $directory;
		$this->docName = $name;
	}
	
	public function create($name, $context = "", $locale = NULL)
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
		
		// Get doc path and create as empty
		$this->docName = $name;
		$docPath = $this->getDocPath($locale);
		fileManager::create(systemRoot.$docPath, "", TRUE);
		
		// Create html file
		$parser = new DOMParser();
		$container = $parser->create("div", "", "", "wDoc");
		$parser->append($container);
		
		// Create doc Header
		$header = $parser->create("div", "", "", "header");
		$parser->append($container, $header);
		
		// Create doc body container
		$body = $parser->create("div", "", "", "body");
		$parser->append($container, $body);
		$parser->innerHTML($body, $context);
		
		// Create footer container
		$footer = $parser->create("div", "", "", "footer");
		$parser->append($container, $footer);
		
		// Save file
		$contextHTML = $parser->getHTML(TRUE);
		fileManager::create(systemRoot.$path.$fileName, $contextHTML, TRUE);
	}
	
	public function update($context, $locale = NULL)
	{
	}
	
	public function get($locale = NULL)
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
			
		// Check if document exists in given locale and then get default
		$docPath = $this->getDocPath($locale);
		if (!file_exists(systemRoot.$docPath))
		{
			$locale = locale::getDefault();
			$docPath = $this->getDocPath($locale);
		}
		
		// Load document content
		$parser = new DOMParser();
		try
		{
			$parser->load($docPath);
		}
		catch (Exception $ex)
		{
			return "";
		}
		
		// Parse document and get body
	}
	
	public function load($locale = NULL)
	{
		// Get current locale if not given
		if (empty($locale))
			$locale = locale::get();
			
		// Check if document exists in given locale and then get default
		$docPath = $this->getDocPath($locale);
		if (!file_exists(systemRoot.$docPath))
		{
			$locale = locale::getDefault();
			$docPath = $this->getDocPath($locale);
		}
		
		// Get the whole document
		return fileManager::get(systemRoot.$docPath);
	}
	
	private function parseContents()
	{
		// Load Document and parse contents
	}
	
	private function getDocPath($locale)
	{
		// Get Web Docs Service folder
		$serviceFolder = profileTester::getTrunk();
		
		// Form document path
		$path = $serviceFolder."/".$this->directory."/".$this->docName.".wDoc/";
		
		// Set filename with the given locale
		$fileName = $this->docName.".". $locale.".html";
		
		// Return full path (no systemRoot)
		return $path.$fileName;
	}
}
//#section_end#
?>