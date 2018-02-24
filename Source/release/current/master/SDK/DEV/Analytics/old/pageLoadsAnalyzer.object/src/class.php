<?php
//#section#[header]
// Namespace
namespace DEV\Analytics\old;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "storage::session");

use \API\Resources\storage\session;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;

class pageLoadsAnalyzer
{
	/**
	 * The path of log file
	 * 
	 * @type	string
	 */
	const VISITS_FOLDER = "/analytics/visits/";
	
	const CACHE_NONE = 'none';
	
	const CACHE_INSTANCE = 'instance';
	
	const CACHE_SESSION = 'session';
	
	private $cacheType;
	
	private $parser;
	
	const FILTER_FULL = 1;
	
	const FILTER_EMPTY = 2;	
	
	public function __construct($cacheType = self::CACHE_NONE)
	{
		$this->cacheType = $cacheType;
	}
	
	public function initData($date = '')
	{
		if($this->cacheType == self::CACHE_NONE)
			return FALSE;
			
		// Temporary
		$this->parser = $this->loadFile($date);
	}
	
	public function initDataRange($dateStart = '', $dateEnd = '')
	{
		if($this->cacheType == self::CACHE_NONE)
			return FALSE;
			
		// Temporary
		return $this->loadData($dateStart);
	}

	
	public function getData($date = "", $dateEnd = '', $filter = 0)
	{		
		if($this->cacheType == self::CACHE_NONE)
		{	
			$parser = $this->loadFile($date);
		}
		else
		{
			// Check date consistency
			// if not reInit data
			$parser = $this->parser;
		}
		
		// Get root
		$root = $parser->evaluate("//visits")->item(0);
		$entries = $parser->evaluate("//entry", $root);
		
		$data = array();
		foreach($entries as $entry)
		{
			$entr = array();
			
			$entr["time"] = $parser->attr($entry, "time");
			$entr["browser"] = $parser->evaluate("client/browser", $entry)->item(0)->nodeValue;
			$empty = empty($entr["browser"]);			
			$entr["ip"] = $parser->evaluate("client/ip", $entry)->item(0)->nodeValue;			
			$entr["domain"] = $parser->evaluate("client/domain", $entry)->item(0)->nodeValue;
			$empty = $empty && empty($entr["domain"]);		
			$uri = $parser->evaluate("client/uri", $entry)->item(0)->nodeValue;
			$query = $parser->evaluate("client/qString", $entry)->item(0)->nodeValue;
			$entr["uri"] = $uri.(empty($query) ? "" : "?").$query;
			$empty = $empty && empty($entr["uri"]);
			$entr["moduleID"] = $parser->evaluate("action/moduleID", $entry)->item(0)->nodeValue;
			$entr["static"] = $parser->evaluate("action/static", $entry)->item(0)->nodeValue;
			$entr["dDesc"] = $parser->evaluate("action/dDesc", $entry)->item(0)->nodeValue;
			$entr["dPath"] = $parser->evaluate("action/ndPath", $entry)->item(0)->nodeValue;
						
			if($filter == self::FILTER_FULL)
				if(!$empty)
					$data[] = $entr;
			else if($filter == self::FILTER_EMPTY)
				if($empty)
					$data[] = $entr;
			else			
				$data[] = $entr;
		}
		
		return $data;
	}
	
	
	private function loadFile($date = '')
	{
		if (empty($date))
			$fileName = self::getFileName();
		else
			$fileName = "pgVisits_".$date.".xml";
		
		$filePath = paths::getSysDynRsrcPath().self::VISITS_FOLDER;
		if (!file_exists(systemRoot.$filePath.$fileName))
			return FALSE;
	
		// Load log file
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		return $parser;
	}
	
	/**
	 * Gets the log filename for the day.
	 * 
	 * @return	string
	 * 		{description}
	 */
	private function getFileName()
	{
		return "pgVisits_".date("Y-m-d").".xml";
	}
}
//#section_end#
?>