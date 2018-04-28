<?php

use Fun\Str;
use Ctr\TestController;

//start line
// php -d always_populate_raw_post_data=-1 -S 0.0.0.0:1234

define('ROOT', dirname(__FILE__));

//the real boot file
include ROOT.'/boot/app.php';
