<?php
//#section#[header]
// Namespace
namespace ESS\Environment;

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
 * @package	Environment
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::directory");

use \API\Resources\filesystem\directory;

/**
 * URL Helper and Resolver
 * 
 * This class is a helper class for handling urls.
 * It is used for resolving urls for resources and redirections.
 * 
 * @version	0.1-2
 * @created	October 23, 2014, 14:20 (EEST)
 * @revised	October 23, 2014, 14:21 (EEST)
 */
class url
{
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
	 * Get current domain.
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
	 * Gets the current navigation subdomain.
	 * 
	 * @return	string
	 * 		Returns the active navigation subdomain.
	 */
	public static function getSubDomain()
	{
		$urlInfo = self::urlInfo();
		return $urlInfo['sub'];
	}
	
	/**
	 * Resolves a given URL given a subdomain and a page url in the framework.
	 * 
	 * @param	string	$sub
	 * 		The subdomain name.
	 * 		It must be a valid name.
	 * 
	 * @param	string	$url
	 * 		The page url.
	 * 		By default it's the root url ("/").
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
	 * Resolves a resource's URL for reference to the resource's domain.
	 * 
	 * @param	string	$url
	 * 		The resource's URL to be resolved.
	 * 
	 * @return	string
	 * 		The resolved resource url.
	 */
	public static function resource($url)
	{
		return self::resolve("www", $url);
	}
	
	/**
	 * Checks if the user is in the desired subdomain with the right way.
	 * The user is redirected in the right subdomain with the right url.
	 * 
	 * It doesn't work in secure mode.
	 * 
	 * @param	string	$sub
	 * 		The subdomain to check for.
	 * 
	 * @param	string	$subPath
	 * 		The inner subdomain path to check for.
	 * 
	 * @return	void
	 */
	public static function checkSubdomain($sub, $subPath)
	{
		// Check secure mode
		if (importer::secure())
			return FALSE;
			
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
	 * Resolves the given url and returns a redirect url after check that the url is valid.
	 * 
	 * @param	string	$url
	 * 		The url to redirect.
	 * 
	 * @return	string
	 * 		The redirected url.
	 */
	public static function redirect($url)
	{
		return $url;
	}
	
	/**
	 * Gets the info of the url in an array.
	 * 
	 * @return	array
	 * 		The url info as follows:
	 * 		['sub'] = The navigation subdomain.
	 * 		['domain'] = The host domain.
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