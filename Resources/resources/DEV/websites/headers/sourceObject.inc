<?php
// Check Website Platform (prevent outside executions)
if (!defined("_RB_WEBSITE_"))
    throw new Exception("Page is not loaded properly!");

// Use Important Headers
use \WAPI\Platform\importer;
