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
 * @version	2.0-1
 * @created	October 23, 2014, 14:20 (EEST)
 * @revised	November 17, 2014, 14:55 (EET)
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
		$urlInfo = self::info();
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
		$urlInfo = self::info();
		if (isset($urlInfo['referer']))
			$urlInfo = self::info($urlInfo['referer']);
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
	 * @param	string	$protocol
	 * 		The protocol to resolve the url to.
	 * 		Leave empty to decide based on the server request protocol.
	 * 
	 * @return	string
	 * 		The resolved url.
	 */
	public static function resolve($sub, $url = "/", $protocol = NULL)
	{
		// Set protocol
		if (empty($protocol))
		{
			$info = self::info();
			$protocol = $info['protocol'];
		}
		
		// Resolve URL according to system configuration
		$domain = self::getDomain();
		$resolved_URL = directory::normalize($domain."/".$url);
		
		// Add the subdomain if it's not www
		$resolved_URL = ($sub == "www" ? "" : $sub.".").$resolved_URL;
		return $protocol."://".$resolved_URL;
	}
	
	/**
	 * Resolves a resource's URL for reference to the resource's domain.
	 * 
	 * @param	string	$url
	 * 		The resource's URL to be resolved.
	 * 
	 * @param	string	$protocol
	 * 		The protocol to resolve the url to.
	 * 		The default protocol is "http".
	 * 
	 * @return	string
	 * 		The resolved resource url.
	 */
	public static function resource($url, $protocol = "http")
	{
		// Check server protocol
		$info = self::info();
		$serverProtocol = $info['protocol'];
		if ($protocol == "https" && $serverProtocol == "http")
			$protocol = "http";
		
		// Resolve url
		return self::resolve("www", $url, $protocol);
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
			$info = self::info();
			header('Location: '.$info['protocol'].'://'.$url);
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
	 * @param	string	$url
	 * 		The url to get the information from.
	 * 		If the url is empty, get the current request url.
	 * 
	 * @param	string	$domain
	 * 		The url domain.
	 * 		This is given to distinguish the subdomains on the front.
	 * 		For Redback framework the default value is 'redback'.
	 * 
	 * @return	array
	 * 		The url info as follows:
	 * 		['url'] = The full url page.
	 * 		['protocol'] = The server protocol.
	 * 		['sub'] = The navigation subdomain.
	 * 		['domain'] = The host domain.
	 * 		['params'] = An array of all url parameters by name and value.
	 * 		['referer'] = The referer value, if exists.
	 */
	public static function info($url = "", $domain = "redback")
	{
		$info = array();
		
		// Get protocol from given url
		$protocol = (strpos($url, "https") === 0 ? "https" : "http");
		
		// If no given url, get current
		if (empty($url))
		{
			// Get protocol
			$protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
			$url = $protocol."://".$_SERVER['HTTP_HOST']."/".$_SERVER['REQUEST_URI'];
			$info['referer'] = $_SERVER['HTTP_REFERER'];
		}
		
		// Normalize url
		$url = str_replace($protocol."://", "", $url);
		$url = directory::normalize($url);
		$info['url'] = $protocol."://".$url;
		
		// Split for domain and subdomain
		list ($path, $params) = explode("?", $url);
		
		// Get domain and subdomain
		$hostParts = explode("/", $path);
		$host = $hostParts[0];
		$parts = explode(".", $host);

		// Some part of this must be redback
		// If the first part is redback, then subdomain is www,
		// otherwise the first part is the subdomain.
		if ($parts[0] == $domain)
			$sub = "www";
		else
		{
			$sub = $parts[0];
			unset($parts[0]);
		}
		
		// Set info
		$info['protocol'] = $protocol;
		$info['sub'] = $sub;
		$info['domain'] = implode(".", $parts);
		
		// Get parameters
		$urlParams = explode("&", $params);
		foreach ($urlParams as $up)
		{
			$pparts = explode("=", $up);
			if (!empty($pparts) && !empty($pparts[0]))
				$info['params'][$pparts[0]] = $pparts[1];
		}
		
		return $info;
	}
}
//#section_end#
?>