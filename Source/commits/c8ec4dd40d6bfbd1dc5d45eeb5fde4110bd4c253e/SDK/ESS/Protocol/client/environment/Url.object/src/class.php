<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client\environment;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client\environment
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
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
 * @revised	October 23, 2013, 12:06 (EEST)
 */
class Url
{
	/**
	 * The system's global domain
	 * 
	 * @type	string
	 */
	const DOMAIN = ".redback.gr";
	
	/**
	 * Contains the subdomain paths
	 * 
	 * @type	array
	 */
	private static $subdomainPaths;
	
	/**
	 * Resolves a given URL and transforms it if necessary to a developer server's URL
	 * 
	 * @param	string	$sub
	 * 		The subdomain.
	 * 
	 * @param	string	$url
	 * 		The URL
	 * 
	 * @param	boolean	$https
	 * 		Whether the URL is secure
	 * 
	 * @param	boolean	$full
	 * 		Indicates whether the return url will full (absolute) and not relative (if applicable).
	 * 
	 * @return	void
	 */
	public static function resolve($sub, $url = "/", $https = FALSE, $full = FALSE)
	{
		// Initialize
		$resolved_URL = "";
		
		// Check subdomain
		$subDomain = self::getSubDomain();
		if (empty($subDomain))
		{
			// Search for stored domain_paths
			if (isset(self::$subdomainPaths[$sub]))
				$resolved_URL = self::$subdomainPaths[$sub].$url;
			else
			{
				// Get domain paths as an array
				$dbc = new interDbConnection();
				$dbq = new dbQuery("573078142", "units.domains");
				$result = $dbc->execute($dbq);
				
				// Update self domain paths
				self::$subdomainPaths = $dbc->toArray($result, "name", "path");

				// Set resolved URL
				$resolved_URL = self::$subdomainPaths[$sub].$url;
			}
			
			if ($full)
			{
				$host = $_SERVER['HTTP_HOST'];
				$preNormal = $host."/".$resolved_URL;
				$normalURL = directory::normalize($preNormal);
				$resolved_URL = "http".($https ? "s" : "")."://".$normalURL;
			}
		}
		else
		{
			$preNormal = $sub.self::DOMAIN."/".$url;
			$normalURL = directory::normalize($preNormal);
			$resolved_URL = "http".($https ? "s" : "")."://".$normalURL;
		}
		
		$resolved_URL = $resolved_URL;
		return $resolved_URL;
	}
	
	/**
	 * Resolves a resource's URL for the needs of the testing/developer server
	 * 
	 * @param	string	$url
	 * 		The resource's URL
	 * 
	 * @return	void
	 */
	public static function resource($url)
	{
		// Resolve URL according to system configuration
		$resolved_URL = "";
		$subDomain = self::getSubDomain();
		if (!empty($subDomain))
		{
			$preNormal = self::DOMAIN."/".$url;
			$normalURL = directory::normalize($preNormal);
			$resolved_URL .= "http://www".$normalURL;
		}
		else
		{
			$resolved_URL = "/".$url;
			$resolved_URL = directory::normalize($resolved_URL);
		}
		
		return $resolved_URL;
	}
	
	/**
	 * Check for subdomain redirection
	 * 
	 * @param	string	$domain
	 * 		The domain to check
	 * 
	 * @param	string	$domainPath
	 * 		The domain's server path
	 * 
	 * @return	void
	 */
	public static function checkSubdomain($domain, $domainPath)
	{
		$pos = strrpos($_SERVER['REQUEST_URI'], $domainPath);
		$subDomain = self::getSubDomain();
		if (!empty($subDomain) && !($pos === false) && ($pos == 0) && $domain != "www")
		{
			$request_uri = str_replace($domainPath, "", $_SERVER['REQUEST_URI']);
			header('Location: http://'.$domain.self::DOMAIN.$request_uri);
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Get system domain
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function getDomain()
	{
		$subDomain = self::getSubDomain();
		return (!empty($subDomain) ? self::DOMAIN : "");
	}
	
	/**
	 * Gets the current url subdomain.
	 * 
	 * @return	string
	 * 		Returns empty if its the tester subdomain or the active subdomain.
	 */
	public static function getSubDomain()
	{
		$host = $_SERVER['HTTP_HOST'];
		$parts = explode(".", $host);
		
		// Check if host is ([subdomain].redback.gr or just redback.gr)
		if (count($parts) == 3)
		{
			$sub = $parts[0];
			$domain = $parts[1];
			$type = $parts[2];
			
			if ($domain == "redback")
				return $sub;
			else
				return "";
		}
		else
		{
			$domain = $parts[0];
			$type = $parts[1];
			if ($domain == "redback" && ($type == "gr" | $type == "com"))
				return "www";
		}
		
		return "";
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
	 * 		{description}
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
}
//#section_end#
?>