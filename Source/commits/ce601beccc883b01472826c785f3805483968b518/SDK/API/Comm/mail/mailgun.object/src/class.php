<?php
//#section#[header]
// Namespace
namespace API\Comm\mail;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Comm
 * @namespace	\mail
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

/**
 * Mailgun client
 * 
 * This class connects to mailgun and sends email.
 * For more information see https://mailgun.com.
 * 
 * @version	0.1-1
 * @created	September 25, 2015, 2:03 (EEST)
 * @updated	September 25, 2015, 2:03 (EEST)
 */
class mailgun
{
	/**
	 * The to recipient identifier.
	 * 
	 * @type	string
	 */
	const RCP_TO = "to";
	/**
	 * The cc recipient identifier.
	 * 
	 * @type	string
	 */
	const RCP_CC = "cc";
	/**
	 * The bcc recipient identifier.
	 * 
	 * @type	string
	 */
	const RCP_BCC = "bcc";
	/**
	 * The text content identifier.
	 * 
	 * @type	string
	 */
	const CONTENT_TXT = "txt";
	/**
	 * The html content identifier.
	 * 
	 * @type	string
	 */
	const CONTENT_HTML = "html";
	
	/**
	 * The mailgun domain.
	 * 
	 * @type	string
	 */
	private $domain;
	/**
	 * The mailgun api key (api:key).
	 * 
	 * @type	string
	 */
	private $apikey;
	/**
	 * The mailgun api version.
	 * 
	 * @type	string
	 */
	private $version;
	
	/**
	 * Email tags.
	 * 
	 * @type	array
	 */
	protected $tags;
	/**
	 * The from address array.
	 * 
	 * @type	array
	 */
	protected $from;
	/**
	 * Mail recipients.
	 * 
	 * @type	array
	 */
	protected $recipients;
	
	/**
	 * Create a mailgun client instance.
	 * 
	 * @param	string	$domain
	 * 		The user domain for the mailgun.
	 * 
	 * @param	string	$apikey
	 * 		The api key in the format api:key.
	 * 
	 * @param	integer	$version
	 * 		The api version.
	 * 		Default value is 3.
	 * 
	 * @return	void
	 */
	public function __construct($domain, $apikey, $version = 3)
	{
		$this->domain = $domain;
		$this->version = 3;
		$this->apikey = $apikey;
	}
	
	/**
	 * Set the from address parameter.
	 * 
	 * @param	array	$from
	 * 		The from address in the following format:
	 * 		['name@example.com'] = "Name Example".
	 * 
	 * @return	mailgun
	 * 		The mailgun object.
	 */
	public function setFrom($from)
	{
		// Split the address
		$address = key($from);
		$name = $from[$address];
		
		// Set from email address
		$this->from = $name." <".$address.">";
		
		// Return mailgun object
		return $this;
	}
	
	/**
	 * Add a mail recipient.
	 * The previous recipient will be replaced.
	 * 
	 * @param	string	$address
	 * 		The recipient address.
	 * 
	 * @param	string	$type
	 * 		The recipient type.
	 * 		See class constants.
	 * 
	 * @return	mailgun
	 * 		The mailgun object.
	 */
	public function addRecipient($address, $type = self::RCP_TO)
	{
		$this->recipients[$type] = $address;
		return $this;
	}
	
	/**
	 * Set email tags, using mailgun parameters.
	 * 
	 * @param	array	$tags
	 * 		An array of o:tag for the email.
	 * 
	 * @return	mailgun
	 * 		The mailgun object.
	 */
	public function setTags($tags)
	{
		$this->tags = $tags;
		return $this;
	}
	
	/**
	 * Send the email using mailgun api.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	string	$content
	 * 		The mail content.
	 * 
	 * @param	string	$type
	 * 		Indicates whether the content is text or html.
	 * 		It is in text mode by default.
	 * 		See class constants.
	 * 
	 * @return	mixed
	 * 		The api response.
	 */
	public function send($subject, $content, $type = self::CONTENT_TXT)
	{
		// Create curl url
		$url = "https://api.mailgun.net/v".$this->version."/".$this->domain."/messages";
		
		// Add parameters
		$parameters = array();
		$parameters['from'] = $this->from;
		
		// Set recipients
		foreach ($this->recipients as $type => $address)
			$parameters[$type] = $address;
		
		// Set subject and text
		$parameters['subject'] = $subject;
		
		if ($type == self::CONTENT_TXT)
			$parameters['text'] = $content;
		else
			$parameters['html'] = $content;
		
		// Add tags
		$parameters['o:tag'] = $this->tags;
		
		// Perform curl
		return $this->curl($url, $parameters);
	}
	
	/**
	 * Make the cURL request to mailgun api.
	 * 
	 * @param	string	$url
	 * 		The url value.
	 * 
	 * @param	array	$parameters
	 * 		The post parameters.
	 * 
	 * @return	mixed
	 * 		The cURL response.
	 */
	private function curl($url, $parameters = array())
	{
		// Initialize cURL
		$curl = curl_init();
		
		// Set options
		$options = array();
		$options[CURLOPT_RETURNTRANSFER] = 1;
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_USERPWD] = $this->apikey;
		
		// Set post parameters
		$options[CURLOPT_POST] = 1;
		$options[CURLOPT_POSTFIELDS] = $parameters;
		
		// Set options array
		curl_setopt_array($curl, $options);
		
		// Execute and close url
		$response = curl_exec($curl);
		curl_close($curl);
		
		// Return response
		return $response;
	}
}
//#section_end#
?>