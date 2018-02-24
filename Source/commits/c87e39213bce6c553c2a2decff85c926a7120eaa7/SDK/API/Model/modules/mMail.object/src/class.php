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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
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
 * @version	0.1-1
 * @created	June 3, 2015, 23:00 (EEST)
 * @updated	June 3, 2015, 23:00 (EEST)
 */
class mMail
{
	/**
	 * Send an email to a specific user.
	 * 
	 * @param	string	$mailPath
	 * 		The path of the mail message inside the module resources.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	string	$recipient
	 * 		The recipient mail.
	 * 
	 * @param	array	$attributes
	 * 		An array of attributes to personalize the email.
	 * 		This can include names and other user information.
	 * 
	 * @return	mixed
	 * 		True on success, false or NULL on failure.
	 */
	public static function send($mailPath, $subject, $recipient, $attributes = array())
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
		return self::sendMail($subject, $recipient, $messageHTML);
	}
	
	/**
	 * It actually sends the mail.
	 * 
	 * @param	string	$subject
	 * 		The mail subject.
	 * 
	 * @param	string	$recipient
	 * 		The recipient mail.
	 * 
	 * @param	string	$messageHTML
	 * 		The message in html format.
	 * 
	 * @return	boolean
	 * 		True on success, false or NULL on failure.
	 */
	private static function sendMail($subject, $recipient, $messageHTML)
	{
		// Initialize mailer
		$mailer = new mailer("support");
		
		// Set mail subject
		$mailer->subject($subject);
		
		// Set HTML message
		$mailer->MsgHTML($messageHTML);
		
		// Send message
		$sender = array();
		$sender["no-reply@redback.io"] = 'Redback';
		return $mailer->send($subject, $sender, $recipient);
	}
}
//#section_end#
?>