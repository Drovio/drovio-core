<?php

/* 
 * Redback PHP Popup Document
 *
 * Title: RedBack PHP Popup Document - Notification Popup
 * Description: Aqcuires proper notification, builds and returns a notification popup
 * Author: RedBack Developing Team
 * Standalone: True
 * Version: --
 * DateCreated: 21/01/2012
 * DateRevised: --
 *
 */

//__________ SERVER CONTROL __________//
if ($_SERVER['REQUEST_METHOD'] == "POST")
	return;

//____________________________________________________________________________________________________ Important!
//__________ Check Domains __________//
require_once($_SERVER['DOCUMENT_ROOT'].'/_domainConfig.php');

//__________ Include Center __________//
// Require Importer
require_once(systemRoot.systemCore.'/base/importer.php');
use \core\base\importer;

importer::useCore('base::sdk');
use \core\base\sdk;

importer::useHeader('sessionManager');
//____________________________________________________________________________________________________ Important!__END

//__________ Include Libs __________//
importer::useCore('resources::geolocation_center');
importer::useCore('resources::multilingual_center');
sdk::useSDK('protocol::reports::system_report');
sdk::useSDK('ui::messages::notifications::notification');
sdk::useSDK('ui::messages::notifications::popups::popup');

use \core\resources\geolocation_center;
use \core\resources\multilingual_center;
use \sdk\protocol\reports\system_report;
use \sdk\notifications\notification;
use \sdk\notifications\popup;

$sys_rep = new system_report();
$builder = $sys_rep->get_builder();
$rgMng = new geolocation_center();
$locale = $rgMng->getCurrentLocale();

//print_r($_GET);
//
$notificationInfo = $_GET['info'];
// type: warning | error | info, acquired from php through GET
$notificationType = $_GET['type'];
$notificationID = $_GET['id'];
$notificationLocale = $_GET['locale'];

$multiLang = new multilingual_center($builder, "/Library/Media/Tools/eBuilder/forms/validator/notifications/");
$form_notification = new notification($builder);
$form_popup = new popup($builder);
$ntfType = $notificationType;
if ($notificationType == "fann")
	$ntfType = "info";

$fnpp = $form_notification->build_notification($ntfType, $header = FALSE, $footer = TRUE);
$fnppContainer = $form_popup->get_popup($fnpp, $inline = TRUE);
$builder->addAttribute($fnppContainer, 'id', 'fnpp');

$builder->appendElement($fnppContainer);

if ($notificationType == "warning")
{
	//if (isset($notificationInfo))
		//$content = $eHandler->form_cst_msg($builder, $notificationInfo);
	//else
		$content = $multiLang->get_message($notificationType, $notificationID);
	
	$form_notification->append_content($content);
}
else if ($notificationType == "error" || $notificationType == "fann")
{
	if (!is_array($notificationID))
	{
		//if ($notificationType == "fann" && isset($notificationInfo))
		//	$content = $eHandler->form_cst_msg($builder, $notificationInfo);
		//else
			$content = $multiLang->get_message($notificationType, $notificationID);
		
		$form_notification->append_content($content);
	}
	else
	{
		$has_list = false;
		$fnppList;
		
		if ($notificationType == "fann" && isset($notificationInfo))
		{
			//$content = $eHandler->form_cst_msg($builder, $notificationInfo);
			
			$form_notification->append_content($content);
		}
		foreach ($notificationID as $nID)
		{
			$nIDextra="";
			$nIDhidden=false;
			$nIDlist=false;
			$nIDheader=false;
			$nIDfooter=false;
			if (strpos($nID, " footer"))
			{
				$nIDfooter = true;
				$nID = substr($nID,0,strpos($nID, " footer"));
				$footerContainer = $builder->getElement("div", "", "", 'notes');
				$content = $multiLang->get_message($notificationType, $nID);
				$builder->appendChild($footerContainer, $content);
				$form_notification->append_content($footerContainer);
			}
			else if (strpos($nID, " header"))
			{
				$nIDheader = true;
				$nID = substr($nID,0,strpos($nID, " header"));
				$content = $multiLang->get_message($notificationType, $nID);
				$form_notification->append_content($content);
			}
			else if (strpos($nID, " list"))
			{
				$nIDlist = true;
				$nID = substr($nID,0,strpos($nID, " list"));
				if (strpos($nID, " hidden"))
				{
					$nIDhidden = true;
					$nID = substr($nID,0,strpos($nID, " hidden"));
				}
				if (strpos($nID, " "))
				{
					$nIDextra = substr($nID, strpos($nID, " ")+1);
					$nID = substr($nID,0,strpos($nID, " "));
				}
				if (!$has_list)
				{
					$fnppList = $builder->getElement('ul');
					$form_notification->append_content($fnppList);
					$has_list = true;
				}
				
				$content = $multiLang->get_message($notificationType, $nID);
				
				$li_class = ($notificationType == "error" ? 'fnppRedBullet fnppRedText' : 'fnppBullet').($nIDhidden ? ' noDisplay' : '');
				$li = $builder->getElement('li', "", "", $li_class);
					
				$span = $builder->getElement('span', $nIDextra, "", "extra");
				$builder->appendChild($content, $span);
				$builder->appendChild($li, $content);
				$builder->appendChild($fnppList, $li);
			}
		}
	}
}
echo $builder->getHTML();
return;
?>