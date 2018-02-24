<?php
/* 
 * Redback PHP Popup Document
 *
 * Title: RedBack PHP Popup Document - Grid Presenter Containers
 * Description: Creates Grid Presenter (Presenter | gridRow | Item)
 * Author: RedBack Developing Team
 * Standalone: True
 * Version: --
 * DateCreated: 11/07/2012
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

//__________ Include Libs __________//
$useCenter->useCore('reporting::system_reporting');
$useCenter->useCore('reporting::notifications::notification_center');

$sys_rep = new system_report();
$builder = $sys_rep->get_builder();

//____________________ General External Holder for containers
$ext_holder = $builder->getElement("div");
$builder->appendElement($ext_holder);

//__________ General Grid Presenter Wrapper
$presenter = $builder->getElement("div", "", "", "presenter");
$builder->appendChild($ext_holder, $presenter);
$builder->addAttribute($presenter, "data-container", "presenter");

//_____ Items Holder
$itemsCollection = $builder->getElement("div", "", "", "itemsCollection");
$builder->appendChild($presenter, $itemsCollection);

//_____ Curtine
$curtine = $builder->getElement("div", "", "", "curtine");
$builder->appendChild($presenter, $curtine);
// Curtine Pointer
$pointer = $builder->getElement("div", "", "", "pointer");
$builder->appendChild($curtine, $pointer);
// Curtine Pool
$pool = $builder->getElement("div", "", "", "pool");
$builder->appendChild($curtine, $pool);


//__________ Item Presenter Wrapper
$presenterItem = $builder->getElement("div", "", "", "presenterItem");
$builder->addAttribute($presenterItem, "data-container", "presenterItem");
$builder->appendChild($ext_holder, $presenterItem);

//_____ Item a
$itemNav = $builder->getElement("a");
$builder->appendChild($presenterItem, $itemNav);

//_____ Item Image
$itemImage = $builder->getElement("div", "", "", "gridItemImage");
$builder->appendChild($itemNav, $itemImage);

//_____ Item Title
$itemTitle = $builder->getElement("div", "", "", "gridItemTitle");
$builder->appendChild($itemNav, $itemTitle);

//__________ Loader Overlay
$presenterLoader = $builder->getElement("div", "", "", "overlay");
$builder->addAttribute($presenterLoader, "data-container", "overlay");
$builder->appendChild($ext_holder, $presenterLoader);




echo $builder->getHTML();

?>