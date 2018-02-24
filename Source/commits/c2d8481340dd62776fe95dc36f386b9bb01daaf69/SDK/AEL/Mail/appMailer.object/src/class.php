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
 * @version	2.0-2
 * @created	September 25, 2015, 2:03 (EEST)
 * @updated	September 25, 2015, 18:57 (EEST)
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
		if ($this->mode == self::APP_MODE)
			return $this->setFromApplication($accountTitle, $name);
		else
			return $this->setFromTeam($accountTitle, $name);
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
		application::init(84);
		
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
		if (application::onDEV())
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
}
//#section_end#
?>