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

importer::import("API", "Comm", "mail/phpmailer/phpmailer");
importer::import("API", "Comm", "mail/phpmailer/smtp");
importer::import("API", "Comm", "mail/phpmailer/pop3");
importer::import("DEV", "Profiler", "logger");

use \API\Comm\mail\phpmailer\phpmailer;
use \API\Comm\mail\phpmailer\POP3;
use \API\Comm\mail\phpmailer\SMTP;
use \DEV\Profiler\logger;

/**
 * Php mailer
 * 
 * This is a more simple class for mailing functions.
 * It supports sending mail from any pop3 and smtp mail server.
 * 
 * @version	0.2-3
 * @created	April 23, 2013, 13:45 (EEST)
 * @updated	September 25, 2015, 19:01 (EEST)
 * 
 * @deprecated	Use \API\Comm\mail\mailgun instead.
 */
class mailer extends phpmailer
{
	/**
	 * The mail's signature.
	 * 
	 * @type	string
	 */
	const CLASS_NAME = "drovio_mailer";
	/**
	 * The mail's version.
	 * 
	 * @type	string
	 */
	const CLASS_VERSION = "2.0.4";
	
	/**
	 * Recipient type 'to'.
	 * 
	 * @type	string
	 */
	const RCP_TO = "to";
	/**
	 * Recipient type 'rto'.
	 * 
	 * @type	string
	 */
	const RCP_RTO = "rto";
	/**
	 * Recipient type 'cc'.
	 * 
	 * @type	string
	 */
	const RCP_CC = "cc";
	/**
	 * Recipient type 'bcc'.
	 * 
	 * @type	string
	 */
	const RCP_BCC = "bcc";
	
	/**
	 * Flag variable, declares if an optionset is defined
	 * 
	 * @type	boolean
	 */
	private $optionSetDefined = FALSE;
	
	/**
	 * Create a mailer instance and initialize the class.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize mailer
		$this->initialize();
		
		// Construct phpmailer
		parent::__construct();
	}
	
	/**
	 * Initalizes mail options for mailer class
	 * 
	 * @return	void
	 */
	protected function initialize()
	{
		// Set Exception display
		$this->exceptions = FALSE;
		
		// Set SMTB debug mode
		$this->SMTPDebug = FALSE;
		$this->Debugoutput = "error_log";
		
		// Set common Mailer Options
		$this->XMailer = "Drovio - ".self::CLASS_NAME." Version : ".self::CLASS_VERSION ;
		$this->CharSet = 'UTF-8';
		
	}
	
	/**
	 * Sets all mail options as defined in the phpmailer.
	 * Must be set before sending the mail (obviously).
	 * 
	 * @param	array	$optionSetArray
	 * 		Array of mail options.
	 * 
	 * @return	void
	 */
	public function options($optionSetArray)
	{	
		// Check if options are valid
		if (!is_array($optionSetArray) || empty($optionSetArray))
			return;
		
		// Set options
		try
		{
			// Set send method
			$this->IsSMTP();
			
			// Hostname
			$this->Hostname = $this->getValue($this->Hostname, $optionSetArray['Hostname']);
 
			// Optional - Set Defaults
			$this->ContentType = $this->getValue($this->ContentType, $optionSetArray['ContentType']);
			$this->Encoding = $this->getValue($this->Encoding, $optionSetArray['Encoding']);
			$this->ReturnPath = $this->getValue($this->ReturnPath, $optionSetArray['ReturnPath']);
			$this->WordWrap = $this->getValue($this->WordWrap, $optionSetArray['WordWrap']);
			$this->ConfirmReadingTo = $this->getValue($this->ConfirmReadingTo, $optionSetArray['ConfirmReadingTo']);
			
  			// SMTP Properties
			$this->Host = $this->getValue($this->Host , $optionSetArray['SMTPHost']);
			$this->Port = $this->getValue($this->Port , $optionSetArray['SMTPPort']);
			$this->SMTPAuth = $this->getValue($this->SMTPAuth, $optionSetArray['SMTPAuth']);
			$this->AuthType = $this->getValue($this->AuthType , $optionSetArray['SMTPAuthType']);
			$this->Username = $this->getValue($this->Username , $optionSetArray['SMTPUsername']);
			$this->Password = $this->getValue($this->Password , $optionSetArray['SMTPPassword']);
			
			$this->SMTPSecure = $this->getValue($this->SMTPSecure , $optionSetArray['SMTPSecure']);
			$this->Realm = $this->getValue($this->Realm , $optionSetArray['SMTPRealm']);
			$this->Workstation = $this->getValue($this->Workstation , $optionSetArray['SMTPWorkstation']);
			$this->Timeout = $this->getValue($this->Timeout , $optionSetArray['SMTPTimeout']);
			
			// POP Properties
			$this->host = $this->getValue($this->host , $optionSetArray['POPhost']);
			$this->port = $this->getValue($this->port , $optionSetArray['POPport']);
			$this->tval = $this->getValue($this->tval , $optionSetArray['POPtval']);
			$this->username = $this->getValue($this->username , $optionSetArray['POPusername']);
			$this->password = $this->getValue($this->password , $optionSetArray['POPpassword']);
			
		}
		catch(Exception $e)
		{
			$this->optionSetDefined = FALSE;
			logger::log($e->getMessage(), logger::DEBUG);
			return;
		}
		
		// Set the flag for defined options
		$this->optionSetDefined = TRUE;
				
		// Pop initialazation, not needed for send
		// $pop = new POP3();
		// $pop->Authorise($this->host, $this->port, $this->timeout, "username", "password", 1);
	}
	
	/**
	 * Return the new value, if set.
	 * 
	 * @param	mixed	$currValue
	 * 		The current or the value without error.
	 * 
	 * @param	mixed	$newValue
	 * 		The new or the value that must be checked.
	 * 
	 * @return	mixed
	 * 		If the new value is set, return the new value, otherwise return the current value.
	 */
	private function getValue($currValue, $newValue)
	{
		return (isset($newValue) ? $newValue : $currValue);
	}
	
	/**
	 * Send the mail.
	 * It will throw an exception if the mail fails for some reason.
	 * 
	 * @param	string	$subject
	 * 		The mail's subject.
	 * 
	 * @param	array	$from
	 * 		An array to set the from argument. Format:
	 * 		$from['mail@example.com'] = 'Example Author'
	 * 
	 * @param	string	$to
	 * 		The receivers mail address.
	 * 
	 * @param	boolean	$auto
	 * 		If TRUE, also set Reply-To and Sender.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @throws	Exception
	 */
	public function send($subject = "", $from = array(), $to = "", $auto = TRUE)
	{
		if (!$this->optionSetDefined)
		{
			logger::log("Mailer was not initialized before sending this mail.", logger::DEBUG);
			return;
		}
	
		// Set subject
		$this->subject($subject);
		
		// Set mail from argument
		if (!empty($from))
		{
			// Key of first element
			$key = key($from);
			if ($this->ValidateAddress($key))
			{
				$fAddress = $key;
				$fName = $from[$key];
			}
			else
			{
				$fAddress = $from[$key];
				$fName = '';
			}	
		
			// 1 : Also set Reply-To and Sender
			$this->SetFrom($fAddress, $fName, $auto);
		}
		
		// Set mail to argument
		if (!empty($to))
		{
			$tName = '';
			$tAddress = $to;
			if (is_array($to))
			{
				$tAddress = $to[0];
				$tName = $to[1];			
			}
			$this->AddAddress($tAddress, $tName);
		}
		
		try
		{
			parent::Send();
		}
		catch (Exception $e) 
		{
			logger::log("Error sending mail with mailer: ".$e->getMessage(), logger::ERROR);
			throw new Exception('Error sending mail with mailer: '.$e->getMessage());
			return FALSE;
		}
		
		return TRUE;
	}

	/**
	 * Sets mail subject.
	 * 
	 * @param	string	$subject
	 * 		The mail's subject.
	 * 		It must not be empty.
	 * 
	 * @return	string
	 * 		The mail's subject.
	 */
	public function subject($subject = "")
	{
		// Set new subject (if not empty)
		$this->Subject = (!empty($subject) ? $subject : $this->Subject);
		
		// Return subject value
		return $this->Subject;
	}
	
	/**
	 * Set message priority
	 * 
	 * @param	integer	$priority
	 * 		Message priority from 1 to 5.
	 * 
	 * @return	void
	 */
	public function setPriority($priority)
	{
		$this->Priority = $priority;	
	}
	
	/**
	 * Add multiple recipients.
	 * 
	 * @param	string	$kind
	 * 		The type of recipients to add.
	 * 		Accepted values to | rto | cc | bcc.
	 * 
	 * @param	array	$addressesArray
	 * 		Array of arrays (address, name) or array of strings (address).
	 * 
	 * @return	void
	 */
	public function addMultipleRecipients($kind, $addressesArray)
	{
		// Check if it is a valid array
		if (!is_array($addressesArray))
			return FALSE;	
			
		// Add recipient addresses
		foreach ($addressesArray as $address)
		{
			$addr = array();		
			if (!is_array($address))
			{
				$addr[0] = $address;
				$addr[1] = '';
			}
			else
				$addr = $address;
		
			switch ($kind)
			{
				case self::RCP_TO:
					$this->AddAddress($addr[0], $addr[1]);
					break;
				case self::RCP_RTO:
					$this->AddReplyTo($addr[0], $addr[1]);
					break;
				case self::RCP_CC:
					$this->AddCC($addr[0], $addr[1]);
					break;
				case self::RCP_BCC:
					$this->AddBCC($addr[0], $addr[1]);
					break;
			}
		}
	}
	
	/**
	 * Alternative Message for non-html mail clients
	 * 
	 * @param	string	$altBody
	 * 		Non html, plain text body
	 * 
	 * @return	void
	 */
	public function setAltBody($altBody)
	{
		$this->AltBody = $altBody;
	}
}
//#section_end#
?>