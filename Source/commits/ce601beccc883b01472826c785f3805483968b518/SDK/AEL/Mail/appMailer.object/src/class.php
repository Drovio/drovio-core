<?php
//#section#[header]
// Namespace
namespace AEL\Mail;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Mail
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("API", "Comm", "mail/mailgun");
importer::import("API", "Model", "apps/application");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Apps", "application");

use \AEL\Platform\application;
use \API\Comm\mail\mailgun;
use \API\Model\apps\application as APIApplication;
use \API\Profile\account;
use \API\Profile\team;
use \DEV\Apps\test\appTester;
use \DEV\Apps\application as DEVApplication;

/**
 * Application mailer
 * 
 * Manages to send mails as application user or team user, according to mailer mode.
 * 
 * - For application use, the 'from' field will be:
 * user@application_name.apps.drov.io
 * - For team use, the 'from' field will be:
 * user@team_name.drov.io
 * 
 * @version	0.1-1
 * @created	September 25, 2015, 2:03 (EEST)
 * @updated	September 25, 2015, 2:03 (EEST)
 */
class appMailer extends mailgun
{
	/**
	 * The mailer application mode identifier.
	 * 
	 * @type	string
	 */
	const MODE_APP = "app";
	/**
	 * The mailer team mode identifier.
	 * 
	 * @type	string
	 */
	const MODE_TEAM = "team";
	
	/**
	 * Create an application mailer instance.
	 * 
	 * @param	string	$mode
	 * 		The mail mode.
	 * 		It is in application mode by default.
	 * 
	 * @return	void
	 */
	public function __construct($mode = self::MODE_APP)
	{
		// Set mode
		$this->mode = ($mode == self::MODE_APP ? self::MODE_APP : self::MODE_TEAM);
			
		// Initialize mailgun
		parent::__construct($domain = "drov.io", $apikey = "api:key-b596b7499f9c28896ee15586ab0dbc65");
	}
	
	/**
	 * Sets the from parameter for the application.
	 * It isn't necessary to be called explicitly. It will be called just before sending the mail.
	 * 
	 * @return	appMailer
	 * 		The appMailer object.
	 */
	public function setFrom()
	{
		// Get account information
		$accountInfo = account::info();
		$accountTitle = $accountInfo['accountTitle'];
		$name = $accountInfo['username'];
		$name = (empty($name) ? "user" : $name);
		
		// Set application or team from
		if ($this->mode == self::MODE_APP)
			return $this->setFromApplication($accountTitle, $name);
		else
			return $this->setFromTeam($accountTitle, $name);
	}
	
	/**
	 * Send the email using the defined settings.
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
	 * 		The mailgun api response.
	 */
	public function send($subject, $content, $type = self::CONTENT_TXT)
	{
		// Set from
		$this->setFrom();
		
		// Check from
		if (empty($this->from))
			return FALSE;
		
		// Send email
		return parent::send($subject, $content, $type);
	}
	
	/**
	 * Set the mail from value to be from application.
	 * 
	 * @param	string	$fromTitle
	 * 		The from field title.
	 * 
	 * @param	string	$fromName
	 * 		The from field name.
	 * 
	 * @return	appMailer
	 * 		The appMailer object.
	 */
	private function setFromApplication($fromTitle, $fromName)
	{
		// Get current application id
		$applicationID = application::init();
		if (empty($applicationID))
			return $this;
			
		// Get application information
		$applicationInfo = array();
		if ($this->onDEV())
		{
			$devApp = new DEVApplication($applicationID);
			$applicationInfo = $devApp->info();
		}
		else
			$applicationInfo = APIApplication::getApplicationInfo($applicationID);
		if (empty($applicationInfo))
			return $this;
		
		// Set the application name as from
		$applicationName = $applicationInfo['name'];
		$applicationTitle = $applicationInfo['title'];
		if (empty($applicationName))
			return $this;
		
		// Set from address
		$from = array();
		$from[$fromName."@".$applicationName.".apps.drov.io"] = $fromTitle;
		return parent::setFrom($from);
	}
	
	/**
	 * Set the mail from value to be from team.
	 * 
	 * @param	string	$fromTitle
	 * 		The from field title.
	 * 
	 * @param	string	$fromName
	 * 		The from field name.
	 * 
	 * @return	appMailer
	 * 		The appMailer object.
	 */
	private function setFromTeam($fromTitle, $fromName)
	{
		// Get team info
		$teamInfo = team::info();
		$teamName = $teamInfo['uname'];
		if (empty($teamName))
			return $this;

		// Set from address
		$from = array();
		$from[$fromName."@".$teamName.".drov.io"] = $fromTitle;
		return parent::setFrom($from);
	}
	
	/**
	 * Check whether the application is running on the Development Environment.
	 * 
	 * @return	boolean
	 * 		True if the application is on DEV, false otherwise.
	 */
	public function onDEV()
	{
		// Get subdomain where the application is running
		$subdomain = appTester::currentDomain();
		
		// Check if it is on Development Environment
		return ($subdomain == "developers");
	}
	
	
}
//#section_end#
?>