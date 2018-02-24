<?php
//#section#[header]
require_once($_SERVER['DOCUMENT_ROOT'].'/_domainConfig.php');

// Importer
use \API\Platform\importer;

// Engine Start
importer::import("API", "Platform", "engine");
use \API\Platform\engine;
engine::start();

use \Exception;

// Important Headers
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "reports/JSONServerReport");
use \ESS\Protocol\AsCoProtocol;

// Set Ascop Variables
if (isset($_REQUEST['__REQUEST']))
{
	$GLOBALS['__REQUEST'] = $_REQUEST['__REQUEST'];
	unset($_REQUEST['__REQUEST']);
}
if (isset($_REQUEST['__ASCOP']))
{
	AsCoProtocol::set($_REQUEST['__ASCOP']);
	unset($_REQUEST['__ASCOP']);
}

// Set the default request headers
\ESS\Protocol\reports\JSONServerReport::setResponseHeaders();
//#section_end#
//#section#[code]
importer::import("SYS", "Geoloc", "locale");
importer::import("API", "Literals", "literal");
importer::import("UI", "Presentation", "frames/dialogFrame");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Content", "HTMLContent");

use \SYS\Geoloc\locale;
use \API\Literals\literal;
use \UI\Presentation\frames\dialogFrame;
use \UI\Html\HTML;
use \UI\Content\HTMLContent;

// Initialize DOM
HTML::initialize();

// Handle post
if (engine::isPost())
{
	// Initialize content
	$pageContent = new HTMLContent();
	$actionFactory = $pageContent->getActionFactory();
	
	// Set locale
	$locale = $_POST['locale'];
	locale::set($locale);
	
	// Reload page
	echo $actionFactory->getReportReload($formSubmit = TRUE);
	return;
}

// Build geoloc dialog frame
$frame = new dialogFrame();
$title = literal::get("global.geoloc", "hd_geolocDialog");
$frame->build($title, $action = "/ajax/global/geoloc.php", $background = TRUE);

// Get form
$form = $frame->getFormFactory();

// Get Active Locale
$activeLocale = locale::active();
$localeOptions = array();
foreach ($activeLocale as $locale)
{
	$localeOptions[$locale['region_id']] = array();
	$localeOptions[$locale['region_id']][] = $form->getOption($locale['friendlyName'], $locale['countryCode_ISO2A'].":".$locale['locale'], (locale::get() == $locale['locale']));
}

$regionOptGroups = array();
foreach ($activeLocale as $locale)
	$regionOptGroups[$locale['region_id']] = $form->getOptionGroup($locale['name'], $localeOptions[$locale['region_id']]);


$title = literal::get("global.geoloc", "lbl_language");
$input = $form->getSelect($name = "locale", $multiple = FALSE, $class = "", $options = array());
foreach ($regionOptGroups as $optionGroup)
	HTML::append($input, $optionGroup);
$inputRow = $form->buildRow($title, $input, $required = TRUE, $notes = "");
$frame->append($inputRow);




echo $frame->getFrame();
return;
//#section_end#
?>