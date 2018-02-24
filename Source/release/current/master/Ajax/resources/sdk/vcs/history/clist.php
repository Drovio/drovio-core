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
importer::import("API", "Geoloc", "datetimer");
importer::import("API", "Resources", "filesystem/directory");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Content", "HTMLContent");
importer::import("UI", "Presentation", "togglers/toggler");
importer::import("UI", "Presentation", "dataGridList");
importer::import("DEV", "Version", "tools/historyManager");

use \API\Geoloc\datetimer;
use \API\Resources\filesystem\directory;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \UI\Content\HTMLContent;
use \UI\Presentation\togglers\toggler;
use \UI\Presentation\dataGridList;
use \DEV\Version\tools\historyManager;

DOM::initialize();
$pageContent = new HTMLContent();
$pageContent->build("", "clist");

// Get tabControl parameters
$hmID = $_GET['hmid'];
$page = $_GET['page'];
$page = empty($page) ? 0 : $page;
$pageCapacity = 5;

$hm = new historyManager($hmID);
$vcs = $hm->getVCS();

// Get working items
$workingBranch = $vcs->getWorkingBranch();
$commits = $vcs->getBranchCommits($workingBranch);
$authors = $vcs->getAuthors();

$totalPages = count($commits) / $pageCapacity;
$totalPages = ceil($totalPages);

// Set page attribute
$clist = $pageContent->get();
$attr['current'] = $page;
$attr['pages'] = $totalPages;
HTML::data($clist, "pagination", $attr);

$startIndex = $page * $pageCapacity;
$pageCommits = array_slice($commits, $startIndex, $pageCapacity);
foreach ($pageCommits as $commitID => $commitData)
{
	// Set toggler data
	$cnt = DOM::create("span", $commitData['summary']);
	$togHeader = DOM::create("div", $cnt);
	
	$author = DOM::create("div", $commitData['author'].", ", "", "cAuthor");
	DOM::append($togHeader, $author);
	$commitDate = DOM::create("span", "commited ", "", "cDate");
	DOM::append($author, $commitDate);
	$liveDate = datetimer::live($commitData['time']);
	DOM::append($commitDate, $liveDate);
	
	// Create item's list
	$gridList = new dataGridList();
	$gridList->build("cdlist", TRUE);
	
	$headers = array();
	$headers[] = "Path";
	$gridList->setHeaders($headers);
	
	// Get commit items
	$commitItems = $vcs->getCommitItems($commitID);
	foreach ($commitItems as $itemID => $itemInfo)
	{
		$row = array();
		$row[] = DOM::create("span", directory::normalize("/".$itemInfo['path']."/".$itemInfo['name']));
		
		$checkID = $commitID.":".$itemID;
		$gridList->insertRow($row, "restore[$checkID]");
	}
	$togBody = $gridList->get();
	
	// Build toggler
	$tog = new toggler();
	$commitViewer = $tog->build($id = $commitID, $togHeader, $togBody, $open = FALSE)->get();
	$pageContent->append($commitViewer);
}

$commitCount = count($commits);
if ($commitCount > $pageCapacity)
{
	$controlsContainer = DOM::create("div", "", "", "controls");
	$pageContent->append($controlsContainer);

	// Pagination
	$pagination = DOM::create("div", "", "", "pagination");
	DOM::append($controlsContainer, $pagination);
	
	if ($page > 0)
		$extraClass = "active";
	$newerBtn = DOM::create("span", "<< Newer", "", trim("navBtn newer ".$extraClass));
	DOM::append($pagination, $newerBtn);
	
	if ($page * $pageCapacity < $commitCount)
		$extraClass = "active";
	$olderBtn = DOM::create("span", "Older >>", "", trim("navBtn older ".$extraClass));
	DOM::append($pagination, $olderBtn);
	
	
	// Pages
	$pagesInfo = DOM::create("div", "", "", "pagesInfo");
	DOM::append($controlsContainer, $pagesInfo);
	
	$startCount = $page * $pageCapacity + 1;
	$endCount = ($commitCount < ($page + 1)*$pageCapacity ? $commitCount : ($page + 1)*$pageCapacity);
	$text = DOM::create("span", "Displaying ".$startCount." to ".$endCount." commits, from ".$commitCount." total.");
	DOM::append($pagesInfo, $text);
}
else
{	// Page Info Text
	$startCount = $page * $pageCapacity + 1;
	$endCount = ($commitCount < ($page + 1)*$pageCapacity ? $commitCount : ($page + 1)*$pageCapacity);
	$text = DOM::create("span", "Displaying ".$startCount." to ".$endCount." commits, from ".$commitCount." total.");
	$pageContent->append($text);
}

// Return the result
$holderID = $_GET['hid'];
echo $pageContent->getReport("#".$holderID." .cmContainer");
return;
//#section_end#
?>