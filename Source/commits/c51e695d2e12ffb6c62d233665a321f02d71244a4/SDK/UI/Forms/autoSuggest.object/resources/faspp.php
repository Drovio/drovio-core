<?php
/* 
 * Redback PHP Popup Document
 *
 * Title: RedBack PHP Popup Document - Auto Suggest Containers
 * Description: Creates Autosuggest System Containers (Wrapper | Group | GroupItem | Popup)
 * Author: RedBack Developing Team
 * Standalone: True
 * Version: --
 * DateCreated: 24/05/2012
 * DateRevised: 25/05/2012
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

//__________ Include Libs __________//
$useCenter->useCore('reporting::system_reporting');
$useCenter->useCore('reporting::notifications::notification_center');
$useCenter->useCore('reporting::notifications::data_center');
$useCenter->useCore('resources::multilingual_center');
$useCenter->useCore('resources::geolocation_center');


//__________ Notification Content Directory __________//
$ntfDirectory = "/Library/Media/Tools/eBuilder/forms/autosuggest/notifications";

$sys_rep = new system_report();
$mlgMng = new multilingual_center(TRUE, $ntfDirectory);
$rgMng = new geolocation_center();
$locale = (isset($_GET['locale']) ? $_GET['locale'] : $rgMng->getCurrentLocale());

$ntf_center = new notification_center($ntfDirectory, $locale);
$builder = $sys_rep->get_builder();
$sys_data_center = new data_center($builder);

//____________________ General External Holder for containers
$ext_holder = $builder->getElement("div");
$builder->appendElement($ext_holder);

//__________ External wrapper for suggestion groups
$wrapper = $builder->getElement("div");
$builder->addAttribute($wrapper, "class", "fasppContainer");
$builder->addAttribute($wrapper, "data-container", "wrapper");
$builder->appendChild($ext_holder, $wrapper);

$container = $sys_data_center->get_glb_data_container($builder, $has_head = FALSE, $has_body = TRUE, $title = NULL, $has_footer = FALSE);
$builder->appendChild($wrapper, $container);

$msg_body = $sys_data_center->get_msg_body($builder);

$pivot = $builder->getElement("span");
$builder->addAttribute($pivot, "class", "pivot");
$builder->appendChild($msg_body, $pivot);

// Suggestion List "loading" item
$loadingItem = $builder->getElement("div");
$builder->addAttribute($loadingItem, "class", "loading");
$builder->appendChild($msg_body, $loadingItem);

$loadingIcon = $builder->getElement("div");
$builder->addAttribute($loadingIcon, "class", "icon");
$builder->appendChild($loadingItem, $loadingIcon);

$loadingTitle = $builder->getElement("span", "Loading...");
$builder->addAttribute($loadingTitle, "class", "title");
$builder->appendChild($loadingItem, $loadingTitle);


//__________ Group Container
$group = $builder->getElement("div");
$builder->addAttribute($group, "class", "fasppGroup");
$builder->addAttribute($group, "data-container", "group");
$builder->appendChild($ext_holder, $group);
//_____ Title of the Group
$groupTitle = $builder->getElement("div");
$builder->addAttribute($groupTitle, "class", "groupTitle");
$builder->appendChild($group, $groupTitle);

//_____ Suggestion List Container
$suggestionList = $builder->getElement("ul");
$builder->addAttribute($suggestionList, "class", "suggestionList");
$builder->appendChild($group, $suggestionList);

// Suggestion List "more" Item
$moreItem = $builder->getElement("li");
$builder->addAttribute($moreItem, "class", "more");
$builder->appendChild($suggestionList, $moreItem);

$moreMessage = $ntf_center->get_sys_msg($builder, $type = "info", $id = "info.autosuggest.footer", $locale = "");
$builder->appendChild($moreItem, $moreMessage);

//__________ Group Item
$groupItem = $builder->getElement("li");
$builder->addAttribute($groupItem, "class", "suggestListItem");
$builder->addAttribute($groupItem, "data-container", "groupItem");
$builder->appendChild($ext_holder, $groupItem);

$itemImage = $builder->getElement("div");
$builder->addAttribute($itemImage, "class", "listItemImage");
$builder->appendChild($groupItem, $itemImage);

$itemTitle = $builder->getElement("div");
$builder->addAttribute($itemTitle, "class", "listItemTitle");
$builder->appendChild($groupItem, $itemTitle);

$itemSubtitle = $builder->getElement("div");
$builder->addAttribute($itemSubtitle, "class", "listItemSubtitle");
$builder->appendChild($groupItem, $itemSubtitle);

$itemNotes = $builder->getElement("div");
$builder->addAttribute($itemNotes, "class", "listItemNotes");
$builder->appendChild($groupItem, $itemNotes);

$itemTag = $builder->getElement("div");
$builder->addAttribute($itemTag, "class", "listItemTag");
$builder->appendChild($groupItem, $itemTag);

//__________ Popup Container
$popup = $ntf_center->get_sys_ntf($builder, "info", TRUE, "1", TRUE);
$builder->addAttribute($popup['holder'], "data-container", "popup");
$builder->appendChild($ext_holder, $popup['holder']);

$bd = $popup['body'];
$ntf_msg = $ntf_center->get_sys_msg($builder, "info", "info.popup");
$builder->appendChild($bd, $ntf_msg);


echo $builder->getHTML();

?>