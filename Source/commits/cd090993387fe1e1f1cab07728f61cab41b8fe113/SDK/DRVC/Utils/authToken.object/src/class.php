<?php
//#section#[header]
// Namespace
namespace DRVC\Utils;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DRVC
 * @package	Utils
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * Drovio Authentication Tokens
 * 
 * Create and verify authentication tokens.
 * 
 * @version	0.1-1
 * @created	November 14, 2015, 19:29 (GMT)
 * @updated	November 14, 2015, 19:29 (GMT)
 */
class authToken
{
	/**
	 * Generate an authentication token.
	 * 
	 * @param	array	$payload
	 * 		The payload in the form of array.
	 * 
	 * @param	string	$key
	 * 		The key to sign the token.
	 * 
	 * @return	string
	 * 		The token generated.
	 * 		False if the payload is not valid (not array or empty).
	 */
	public static function generate($payload, $key)
	{
		// Check if payload is array
		if (!is_array($payload))
			return FALSE;
		
		// Check if payload or key is empty
		if (empty($payload) || empty($key))
			return FALSE;
		
		// Encode payload to json
		$payloadJSON = json_encode($payload, JSON_FORCE_OBJECT);
		
		// Encode
		$payloadJSON_encoded = base64_encode($payloadJSON);
		
		// Create signature
		$signature = hash_hmac($algo = "sha256", $data = $payloadJSON_encoded, $key);
		
		// Return combined key
		return $payloadJSON_encoded.".".$signature;
	}
	
	/**
	 * Get the payload from the token.
	 * 
	 * @param	string	$token
	 * 		The authentication token.
	 * 
	 * @param	boolean	$jsonDecode
	 * 		Whether to decode the payload from json to array.
	 * 		It is TRUE by default.
	 * 
	 * @return	mixed
	 * 		Array or json string according to second parameter.
	 */
	public static function getPayload($token, $jsonDecode = TRUE)
	{
		// Split parts
		$parts = explode(".", $token);
		
		// Decode first part
		$payloadJSON = base64_decode($parts[0]);
		
		// Choose to decode or not
		if ($jsonDecode)
			return json_decode($payloadJSON, TRUE);
		
		// Return json
		return $payloadJSON;
	}
	
	/**
	 * Verify the given token with the given signature key.
	 * 
	 * @param	string	$token
	 * 		The authentication token.
	 * 
	 * @param	string	$key
	 * 		The signature secret key.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	public static function verify($token, $key)
	{
		// Check values
		if (empty($token) || empty($key))
			return FALSE;
		
		// Split parts
		$parts = explode(".", $token);
		
		// Get payload and signature
		$payloadJSON_encoded = $parts[0];
		$signature = $parts[1];
		
		// Generate signature to verify
		$signatureGenerated = hash_hmac($algo = "sha256", $data = $payloadJSON_encoded, $key);
		return ($signature === $signatureGenerated);
	}
}
//#section_end#
?>