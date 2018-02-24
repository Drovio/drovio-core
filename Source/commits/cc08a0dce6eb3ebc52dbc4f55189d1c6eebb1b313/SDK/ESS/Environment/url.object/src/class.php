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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/directory");

use \API\Resources\filesystem\directory;

/**
 * URL Helper and Resolver
 * 
 * This class is a helper class for handling urls.
 * It is used for resolving urls for resources and redirections.
 * 
 * @version	4.0-2
 * @created	October 23, 2014, 12:20 (BST)
 * @updated	December 8, 2015, 14:56 (GMT)
 */
class url
{
	/**
	 * The current host domain for the platform.
	 * 
	 * @type	string
	 */
	const HOST_DOMAIN = "drov";
	
	/**
	 * Creates and returns a url with parameters in url encoding.
	 * 
	 * @param	string	$url
	 * 		The base url.
	 * 
	 * @param	array	$parameters
	 * 		An associative array of parameters as key => value.
	 * 
	 * @return	string
	 * 		A well formed url.
	 */
	public static function get($url, $parameters = array())
	{
		// Build url query
		if (!empty($parameters))
			$url .= "?".http_build_query($parameters);
		
		// Return full url
		return rtrim($url, "?&");
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
	 * @param	boolean	$useOrigin
	 * 		Set TRUE to use origin value if exists.
	 * 		It is TRUE by default.
	 * 
	 * @return	string
	 * 		Returns the active navigation subdomain.
	 */
	public static function getSubDomain($useOrigin = TRUE)
	{
		// Get current url info
		$urlInfo = self::info();
		
		// Check if there is an origin value and use that
		if (isset($urlInfo['origin']) && $useOrigin)
			$urlInfo = self::info($urlInfo['origin']);
		
		// Return subdomain value
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
	 * @param	array	$params
	 * 		An array of parameters as key =&gt; value.
	 * 
	 * @param	string	$protocol
	 * 		The protocol to resolve the url to.
	 * 		Leave empty to decide based on the server request protocol.
	 * 		It is NULL by default.
	 * 
	 * @param	boolean	$explicit
	 * 		If TRUE, define explicitly the subdomain.
	 * 		If FALSE, www will be omitted.
	 * 
	 * @return	string
	 * 		The resolved url.
	 */
	public static function resolve($sub, $url = "/", $params = array(), $protocol = NULL, $explicit = FALSE)
	{
		// Compatibility check for params and protocol
		if (is_string($params))
		{
			$protocol = $params;
			$params = array();
		}
			
		// Set protocol
		if (empty($protocol))
		{
			$info = self::info();
			$protocol = $info['protocol'];
		}
		
		// Resolve URL according to system configuration
		$domain = self::getDomain();
		$resolved_URL = (self::isIP($domain) || ($sub == "www" && !$explicit) ? "" : $sub.".").$domain;
		if (!empty($url))
			$resolved_URL = directory::normalize($resolved_URL."/".$url);
		
		// Add protocol and return resolved url with parameters (if any)
		return url::get($protocol."://".$resolved_URL, $params);
	}
	
	/**
	 * Resolves a url to the default www domain, for resources.
	 * 
	 * @param	string	$url
	 * 		The resource's URL to be resolved.
	 * 
	 * @param	string	$protocol
	 * 		The protocol to resolve the url to.
	 * 		Leave empty to decide based on the server request protocol.
	 * 		It is NULL by default.
	 * 
	 * @return	string
	 * 		The resolved resource url.
	 * 
	 * @deprecated	Use resolve() instead. This function is no longer needed.
	 */
	public static function resource($url, $protocol = NULL)
	{
		// Resolve url for 'www'
		return self::resolve("www", $url, array(), $protocol);
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
	 * 		['origin'] = The host origin value, if exists.
	 */
	public static function info($url = "", $domain = self::HOST_DOMAIN)
	{
		// Initialize url info array
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
			$info['origin'] = $_SERVER['HTTP_ORIGIN'];
		}
		
		// Normalize url
		$url = str_replace($protocol."://", "", $url);
		$url = directory::normalize($url);
		$info['url'] = $protocol."://".$url;
		$info['protocol'] = $protocol;
		
		// Split for domain and subdomain
		list ($path, $params) = explode("?", $url);
		
		// Get all host parts
		$hostParts = explode("/", $path);
		$host = $hostParts[0];
		
		// Check if this is an ip or a domain
		if (self::isIP($host))
		{
			$sub = "";
			$domain = $host;
		}
		else
		{
			// Get host parts
			$parts = explode(".", $host);
			
			// Some part of this must be drov
			// If the first part is drov, then subdomain is www,
			// otherwise the first part is the subdomain.
			if ($parts[0] == $domain || count($parts) < 3)
				$sub = "www";
			else
			{
				$sub = $parts[0];
				unset($parts[0]);
			}

			// Set domain
			$domain = implode(".", $parts);
		}
		
		// Set info
		$info['sub'] = $sub;
		$info['domain'] = $domain;
		
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
	
	/**
	 * Check if the given url is in IP format.
	 * It includes checks for IPv4 and IPv6.
	 * 
	 * @param	string	$url
	 * 		The url to check.
	 * 
	 * @return	boolean
	 * 		True if url is an IP, false otherwise.
	 */
	public static function isIP($url)
	{
		// Check if given url is ip (v4 or v6)
		return self::isIPv4($url) || self::isIPv6($url);
	}
	
	/**
	 * Check if the given url is an IPv4.
	 * 
	 * @param	string	$url
	 * 		The url to be checked.
	 * 
	 * @return	boolean
	 * 		True if it's IPv4, false otherwise.
	 */
	private static function isIPv4($url)
	{
		return preg_match('/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $url);
	}
	
	/**
	 * Check if the given url is an IPv6.
	 * 
	 * @param	string	$url
	 * 		The url to be checked.
	 * 
	 * @return	boolean
	 * 		True if it's IPv6, false otherwise.
	 */
	private static function isIPv6($url)
	{
		// Not supported for the time being
		return FALSE;
	}
}
//#section_end#
?>