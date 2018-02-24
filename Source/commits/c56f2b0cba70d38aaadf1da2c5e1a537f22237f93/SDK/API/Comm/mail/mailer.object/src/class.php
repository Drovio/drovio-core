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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Content", "settings::configSettings");
importer::import("API", "Comm", "phpmailer::phpmailer");
importer::import("API", "Comm", "phpmailer::smtp");
importer::import("API", "Comm", "phpmailer::pop3");
importer::import("DEV", "Profiler", "logger");

use \DEV\Profiler\logger;
use \API\Content\settings\configSettings;
use \API\Comm\phpmailer\phpmailer;
use \API\Comm\phpmailer\pop3;
use \API\Comm\phpmailer\smtp;

/**
 * mailer
 * 
 * Manages all mail actions from redback subsystems
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:45 (EEST)
 * @revised	April 23, 2013, 13:45 (EEST)
 */
class mailer extends PHPMailer
{
	/**
	 * Redback Mail visual to public class name
	 * 
	 * @type	string
	 */
	const CLASS_NAME = "rbMailer";
	/**
	 * Mailer class visual to public version
	 * 
	 * @type	string
	 */
	const CLASS_VERSION = "2.0.4";
	
	/**
	 * Flaf variable, declares if an optionset is defined
	 * 
	 * @type	bool
	 */
	private $optionSetDefined = FALSE;
	
	/**
	 * Initalizes mail options for mailer class
	 * 
	 * @return	void
	 */
	protected function intialize()
	{
		//Set Exception display
		$this->exceptions = FALSE;
		//enables SMTP debug information (for testing)
		$this->SMTPDebug = FALSE;
		$this->Debugoutput = "error_log";
		
		//For pop, may need object
		//$this->do_debug = 2;
			
		//Set common Mailer Options
		$this->XMailer = "Redback - ".self::CLASS_NAME." Version : ".self::CLASS_VERSION ;
		$this->CharSet = 'UTF-8';
		
	}
	
	/**
	 * Configures mail class given an array of options
	 * 
	 * @param	array	$optionSetArray
	 * 		Array of mail options
	 * 
	 * @return	void
	 */
	public function options($optionSetArray)
	{	
		if(!is_array($optionSetArray) || empty($optionSetArray))
			return;
		try
		{
			//Set send method
			$this->IsSMTP();  			
			//IsMail() 
			//IsSendmail() 
			//IsQmail() 
			
			//On Email
			//Requireed - Stop Script
			$this->Hostname = $this->getValue($this->Hostname, $optionSetArray['Hostname']);
			
 
			//Optional - Set Defaults
			$this->ContentType = $this->getValue($this->ContentType, $optionSetArray['ContentType']);
			$this->Encoding= $this->getValue($this->Encoding, $optionSetArray['Encoding']);
			$this->ReturnPath= $this->getValue($this->ReturnPath, $optionSetArray['ReturnPath']);
			$this->WordWrap= $this->getValue($this->WordWrap, $optionSetArray['WordWrap']);
			$this->ConfirmReadingTo= $this->getValue($this->ConfirmReadingTo, $optionSetArray['ConfirmReadingTo']);
			
  			// PROPERTIES FOR SMTP
			//Requireed - Stop Script
			//Optional - Set Defaults
			$this->Host = $this->getValue($this->Host , $optionSetArray['SMTPHost']);
			$this->Port = $this->getValue($this->Port , $optionSetArray['SMTPPort']);
			$this->SMTPSecure = $this->getValue($this->SMTPSecure , $optionSetArray['SMTPSecure']);
			$this->SMTPAuth= $this->getValue($this->SMTPAuth, $optionSetArray['SMTPAuth']);
			$this->Username = $this->getValue($this->Username , $optionSetArray['SMTPUsername']);
			$this->Password = $this->getValue($this->Password , $optionSetArray['SMTPPassword']);
			$this->AuthType = $this->getValue($this->AuthType , $optionSetArray['SMTPAuthType']);
			$this->Realm = $this->getValue($this->Realm , $optionSetArray['SMTPRealm']);
			$this->Workstation = $this->getValue($this->Workstation , $optionSetArray['SMTPWorkstation']);
			$this->Timeout = $this->getValue($this->Timeout , $optionSetArray['SMTPTimeout']);
			// PROPERTIES FOR POP
			//For pop, may need object
			//Requireed - Stop Script
			//Optional - Set Defaults
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
		$this->optionSetDefined = TRUE;
				
		//Pop initialazation, not need for send
		//$pop = new POP3();
		//$pop->Authorise($this->host, $this->port, $this->timeout, "username", "password", 1);	
	}
	
	/**
	 * Given the new and the current value of a var, returns new value if exists and set or current value else
	 * 
	 * @param	mixed	$currValue
	 * 		The current or the value without error
	 * 
	 * @param	mixed	$newValue
	 * 		The new or the value that must be checked
	 * 
	 * @return	mixed
	 */
	private function getValue($currValue, $newValue)
	{
		if (isset($newValue))
			return $newValue;
		else
			return $currValue;		
	}
	
	/**
	 * Send mail that previously configured and set
	 * 
	 * @return	void
	 */
	public function send($subject = '', $from = array(), $to = '', $auto = 1)
	{
		if(!$this->optionSetDefined)
		{
			logger::log("Mailer was not initiallized before send", logger::DEBUG);
			return;
		}
		
		/* 
		*  If function's args are empty
		*  We assume that user has already given them
		*  useing the appropriate functions
		*/		
	
		// Init
		if(!empty($subject))
			$this->setSubject($subject);
		
		if(!empty($from))
		{
			// Key of first element
			$key = key($from);
			if($this->ValidateAddress($key))
			{
				$sAddress = $key;
				$sName = $from[$key];
			}
			else
			{
				$sAddress = $from[$key];
				$sName = '';
			}			
			//1 : Also set Reply-To and Sender
			$this->SetFrom($sAddress, $sName, $auto);
		}
		
		if(!empty($to))
		{
			$rName = '';
			$rAddress = $to;
			if(is_array($to))
			{
				$rAddress = $to[0];
				$rName = $to[1];			
			}
			$this->AddAddress($rAddress, $rName);
		}
		
		try
		{
			parent::Send();
			return TRUE;
		}
		catch (phpmailerException $e)
		{
			//Pretty error messages from PHPMailer
			logger::log("phpmailerException: ".$e->errorMessage(), logger::DEBUG);
			return FALSE;
		}
		catch (Exception $e) 
		{
			 //Boring error messages from anything else!
			logger::log("Exception: ".$e->getMessage(), logger::DEBUG);
			return FALSE;
		}
	}

	/**
	 * Sets mail subject
	 * 
	 * @param	string	$subject
	 * 		Mail subject
	 * 
	 * @return	void
	 */
	public function setSubject($subject)
	{
		return  $this->Subject = $subject;
	}
	/**
	 * Add a recipient address
	 * 
	 * @param	string	$address
	 * 		The recipients address
	 * 
	 * @param	string	$name
	 * 		The recipients name
	 * 
	 * @return	void
	 */
	public function AddAddress($address, $name = '')
	{
		return  parent::AddAddress($address, $name);
	}	
	/**
	 * Add a CC recipient
	 * 
	 * @param	{type}	$address
	 * 		The recipients address
	 * 
	 * @param	{type}	$name
	 * 		The recipients name
	 * 
	 * @return	void
	 */
	public function AddCC($address, $name = '')
	{
		return  parent::AddCC($address, $name);
	}	
	/**
	 * Add a BCC recipient
	 * 
	 * @param	{type}	$address
	 * 		The recipients address
	 * 
	 * @param	{type}	$name
	 * 		The recipients name
	 * 
	 * @return	void
	 */
	public function AddBCC($address, $name = '')
	{
		return  parent::AddBCC($address, $name);
	}
	/**
	 * Add reply to address
	 * 
	 * @param	{type}	$address
	 * 		The recipients address
	 * 
	 * @param	{type}	$name
	 * 		The recipients name
	 * 
	 * @return	void
	 */
	public function AddReplyTo($address, $name = '')
	{
		return parent::AddReplyTo($address, $name);
	}
	/**
	 * Set message priority
	 * 
	 * @param	int	$priority
	 * 		Message prioty from 1 to 5
	 * 
	 * @return	void
	 */
	public function setPriority($priority)
	{
		$this->Priority = $priority;	
	}
	/**
	 * Add multiple recipoients
	 * 
	 * @param	string	$kind
	 * 		Accepted values to|rto|cc|bcc
	 * 		The kind of recipients to add
	 * 
	 * @param	array	$addressesArray
	 * 		Array of arrays(address, name) or array of strings (address)
	 * 
	 * @return	void
	 */
	public function addMultipleRecipients($kind, $addressesArray)
	{
		if(!is_array($addressesArray))
			return false;	
		//to|rto|cc|bcc
		foreach($address as $addressesArray)
		{
			$addr = array();		
			if(!is_array($address ))
			{
				$addr[0] = $address;
				$addr[1] = '';
			}
			else
			{
				$addr = $address;
			}		
			switch ($kind)
			{
				case "to" :
					$this->AddAddress($addr[0], $addr[1]);
					break;
				case "rto" :
					$this->AddReplyTo($addr[0], $addr[1]);
					break;	
				case "cc" :
					$this->AddCC($addr[0], $addr[1]);
					break;	
				case "bcc" :
					$this->AddBCC($addr[0], $addr[1]);
					break;
				default :			
					break;			
				
			}
		}
	}
	
	/**
	 * Alternative Message for non-html mail clients
	 * 
	 * @param	string	$altBody
	 * 		non html, plain text body
	 * 
	 * @return	void
	 */
	public function setAltBody($altBody)
	{
		$this->AltBody = $altBody;
	}
	/**
	 * Set html body
	 * 
	 * @param	string	$body
	 * 		An html formated mstring
	 * 
	 * @return	void
	 */
	public function MsgHTML($body)
	{
		parent::MsgHTML($body);
	}
			
	
	/**
	 * __Optional Validators
	 * 
	 * @param	{type}	$address
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function validate_address($address){return $address;}
	/**
	 * {description}
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function validate_name($name){return $name;}
	/**
	 * {description}
	 * 
	 * @param	{type}	$subject
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function validate_subject($subject){return $subject;}
	/**
	 * {description}
	 * 
	 * @param	{type}	$htmlBody
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function validate_htmlBody($htmlBody){return $htmlBody;}
	/**
	 * {description}
	 * 
	 * @param	{type}	$altBody
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function validate_altBody($altBody){return $altBody;}
	
}
//#section_end#
?>