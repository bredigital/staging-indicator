<?php
/*
Plugin Name: Staging Indicator
Description: Specifies the server and version of the development site.
Version: 1.5.4
Author: BRE Digital
Author URI: http://bre.digital/
*/

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/toolbar.php';

use Symfony\Component\Dotenv\Dotenv;
use wpstagingindicator\config;
use wpstagingindicator\toolbar;

add_filter( 'plugin_row_meta', function($links, $file) { 	
	$url    = get_site_url();
	$plugin = plugin_basename( __FILE__ );
	if ( is_plugin_active($plugin) && $file == $plugin ) {
		$links[] = "<a href='{$url}/wp-admin/plugin-editor.php?file=staging-indicator%2Fconfig.env&plugin=staging-indicator%2Findex.php'>Edit</a>";
	}

	return $links;
}, 10, 2 );

$configLoader = new config();

if($configLoader->Broadcast && !empty($_GET) && !empty($_GET["c"]) && $_GET["c"] == 1) {
	header('Content-Type: application/json');
	echo json_encode( $configLoader->Sites );
	die();
} else { 
	defined( 'ABSPATH' ) or die( 'Operation not permitted.' );
	$toolbar = new toolbar( $configLoader );

	add_action( 'admin_bar_menu', [&$toolbar, 'hook'], 999 );
	add_filter( 'editable_extensions', function($editable_extensions) {
		$editable_extensions[] = 'env';
		return $editable_extensions; 
	}, 10, 1 ); 
}