<?php
//#section#[header]
// Namespace
namespace API\Model\modules;

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
 * @package	Model
 * @namespace	\modules
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("SYS", "Comm", "mail/mailer");
importer::import("ESS", "Environment", "url");
importer::import("API", "Model", "modules/resource");
importer::import("DEV", "Modules", "test/mrsrcTester");
importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");

use \SYS\Comm\mail\mailer;
use \ESS\Environment\url;
use \API\Model\modules\resource;
use \DEV\Modules\test\mrsrcTester;
use \DEV\Modules\modulesProject;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;

/**
 * Modules mailer mechanism
 * 
 * Sends specific mails to users for specific reasons.
 * These mails come from the platform.
 * 
 * @version	1.0-2
 * @created	June 3, 2015, 23:00 (EEST)
 * @updated	June 30, 2015, 11:01 (EEST)
 */
class mMail
{
	/**
	 * Add 'to' recipients for the mail.
	 * 
	 * @type	string
	 */
	const MAIL_TYPE_TO = "to";
	
	/**
	 * Add 'cc' recipients for the mail.
	 * 
	 * @type	string
	 */
	const MAIL_TYPE_CC = "cc";
	
	/**
	 * Add 'bcc' recipients for the mail.
	 * 
	 * @type	string
	 */
	const MAIL_TYPE_BCC = "bcc";
	
	/**
	 * Send an email to a specific user.
	 * 
	 * @param	string	$mailPath
	 * 		The path of the mail message inside the module resources.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	mixed	$recipients
	 * 		For one recipient, use the email address directly.
	 * 		For many recipients, use an array as:
	 * 		mail => name.
	 * 
	 * @param	array	$attributes
	 * 		An array of attributes to personalize the email.
	 * 		This can include names and other user information.
	 * 
	 * @param	string	$mailType
	 * 		The mail recipient type.
	 * 		You can choose to send mail 'to', 'cc' or 'bcc'.
	 * 		Use class constants.
	 * 		Default value is MAIL_TYPE_TO.
	 * 
	 * @return	mixed
	 * 		True on success, false or NULL on failure.
	 */
	public static function send($mailPath, $subject, $recipients, $attributes = array(), $mailType = self::MAIL_TYPE_TO)
	{
		// Load html message
		$messageHTML = resource::get($mailPath);
		
		// Set attributes
		foreach ($attributes as $key => $value)
			$messageHTML = str_replace("%{".$key."}", $value, $messageHTML);
		
		// Resolve urls
		if (mrsrcTester::status())
		{
			// Resolve project-specific urls
			$project = new modulesProject();
			$resourcePath = $project->getResourcesFolder()."/media";
			$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
			$resourceUrl = url::resolve("repo", $resourcePath, "http");
			
			// Set Variables
			$messageHTML = str_replace("%resources%", $resourceUrl, $messageHTML);
			$messageHTML = str_replace("%{resources}", $resourceUrl, $messageHTML);
			$messageHTML = str_replace("%media%", $resourceUrl, $messageHTML);
			$messageHTML = str_replace("%{media}", $resourceUrl, $messageHTML);
		}
		else
		{
			// Get project folder
			$version = projectLibrary::getLastProjectVersion(modulesProject::PROJECT_ID);
			$publishFolder = projectLibrary::getPublishedPath(modulesProject::PROJECT_ID, $version);
			
			// Resolve project-relative urls
			$resourcePath = $publishFolder.projectLibrary::RSRC_FOLDER;
			$resourcePath = str_replace(paths::getPublishedPath(), "", $resourcePath);
			$resourceUrl = url::resolve("lib", $resourcePath);
			
			$messageHTML = str_replace("%resources%", $resourceUrl, $messageHTML);
			$messageHTML = str_replace("%{resources}", $resourceUrl, $messageHTML);
			$messageHTML = str_replace("%media%", $resourceUrl, $messageHTML);
			$messageHTML = str_replace("%{media}", $resourceUrl, $messageHTML);
		}
		
		// Send email
		return self::sendMail($subject, $recipients, $messageHTML, $mailType);
	}
	
	/**
	 * It actually sends the mail.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	mixed	$recipients
	 * 		For one recipient, use the email address directly.
	 * 		For many recipients, use an array as:
	 * 		mail => name.
	 * 
	 * @param	string	$messageHTML
	 * 		The message in html format.
	 * 
	 * @param	string	$mailType
	 * 		The mail recipient type.
	 * 		You can choose to send mail 'to', 'cc' or 'bcc'.
	 * 		Use class constants.
	 * 		Default value is MAIL_TYPE_TO.
	 * 
	 * @return	boolean
	 * 		True on success, false or NULL on failure.
	 */
	private static function sendMail($subject, $recipients, $messageHTML, $mailType = self::MAIL_TYPE_TO)
	{
		// Initialize mailer
		$mailer = new mailer("support");
		
		// Set mail subject
		$mailer->subject($subject);
		
		// Set HTML message
		$mailer->MsgHTML($messageHTML);
		
		// Set recipients
		$rcpArray = array();
		if (is_string($recipients))
			$rcpArray[$recipients] = "";
		else
			$rcpArray = $recipients;
		
		// Add recipients fields
		foreach ($rcpArray as $mail => $name)
			switch ($mailType)
			{
				case self::MAIL_TYPE_TO:
					$mailer->AddAddress($mail, $name);
					break;
				case self::MAIL_TYPE_CC:
					$mailer->AddCC($mail, $name);
					break;
				case self::MAIL_TYPE_BSS:
					$mailer->AddBCC($mail, $name);
					break;
				default:
					$mailer->AddAddress($mail, $name);
			}
		
		// Send message
		$sender = array();
		$sender["no-reply@redback.io"] = 'Drovio';
		return $mailer->send($subject, $sender);
	}
}
//#section_end#
?>