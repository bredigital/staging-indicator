<?php
/*
Plugin Name: Staging Indicator
Description: Specifies the server and version of the development site.
Version: 1.5
Author: BRE Digital
Author URI: http://bre.digital/
*/

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/toolbar.php';

use Symfony\Component\Dotenv\Dotenv;
use wpstagingindicator\config;
use wpstagingindicator\toolbar;

$toolbar = new toolbar( new config() );

add_action( 'admin_bar_menu', [&$toolbar, 'hook'], 999 );
add_filter( 'editable_extensions', function($editable_extensions) {
	$editable_extensions[] = 'env';
    return $editable_extensions; 
}, 10, 1 ); 