<?php
//#section#[header]
// Namespace
namespace API\Comm\mail;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Comm
 * @namespace	\mail
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "mail::mailer");
importer::import("API", "Resources", "settingsManager");

importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger;

use \API\Comm\mail\mailer;
use \API\Resources\settingsManager;
/**
 * RB Mailer
 * 
 * Configure all mail proccedures from redback.gr mail server for predifined account such as support, info, admin
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:44 (EEST)
 * @revised	October 23, 2013, 12:31 (EEST)
 */
class rbMailer extends mailer
{	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const SEND = 'send';
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const RCVE = 'recieve';
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	const BOTH = 'both';

	/**
	 * Mail configuration set
	 * 
	 * @type	array
	 */
	private $optionSet = array();
	
	private $config = array();

	/**
	 * Constructor Method
	 * 
	 * @param	string	$account
	 * 		The email login account name, which crendential will be used for class interaction.
	 * 
	 * @param	string	$optionSetType
	 * 		The type of configuration set
	 * 		send : to enable send mail options
	 * 		recieve : to enable recieve mail options
	 * 		both : to enable both, send and recieve mail options
	 * 
	 * @return	void
	 */
	public function __construct($account, $mode = self::SEND)
	{	
		// Call initialize function for parent	
		$this->intialize();
		
		// Load Common Domain / Server Configuration
		$this->config['Hostname'] = 'mail.redback.gr';
		
		// Load Crendetials
		$domain = "redback.gr";
		$crendentials = $this->loadConfiguration('support');
		$this->config['SMTPUsername'] = $crendentials['USERNAME']."@".$domain;
		$this->config['SMTPPassword'] = $crendentials['PASSWORD'];
		
		// Load Mode Specific Server Configuration
		if($mode == self::SEND || $mode == self::BOTH)
		{
			// PROPERTIES FOR SMTP
			//Requireed - Stop Script
			//Optional - Set Defaults
			$this->config['SMTPHost'] = 'mail.redback.gr';
			$this->config['SMTPPort'] = 25;
			//$optionSetArray['SMTPSecure']= '';
			$this->config['SMTPAuth'] = true;
			$this->config['SMTPAuthType'] = 'LOGIN';
			//$commonOptioset['SMTPRealm']= '';
			//$commonOptioset['SMTPWorkstation']= '';
			//$commonOptioset['SMTPTimeout']= 10;
		}
		
		if($mode == self::RCVE ||$mode == self::BOTH)
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
		$this->config['ContentType'] = 'text/plain';
		$this->config['Encoding']= '8bit';
		$this->config['WordWrap'] = 0;
		//$optionSetArray['ReturnPath'] = '';
		//$optionSetArray['ConfirmReadingTo']= '';	
		
		
		// Set Configuration
		$this->options($this->config);	
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$mailAcount
	 * 		{description}
	 * 
	 * @return	void
	 */
	private function loadConfiguration($mailAcount)
	{
		$settingsManager = new settingsManager("/System/Configuration/Settings/Mail/", $mailAcount, $rootRelative = TRUE);
		$settings = $settingsManager->get();
				
		return $settings;
	}	
	
}
//#section_end#
?>