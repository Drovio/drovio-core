<?php
//#section#[header]
// Namespace
namespace SYS\Comm\mail;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Comm
 * @namespace	\mail
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Comm", "mail/mailer");
importer::import("SYS", "Resources", "settings/accSettings");

use \API\Comm\mail\mailer as APImailer;
use \SYS\Resources\settings\accSettings;
/**
 * Redback mailer
 * 
 * This class is the main handler for sending mail messages from the redback.gr mail server.
 * It supports the predefined accounts such as support, info, admin and no-reply
 * 
 * @version	0.1-3
 * @created	July 7, 2014, 13:06 (EEST)
 * @updated	January 11, 2015, 12:55 (EET)
 */
class mailer extends APImailer
{	
	/**
	 * The send mode operation for the mailer.
	 * 
	 * @type	string
	 */
	const SEND = 'send';
	/**
	 * The receive mode operation for the mailer.
	 * 
	 * @type	string
	 */
	const RCVE = 'recieve';
	/**
	 * This supports both send and receive modes for mailer.
	 * 
	 * @type	string
	 */
	const BOTH = 'both';

	/**
	 * Constructor Method.
	 * Initializes the mailer object.
	 * 
	 * @param	string	$account
	 * 		The email login account name, which credentials will be used for class interaction.
	 * 
	 * @param	string	$mode
	 * 		The type of configuration set.
	 * 		SEND: to enable send mail options.
	 * 		RCVE: to enable receive mail options
	 * 		BOTH: to enable both, send and receive mail options.
	 * 		
	 * 		Use class constants.
	 * 
	 * @return	void
	 */
	public function __construct($account = "support", $mode = self::SEND)
	{	
		// Call initialize function for parent	
		$this->initialize();
		$options = array();
		
		// Load Common Domain / Server Configuration
		$options['Hostname'] = 'mail.redback.io';
		
		// Load Crendetials
		$domain = "redback.io";
		$crendentials = $this->loadConfiguration($account);
		$options['SMTPUsername'] = $crendentials['USERNAME']."@".$domain;
		$options['SMTPPassword'] = $crendentials['PASSWORD'];
		
		// Load Mode Specific Server Configuration
		if ($mode == self::SEND || $mode == self::BOTH)
		{
			// PROPERTIES FOR SMTP
			//Requireed - Stop Script
			//Optional - Set Defaults
			$options['SMTPHost'] = 'mail.redback.io';
			$options['SMTPPort'] = 25;
			//$optionSetArray['SMTPSecure']= '';
			$options['SMTPAuth'] = true;
			$options['SMTPAuthType'] = 'LOGIN';
			//$commonOptioset['SMTPRealm']= '';
			//$commonOptioset['SMTPWorkstation']= '';
			//$commonOptioset['SMTPTimeout']= 10;
		}
		
		if ($mode == self::RCVE ||$mode == self::BOTH)
		{
			// PROPERTIES FOR POP
			//For pop, may need object
			//Requireed - Stop Script
			//Optional - Set Defaults
			//$commonOptioset['POPhost'];
			//$commonOptioset['POPport'];
			//$commonOptioset['POPtval'];
			//$commonOptioset['POPusername'];
			//$commonOptioset['POPpassword'];
		}		
		
		// Load Additional Mail Configuration Oprtions
		$options['ContentType'] = 'text/plain';
		$options['Encoding']= '8bit';
		$options['WordWrap'] = 0;
		//$optionSetArray['ReturnPath'] = '';
		//$optionSetArray['ConfirmReadingTo']= '';	
		
		
		// Set mail options
		$this->options($options);	
	}
	
	
	/**
	 * Load the email configuration credentials for the given account name.
	 * 
	 * @param	string	$account
	 * 		The mail account name to load the configuration.
	 * 
	 * @return	array
	 * 		The account configuration settings as parsed from the settings file.
	 */
	private function loadConfiguration($account)
	{
		$settingsManager = new accSettings("mail", $account);
		return $settingsManager->get();
	}	
	
}
//#section_end#
?>