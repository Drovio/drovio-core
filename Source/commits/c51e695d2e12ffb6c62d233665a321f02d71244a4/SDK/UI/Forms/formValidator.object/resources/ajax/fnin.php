<?php
/* 
 * Redback PHP Form Document
 *
 * Title: RedBack PHP Form Document - Notification Bucket
 * Description: Aqcuires proper notification, builds and returns a notification list
 * Author: RedBack Developing Team
 * Standalone: True
 * Version: --
 * DateCreated: 21/04/2012
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
require_once(systemRoot.systemCore.'/base/include_center.php');
$useCenter = new include_center();

$useCenter->useHeader('sessionManager');
//$useCenter->useHeader('systemInit');
//____________________________________________________________________________________________________ Important!__END

$useCenter->useCore('reporting::system_reporting');
$useCenter->useCore('reporting::error_reporting');
$useCenter->useCore('resources::geolocation_center');

$sys_rep = new system_report();

$rgMng = new geolocation_center();

$notificationHeaders = $_GET['headers'];
$notificationErrorLists = $_GET['errorLists'];
$locale = (isset($_GET['locale']) ? $_GET['locale'] : $rgMng->getCurrentLocale());

$builder = $sys_rep->get_builder();
$err_rep = new error_report($builder);

$errList = $err_rep->get_error_bucket();

$counter = 0;
$ntfType = "error";
foreach ($notificationHeaders as $header)
{
	$subList = $err_rep->get_error_bucket_item($errList, $header);
	
	foreach ($notificationErrorLists[$counter] as $errListElement)
	{
		$errListElementExtra="";
		$errListElementHidden=false;
		$errListElementList=false;
		$errListElementList = true;
		$errListElement = substr($errListElement,0,strpos($errListElement, " list"));
		if (strpos($errListElement, " hidden"))
		{
			$errListElementHidden = true;
			$errListElement = substr($errListElement,0,strpos($errListElement, " hidden"));
		}
		if (strpos($errListElement, " "))
		{
			$errListElementExtra = substr($errListElement, strpos($errListElement, " ")+1);
			$errListElement = substr($errListElement,0,strpos($errListElement, " "));
		}
		if (!$errListElementHidden)
			$err_rep->insert_error_desc($subList, $ntfType, $errListElement, $errListElementExtra, $locale);	
	}
	$counter++;
}

echo $sys_rep->invalid_data($errList);
?>