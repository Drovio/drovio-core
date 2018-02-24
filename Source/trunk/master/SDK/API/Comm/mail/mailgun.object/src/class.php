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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * Mailgun client
 * 
 * This class connects to mailgun SMTP API and sends emails.
 * For more information see https://mailgun.com.
 * This version supports the minimum functionality including:
 * - set from address and recipients
 * - set tags
 * - add attachments
 * - send text and html messages.
 * 
 * @version	3.0-1
 * @created	September 25, 2015, 0:03 (BST)
 * @updated	December 8, 2015, 17:46 (GMT)
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
	 * The reply-to address.
	 * 
	 * @type	string
	 */
	protected $replyTo;
	
	/**
	 * Mail recipients.
	 * 
	 * @type	array
	 */
	protected $recipients;
	
	/**
	 * The email attachment list.
	 * 
	 * @type	array
	 */
	protected $attachments = array();
	
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
	 * @param	mixed	$from
	 * 		The from address can by just the email address or an array in the following format:
	 * 		['name@example.com'] = "Name Example".
	 * 
	 * @return	mailgun
	 * 		The mailgun object.
	 */
	public function setFrom($from)
	{
		// Check the type of the from attribute
		if (is_array($from))
		{
			// It's an array and we have to split the fields
			$address = key($from);
			$name = $from[$address];
		}
		else
		{
			// It's only the address so we have to get the name
			$address = $from;
			$name = trim(explode("@", $address, 2)[0]);
		}
		
		// Set from email address
		$this->from = $name." <".$address.">";
		
		// Return mailgun object
		return $this;
	}
	
	/**
	 * Set the reply-to address for the message.
	 * 
	 * @param	string	$replyTo
	 * 		The reply-to mail address.
	 * 
	 * @return	mailgun
	 * 		The mailgun object.
	 */
	public function setReplyTo($replyTo)
	{
		// Set reply to email address
		$this->replyTo = $replyTo;
		
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
		$this->recipients[$type][] = $address;
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
	 * Add a file attachment to the email.
	 * 
	 * @param	string	$filePath
	 * 		The full file path for the attachment.
	 * 
	 * @return	mailgun
	 * 		The mailgun object.
	 */
	public function addAttachment($filePath)
	{
		$this->attachments[] = $filePath;
		return $this;
	}
	
	/**
	 * Send the email using mailgun api.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	string	$textContent
	 * 		The mail text content.
	 * 		Leave empty to skip.
	 * 		It is empty by default.
	 * 
	 * @param	string	$htmlContent
	 * 		The mail html content.
	 * 		Leave empty to skip.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		The api response.
	 */
	public function send($subject, $textContent = "", $htmlContent = "")
	{
		// Create curl url
		$url = "https://api.mailgun.net/v".$this->version."/".$this->domain."/messages";
		
		// Add parameters
		$parameters = array();
		$parameters['from'] = $this->from;
		
		// Set reply-to (if any)
		$parameters['h:Reply-To'] = $this->replyTo;
		
		// Set recipients
		foreach ($this->recipients as $type => $addresses)
			foreach ($addresses as $i => $address)
				$parameters[$type."[".$i."]"] = $address;
		
		// Set subject and text
		$parameters['subject'] = $subject;
		if (!empty($textContent))
			$parameters['text'] = $textContent;
		if (!empty($htmlContent))
			$parameters['html'] = $htmlContent;
		
		// Add tags
		if (!empty($this->tags))
			$parameters['o:tag'] = $this->tags;
		
		// Add attachments
		foreach ($this->attachments as $i => $filePath)
			$parameters["attachment[$i]"] = curl_file_create($filePath);

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