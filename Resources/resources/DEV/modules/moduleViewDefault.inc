<?php
//---------- AUTO-GENERATED CODE ----------//
use \UI\Modules\MContent;

// Create Module Page
$pageContent = new MContent($moduleID);

// Get action factory
$actionFactory = $pageContent->getActionFactory();

// Build the module content
$pageContent->build("", "uc");

// Add a hello world dynamic content
$hw = DOM::create("p", "Hello World!");
$pageContent->append($hw);

// Return output
return $pageContent->getReport();
?>