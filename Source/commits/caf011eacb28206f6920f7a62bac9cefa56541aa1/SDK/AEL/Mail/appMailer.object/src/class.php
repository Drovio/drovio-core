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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("AEL", "Resources", "appManager");
importer::import("API", "Comm", "mail/mailgun");
importer::import("API", "Model", "apps/application");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Apps", "application");

use \AEL\Platform\application;
use \AEL\Resources\appManager;
use \API\Comm\mail\mailgun;
use \API\Model\apps\application as APIApplication;
use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\filesystem\fileManager;
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
 * @version	3.0-3
 * @created	September 25, 2015, 0:03 (BST)
 * @updated	October 28, 2015, 23:46 (GMT)
 */
class appMailer extends mailgun
{
	/**
	 * The mailer application mode identifier.
	 * 
	 * @type	string
	 */
	const APP_MODE = "app";
	/**
	 * The mailer team mode identifier.
	 * 
	 * @type	string
	 */
	const TEAM_MODE = "team";
	
	/**
	 * @deprecated The mailer application mode identifier.
	 * 
	 * @type	string
	 */
	const MODE_APP = "app";
	/**
	 * @deprecated The mailer team mode identifier.
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
	public function __construct($mode = self::APP_MODE)
	{
		// Set mode
		$this->mode = ($mode == self::APP_MODE ? self::APP_MODE : self::TEAM_MODE);
			
		// Initialize mailgun
		parent::__construct($domain = "drov.io", $apikey = "api:key-b596b7499f9c28896ee15586ab0dbc65");
	}
	
	/**
	 * Sets the from parameter for the application.
	 * It isn't necessary to be called explicitly. It will be called just before sending the mail.
	 * 
	 * @param	array	$from
	 * 		The from address in the following format:
	 * 		['name@example.com'] = "Name Example".
	 * 		Leave empty to let the engine choose one for you (not replyable).
	 * 		It is empty by default.
	 * 
	 * @return	appMailer
	 * 		The appMailer object.
	 */
	public function setFrom($from = array())
	{
		// Set given from address
		if (!empty($from))
			return parent::setFrom($from);
		
		// Set application or team from
		if ($this->mode == self::APP_MODE)
			$from = $this->getApplicationFromAddress();
		else
			$from = $this->getTeamFromAddress();
		
		// Set from
		return parent::setFrom($from);
	}
	
	/**
	 * Add a file attachment.
	 * 
	 * @param	string	$filePath
	 * 		The team file path.
	 * 
	 * @param	boolean	$shared
	 * 		Use the application shared folder or the team's private application folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	appMailer
	 * 		The appMailer object.
	 */
	public function addAttachment($filePath, $shared = FALSE)
	{
		// Check current application
		if (!application::init())
			return $this;
		
		// Get file from team directory
		$attachmentFilePath = appManager::getRootFolder($mode = appManager::TEAM_MODE, $shared)."/".$filePath;
		return parent::addAttachment(systemRoot.$attachmentFilePath);
	}
	
	/**
	 * Send the email using the defined settings.
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
	 * 		The mailgun api response.
	 */
	public function send($subject, $textContent = "", $htmlContent = "")
	{
		// Set from
		$this->setFrom();
		
		// Check from
		if (empty($this->from))
			return FALSE;
		
		// Send email
		return parent::send($subject, $textContent, $htmlContent);
	}
	
	/**
	 * Get the from address field for the team mode.
	 * 
	 * @return	array
	 * 		An array in the following format:
	 * 		$form['email_address'] = "Address Title".
	 */
	public function getTeamFromAddress()
	{
		// Get team info
		$teamInfo = team::info();
		$teamName = strtolower($teamInfo['uname']);
		if (empty($teamName))
			return array();
		
		// Get account information
		if (account::getInstance()->validate())
		{
			$accountInfo = account::getInstance()->info();
			$accountTitle = $accountInfo['title'];
			$accountName = $accountInfo['username'];
			$accountName = (empty($accountName) ? "user" : $accountName);
		}
		else
		{
			$accountTitle = "No Reply";
			$accountName = "noreply";
		}
		
		// Return from array field
		$from = array();
		$from[$accountName."@".$teamName.".drov.io"] = $accountTitle;
		return $from;
	}
	
	/**
	 * Get the from address field for the application mode.
	 * 
	 * @return	array
	 * 		An array in the following format:
	 * 		$form['email_address'] = "Address Title".
	 */
	public function getApplicationFromAddress()
	{
		// Get current application id
		$applicationID = application::init();
		if (empty($applicationID))
			return array();
			
		// Get application information
		$applicationInfo = array();
		if (application::onDEV())
		{
			$devApp = new DEVApplication($applicationID);
			$applicationInfo = $devApp->info();
		}
		else
			$applicationInfo = APIApplication::getApplicationInfo($applicationID);
		if (empty($applicationInfo))
			return array();
		
		// Set the application name as from
		$applicationName = strtolower($applicationInfo['name']);
		if (empty($applicationName))
			return array();
		
		// Get account information
		if (account::getInstance()->validate())
		{
			$accountInfo = account::getInstance()->info();
			$accountTitle = $accountInfo['title'];
			$accountName = $accountInfo['username'];
			$accountName = (empty($accountName) ? "user" : $accountName);
		}
		else
		{
			$accountTitle = "No Reply";
			$accountName = "noreply";
		}
		
		// Return from array field
		$from = array();
		$from[$accountName."@".$applicationName.".apps.drov.io"] = $accountTitle;
		return $from;
	}
}
//#section_end#
?>