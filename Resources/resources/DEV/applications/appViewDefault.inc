<?php
//---------- AUTO-GENERATED CODE ----------//
use \UI\Apps\APPContent;

// Create Application Content
$appContent = new APPContent();

// Get action factory
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "application_content_class", TRUE);

// Add a hello world dynamic content
$hw = DOM::create("p", "Hello World!");
$appContent->append($hw);

// Return output
return $appContent->getReport();
?>