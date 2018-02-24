<?php
//#section#[header]
// Namespace
namespace INU\Views;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

class mapView extends UIObjectPrototype
{
	private $mapControlType;
	
	// Constructor Method
	public function __construct($type = "")
	{
		// Initialize Properties
		$this->mapControlType = $type;
	}
	
	public function build()
	{
		// Set UI Object Holder
		$holder = DOM::create("div", "", "mapView");
		$this->set($holder);
		
		// Build Map Container
		$mapContent = DOM::create("div", "", "mapContent");
		DOM::append($holder, $mapContent);
		
		// Build Map Control
		$map = DOM::create("div", "", "map");
		DOM::append($mapContent, $map);
		
		return $this;
	}
	
	public function buildAddressFinder($actionControl = 0, $queryVisibility = 0, $resultVisibility = 0)
	{
		$holder = $this->get();
		DOM::attr($holder, "data-type", "addressFinder");
		
		// Set Attributes
		$attr = array();
		$attr['actionControl'] = $actionControl;
		$attr['queryVisibility'] = $queryVisibility;
		$attr['resultVisibility'] = $resultVisibility;
		DOM::data($holder, "prefs", $attr);
	}
}

/*
//___GET VARIABLES
$mapControlType = (!isset($_GET["mapType"]) || empty($_GET["mapType"]) ? "" : $_GET["mapType"]);



//Build Control Container
$controlContainer = $appBuilder->getElement("div", "", "rb_gMapHolder");

//Build Control Report Div
$controlReport = $appBuilder->getElement("div", "", "rb_gMapReport");
$appBuilder->appendChild($controlContainer, $controlReport);
//Build Control Instructions Div
$rb_gMapInsructions = $appBuilder->getElement("div", "", "rb_gMapInsructions");
$appBuilder->appendChild($controlContainer, $rb_gMapInsructions);
//Build Control Notification Pool Div
$rb_gMapNotificationPool = $appBuilder->getElement("div", "", "rb_gMapNotificationPool");
$appBuilder->appendChild($controlContainer, $rb_gMapNotificationPool);
//Build Control Content Div
$controlContent = $appBuilder->getElement("div", "", "rb_gMapContent");
$appBuilder->appendChild($controlContainer, $controlContent);
//
$mapDiv = $appBuilder->getElement("div","","rb_gMap","");
$appBuilder->appendChild($controlContent, $mapDiv);
//Build Control Ouput Div
$controlResult = $appBuilder->getElement("div", "", "rb_gMapResult","noDisplay");
$appBuilder->appendChild($controlContainer, $controlResult);
//Build Control Ouput Div
$controlResource = $appBuilder->getElement("div", "", "rb_gMapResource","noDisplay");
$appBuilder->appendChild($controlContainer, $controlResource);


//Declare Standar Notification

switch ($mapControlType)
{
//_________________________addressFinder___________________________________________//
	case "addressFinder":
		$attr = array();
		$attr['actionControl'] = (!isset($_GET["actionControl"]) || empty($_GET["actionControl"]) ? "0" : $_GET["actionControl"]);
		$attr['queryVisibility'] = (!isset($_GET["queryVisibility"]) || empty($_GET["queryVisibility"]) ? "0" : $_GET["queryVisibility"]);
		$attr['resultVisibility'] = (!isset($_GET["resultVisibility"]) || empty($_GET["resultVisibility"]) ? "0" : $_GET["resultVisibility"]);
		
		//Add Control Srecified Attributes
		$appBuilder->addAttribute($controlContainer, "data-controlType", $mapControlType);
		$appBuilder->insertData($controlContainer, "prefs", $attr);
		
		//Build Control Row
		$controlRow = $appBuilder->getElement("div","","","rb_gMapControlRow");
		
			//Build Query Field
			$queryField = $appBuilder->getElement("input","","addressQueryField",$attr['queryVisibility'] ? "" : "noDisplay");
			$appBuilder->addAttribute($queryField, "name", "addressQueryField");
			$appBuilder->appendChild($controlRow, $queryField);
	
			//Build Query Execute Button
			if ($attr['actionControl'])
			{
				$execButton = $appBuilder->getElement("a");
				$appBuilder->addAttribute($execButton, "id", "execButton");
				$appBuilder->addAttribute($execButton, "href", "#");
					$execButtonText = $appBuilder->getElement("span","Search");
					$appBuilder->appendChild($execButton, $execButtonText);
				$appBuilder->appendChild($controlRow, $execButton);
			}
		$appBuilder->appendChildBefore($controlContent, $mapDiv, $controlRow);

		//Build Result
			//Build Lat OutputField
			$latOutputField = $appBuilder->getElement("input","","lat",$attr['queryVisibility'] ? "" : "noDisplay");
			$appBuilder->addAttribute($latOutputField, "name", "lat");
			$appBuilder->appendChild($controlResult, $latOutputField);
			//Build Lng OutputField
			$lngOutputField = $appBuilder->getElement("input","","lng",$attr['queryVisibility'] ? "" : "noDisplay");
			$appBuilder->addAttribute($lngOutputField, "name", "lng");
			$appBuilder->appendChild($controlResult, $lngOutputField);

		//Build Left Content
		$leftContentDiv = $appBuilder->getElement("div","","","rb_gMapGeoResult");
		
			//Build Notification Holder
			$noficationsDiv = $appBuilder->getElement("div","","","rb_notification");
				//Prompt Choose Address
				$prmt_chooseAddr = $multiLang->get_glb_lit($appBuilder, "", "prmt_chooseAddr", $locale = "");
				$appBuilder->appendChild($noficationsDiv, $prmt_chooseAddr);
			$appBuilder->appendChild($leftContentDiv, $noficationsDiv);
			
			//Build Address Result List
			$addressListDiv = $appBuilder->getElement("div","","rb_addressTlb","");
				//Notification No Address to select
				$ntf_noAddr = $multiLang->get_glb_lit($appBuilder, "", "ntf_noAddr", $locale = "");
				$appBuilder->appendChild($addressListDiv, $ntf_noAddr);
			$appBuilder->appendChild($leftContentDiv, $addressListDiv);

		$appBuilder->appendChildBefore($controlContent, $mapDiv, $leftContentDiv);
				
		//Built Map
		$appBuilder->addAttribute($mapDiv,"class","addressMap");
		break;
//_________________________setArea___________________________________________//
	case "setArea":
		
		//Add Control Srecified Attributes
		$appBuilder->addAttribute($controlContainer, "data-controlType", $mapControlType);
		
		//Set Instructions
		$instr = $ntf_center->get_sys_msg($appBuilder, "", "instr_setArea");
		//$instr = $msg_center->get_message($appBuilder, "notifications/el_GR", "instr_setArea", $locale = "el_GR");
		$appBuilder->appendChild($rb_gMapInsructions, $instr);
		
		//Check DB for saved area
		$areaMarkersResult = 3;
		if($areaMarkersResult > 0)
		{
			$idNo = "1";
			$marker = $appBuilder->getElement("input","","marker_".$idNo,"");
			$appBuilder->addAttribute($marker, "type", "textbox");
			$appBuilder->addAttribute($marker, "disabled", "disabled");
			$cords = array();
			$cords["lat"] = "40.23415468515967";
			$cords["lng"] = "22.44785858154296";
			$appBuilder->insertData($marker, "cords", $cords);
			$latLng = "40.23415468515967:22.44785858154296";
			$appBuilder->addAttribute($marker, "value", $latLng);
			$appBuilder->appendChild($controlResult, $marker);
			
			$idNo = "2";
			$marker = $appBuilder->getElement("input","","marker_".$idNo,"");
			$appBuilder->addAttribute($marker, "type", "textbox");
			$appBuilder->addAttribute($marker, "disabled", "disabled");
			$cords = array();
			$cords["lat"] = "40.236775598547226";
			$cords["lng"] = "22.49283386230468";
			$appBuilder->insertData($marker, "cords", $cords);
			$latLng = "40.236775598547226:22.49283386230468";
			$appBuilder->addAttribute($marker, "value", $latLng);
			$appBuilder->appendChild($controlResult, $marker);
			
			$idNo = "3";
			$marker = $appBuilder->getElement("input","","marker_".$idNo,"");
			$appBuilder->addAttribute($marker, "type", "textbox");
			$appBuilder->addAttribute($marker, "disabled", "disabled");
			$cords = array();
			$cords["lat"] = "40.21161064269127";
			$cords["lng"] = "22.470174560546866";
			$appBuilder->insertData($marker, "cords", $cords);
			$latLng = "40.21161064269127:22.470174560546866";
			$appBuilder->addAttribute($marker, "value", $latLng);
			$appBuilder->appendChild($controlResult, $marker);
		}
		
		//Built Map
		$appBuilder->addAttribute($mapDiv,"class","setArea");
		break;
//_________________________areaFinder___________________________________________//
	case 'areaFinder':
		//Add Control Srecified Attributes
		$appBuilder->addAttribute($controlContainer, "data-controlType", $mapControlType);
		
		//Controls SideBar
		$sideBar = $appBuilder->getElement("div","","","fLeft");
			$locationControl = $appBuilder->getElement("div","","","locControl");
				//Current Address Indicator
				$curAddress = $appBuilder->getElement("div","","", "curAddress");
				$homeCords = array();
				$homeCords["homelat"] = "41.089492";
				$homeCords["homelng"] = "23.543186";
				$appBuilder->insertData($curAddress, "homeCord", $homeCords);
				$appBuilder->appendChild($locationControl, $curAddress);
				//Promt Message
				$menu = new navMenu($appBuilder);
				$simpleMenu = $menu->build_menu($id = "", $header = "", $class = "");
				$subList = $menu->insert_subList($simpleMenu);
				$staticNav = array();
				$staticNav['ref'] = "locationSelector";
				$staticNav['targetcontainer'] = "rb_gMapContent";
				$staticNav['targetgroup'] = "locationSelector";
				$staticNav['navgroup'] = "locGroup_".rand();
				$staticNav['display'] = "toggle";
				$item_text = $multiLang->get_glb_lit($appBuilder, "", "prmt_locChange", $locale = "");
				$prompt = $menu->insert_listItem($subList, $item_text, "", TRUE);
				$menu->add_static_navigation($prompt, $staticNav);
				$appBuilder->appendChild($locationControl, $simpleMenu);
				
				//location selector
				$locationSelector = $appBuilder->getElement("div","","locationSelector","",array("targetgroupid"=>"locationSelector"));
					$tabControl = new tabControl($appBuilder);
					$tabMenu = $tabControl->get_control($id = "", $full = TRUE);
					//Tab Group
						//Address Search
						$title = $multiLang->get_glb_lit($appBuilder, "", "lbl_searchAddress", $locale = "");
						$addressSearch = $appBuilder->getElement("div");
						//Build Query Field
						$queryField = $appBuilder->getElement("input","","addressQueryField");
						$appBuilder->addAttribute($queryField, "name", "addressQueryField");
						$appBuilder->appendChild($addressSearch, $queryField);
				
						//Build Query Execute Button
						$execButtonText = $multiLang->get_glb_lit($appBuilder, "", "lbl_execButton", $locale = "");
						$execButton = $appBuilder->getElement("a");
						$appBuilder->addAttribute($execButton, "id", "execButton");
						$appBuilder->addAttribute($execButton, "href", "#");
							$appBuilder->appendChild($execButton, $execButtonText);
						$appBuilder->appendChild($addressSearch, $execButton);

						//Build Address Result List
						$addressListDiv = $appBuilder->getElement("div","","rb_addressTlb","");
						$appBuilder->appendChild($addressSearch, $addressListDiv);
						
						$tabControl->insert_tab("addrSearch", $title, $addressSearch, $selected = TRUE);
						
						//My locations
						if(TRUE) //If feature exists
						{
							$title = $multiLang->get_glb_lit($appBuilder, "", "lbl_myLoc", $locale = "");
							$myLocations = $appBuilder->getElement("div");
							if(TRUE) //if user is logged in
							{
								//Check for locations
								if(TRUE) //if user has locations
								{
									//Additional Address From User Preferences
									$address = $appBuilder->getElement("div","","", "myAddress");
									$homeCords = array();
									$homeCords["homelat"] = "40.763024";
									$homeCords["homelng"] = "23.906014";
									$appBuilder->insertData($address, "homeCord", $homeCords);
									$addressName =  $appBuilder->getElement("span","Εξοχικό");
									$appBuilder->appendChild($address, $addressName);
									$appBuilder->appendChild($myLocations, $address);
		
									//Additional Address From User Preferences
									$address = $appBuilder->getElement("div","","", "myAddress");
									$homeCords = array();
									$homeCords["homelat"] = "41.089492";
									$homeCords["homelng"] = "23.543186";
									$appBuilder->insertData($address, "homeCord", $homeCords);
									$addressName =  $appBuilder->getElement("span","Σπιτι Θεσσαλονίκη");
									$appBuilder->appendChild($address, $addressName);
									$appBuilder->appendChild($myLocations, $address);
								}
								else
								{
									//No lcations found
									$noLocations = $appBuilder->getElement("div");
									$msg = $multiLang->get_glb_lit($appBuilder, "", "ntf_noLoc", $locale = "");
									$appBuilder->appendChild($noLocations, $msg);
									$appBuilder->appendChild($myLocations, $noLocations);
								}
							}
							else
							{
								//Please log In
								$noLoggedIn = $appBuilder->getElement("div");
								$msg = $multiLang->get_glb_lit($appBuilder, "", "ntf_plLogIn", $locale = "");
								$appBuilder->appendChild($noLoggedIn, $msg);
								$appBuilder->appendChild($myLocations, $noLoggedIn);
							}
							$tabControl->insert_tab("myLoc", $title, $myLocations, $selected = FALSE);
						}
				
					$appBuilder->appendChild($locationSelector, $tabMenu);
				$appBuilder->appendChild($locationControl, $locationSelector);

			$appBuilder->appendChild($sideBar, $locationControl);
			
			//Results Filters Section

			//Building Filter Collection
			$filterCollection = $appBuilder->getElement("div","","","filterCollection");
				//Result Filter Menu
				$menu = new navMenu($appBuilder);
				$filterMenu = $menu->build_menu($id = "", $header = "", $class = "");
				$subList = $menu->insert_subList($filterMenu);
				$staticNav = array();
				$staticNav['targetcontainer'] = "rb_gMapContent";
				$staticNav['targetgroup'] = "filterGrp";
				$staticNav['navgroup'] = "filterGroup_".rand();
				$staticNav['display'] = "none";
				
				$staticNav['ref'] = "allDlnBranches";
				$item_text =  $multiLang->get_glb_lit($appBuilder, "", "lbl_flstByCat", $locale = "");
				$prompt = $menu->insert_listItem($subList, $item_text, "", TRUE);
				$menu->add_static_navigation($prompt, $staticNav);
				$appBuilder->appendChild($filterCollection, $filterMenu);

				
				$staticNav['ref'] = "filter_2";
				$item_text =  $multiLang->get_glb_lit($appBuilder, "", "lbl_flstByDlArea", $locale = "");
				$prompt = $menu->insert_listItem($subList, $item_text, "", FALSE);
				$menu->add_static_navigation($prompt, $staticNav);
				$appBuilder->appendChild($filterCollection, $filterMenu);
				
				//__FILTER -- Store which serve by category
				$filter = $appBuilder->getElement("div","","allDlnBranches","filter",array("fltcode" => "flt_".rand(),"targetgroupid"=>"filterGrp"));
					//Content will be added Dynamicaly by js, according to template
				$appBuilder->appendChild($filterCollection, $filter);

				//__FILTER -- which serve by Delivery Area
				$filter = $appBuilder->getElement("div","","filter_2","filter",array("fltcode" => "flt_".rand(),"targetgroupid"=>"filterGrp"));
					//Content will be added Dynamicaly by js, according to template
				$appBuilder->appendChild($filterCollection, $filter);
			
			$appBuilder->appendChild($sideBar, $filterCollection);

		$appBuilder->appendChild($controlContent, $sideBar);
		
		//Built Map
		$appBuilder->addAttribute($mapDiv,"class","deliveryFinderMap");
		break;
//_________________________areaFinder___________________________________________//
	case 'pinView':
		$attr = array();
		$attr['xmlSource'] = (!isset($_GET["xmlSource"]) || empty($_GET["xmlSource"]) ? "" : $_GET["xmlSource"]);
		
		//Add Control Srecified Attributes
		$appBuilder->addAttribute($controlContainer, "data-controlType", $mapControlType);
		$appBuilder->insertData($controlContainer, "prefs", $attr);
		//Built Map
		$appBuilder->addAttribute($mapDiv,"class","pinView");
	break;
//_________________________default___________________________________________//
	default :
		$error = $appBuilder->getElement("div");
		$appBuilder->setInnerHTML($error,$sys_rep->internal_system_error());
		$appBuilder->appendChild($controlReport, $error);
		break;
}

$appBuilder->appendElement($controlContainer);
echo $appBuilder->getHTML();
return;


// NOTIFICATIONS
<FORM id="rb_gMap" domain="global" locale="el_GR">
	<FIELD id="prmt_locChange">Αλλάξτε την Τοποθεσία σας.</FIELD>
	<FIELD id="lbl_searchAddress">Διεύθυνση</FIELD>
	<FIELD id="lbl_myLoc">Αποθηκευμένες</FIELD>
	<FIELD id="lbl_execButton">Αναζήτηση</FIELD>
	<FIELD id="lbl_flstByCat">Όλα τα Καταστήματα</FIELD><!--(Ανά Κατηγορία)-->
	<FIELD id="lbl_flstByDlArea">Καταστήματα που με Εξυπηρετουν</FIELD><!--(Ανά Κατηγορία)-->
	
	<FIELD id="ntf_noLoc">Δεν έχεται αποθηκευμένες τοποθεσίες</FIELD>
	<FIELD id="ntf_plLogIn">Δεν είστε συνδεδεμένος. αρακαλώ συνδεθήτε για να χρησιμοποιήσεται αυτη τη δυνατότητα.</FIELD>

	<FIELD id="instr_setArea"><p><h3>Ακολουθήστε τα παρακάτω βήματα για καθορίσεται μια περιοχή εξυπηρέτησης.</h3></p>
						 <p>- Βρείτε την διεύθυνση που σας ενδιαφέρει και κάντε κλικ στο χάρτη για την δημιουργία σημείου.</p>
						 <p>- Δημιουργόντας διαδοχικά σημεία σχηματίζεται μια περιοχή.</p>
						 <p>- Σύρεται τα σημεία για μεγαλύτερη ακρίβεια.</p>
						 <p>- ατήστε "Καταχώρηση" για την αποθήκευση της επιλογής σας.</p>
	</FIELD>
	<FIELD id="prmt_chooseAddr">Επίλέξτε τη διεύθυνση σας απο ττις παρακάτω.</FIELD>
	<FIELD id="ntf_noAddr">(Δεν υπάρχουν αποτελέσματα προς εμφάνιση. Βεβαιωθήτε ότι συμπληρώσατε σωστά το πεδίο της διεύθυνσης.)</FIELD>
</FORM>



*/
//#section_end#
?>