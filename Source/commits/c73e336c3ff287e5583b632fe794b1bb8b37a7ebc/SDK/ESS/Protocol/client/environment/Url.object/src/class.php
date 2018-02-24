<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client\environment;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client\environment
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "filesystem::directory");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\filesystem\directory;

/**
 * URL Resolver
 * 
 * Resolves urls according to static and subdomain
 * 
 * @version	{empty}
 * @created	March 7, 2013, 11:39 (EET)
 * @revised	April 11, 2014, 21:35 (EEST)
 */
class Url
{
	/**
	 * Resolves a given URL and transforms it if necessary to a developer server's URL
	 * 
	 * @param	string	$sub
	 * 		The subdomain.
	 * 
	 * @param	string	$url
	 * 		The URL
	 * 
	 * @return	string
	 * 		The resolved url.
	 */
	public static function resolve($sub, $url = "/")
	{
		// Resolve URL according to system configuration
		$domain = self::getDomain();
		$resolved_URL = directory::normalize($domain."/".$url);
		
		// Add the subdomain if it's not www
		$resolved_URL = ($sub == "www" ? "" : $sub.".").$resolved_URL;
		return "http://".$resolved_URL;
	}
	
	/**
	 * Resolves a resource's URL for the needs of the testing/developer server
	 * 
	 * @param	string	$url
	 * 		The resource's URL
	 * 
	 * @return	string
	 * 		The resource url.
	 */
	public static function resource($url)
	{
		// Resolve URL according to system configuration
		$domain = self::getDomain();
		$resolved_URL = directory::normalize($domain."/".$url);
		return "http://".$resolved_URL;
	}
	
	/**
	 * Check for subdomain redirection
	 * 
	 * @param	string	$sub
	 * 		The subdomain.
	 * 
	 * @param	string	$subPath
	 * 		The inner subdomain path.
	 * 
	 * @return	boolean
	 * 		True if subdomain is ok, false otherwise.
	 */
	public static function checkSubdomain($sub, $subPath)
	{
		$pos = strrpos($_SERVER['REQUEST_URI'], $subPath);
		$domain = self::getDomain();
		if (!($pos === false) && ($pos == 0) && $subdomain != "www")
		{
			$request_uri = str_replace($subPath, "", $_SERVER['REQUEST_URI']);
			$url = directory::normalize($sub.".".$domain.$request_uri);
			header('Location: http://'.$url);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Get system domain.
	 * 
	 * @return	string
	 * 		The host domain.
	 */
	public static function getDomain()
	{
		$urlInfo = self::urlInfo();
		return $urlInfo['domain'];
	}
	
	/**
	 * Gets the current url subdomain.
	 * 
	 * @return	string
	 * 		Returns empty if its the tester subdomain or the active subdomain.
	 */
	public static function getSubDomain()
	{
		$urlInfo = self::urlInfo();
		return $urlInfo['sub'];
	}
	
	/**
	 * Decides whether it is secure to redirect to a given URL outside Redback
	 * 
	 * @param	string	$url
	 * 		The url to redirect.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function redirect($url)
	{
	}
	
	/**
	 * Creates and returns a url with parameters in url encoding.
	 * 
	 * @param	string	$url
	 * 		The base url.
	 * 
	 * @param	array	$params
	 * 		An array of parameters as key => value.
	 * 
	 * @return	string
	 * 		A well formed url.
	 */
	public static function get($url, $params = array())
	{
		$result = $url;
		
		if (count($params) > 0)
			$result .= "?";
		
		// Normalize Parameters
		$arguments = array();
		foreach ($params as $key => $value)
			$arguments[] = $key."=". urlencode($value);
		
		// Merge Parameters
		$result .= implode("&", $arguments);
		
		// Normalize and return
		return $result;
	}
	
	/**
	 * Gets the info of the url in an array.
	 * 
	 * @return	array
	 * 		An array that contains the 'sub' and the 'domain'.
	 */
	private static function urlInfo()
	{
		// Get host and split into parts
		$origin = (empty($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_ORIGIN']);
		$origin = str_replace("http://", "", $origin);
		$origin = str_replace("https://", "", $origin);
		$parts = explode(".", $origin);
		
		// Some part of this must be redback
		// If the first part is redback, then subdomain is www,
		// otherwise the first part is the subdomain.
		if ($parts[0] == "redback")
			$sub = "www";
		else
		{
			$sub = $parts[0];
			unset($parts[0]);
		}
		
		// Get the rest as domain
		$domain = implode(".", $parts);
		
		// Create url info and return
		$info = array();
		$info['sub'] = $sub;
		$info['domain'] = $domain;
		return $info;
	}
}
//#section_end#
?>