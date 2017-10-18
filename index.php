<?php
/*
Plugin Name: Staging Indicator
Description: Specifies the server and version of the development site.
Version: 1.4.2
Author: BRE Digital
Author URI: http://bre.digital/
*/

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Hooks
add_action( 'admin_bar_menu',      'stager_customize_toolbar',   999 );
add_filter( 'editable_extensions', 'filter_editable_extensions', 10, 1 ); 

/**
 * WordPress Action. Adds the stager option to the current WP Admin bar.
 * @param WP_Admin_Bar $wp_admin_bar
 * @return void
 */
function stager_customize_toolbar( $wp_admin_bar ){
	$error = False;
	$dotenv = new Dotenv();
	try {
		$dotenv->load(__DIR__.'/config.env');
	} catch(Exception $e) {
		$error = True;
		error_log($e->getMessage());
	}

	$mini_mode = (getenv("MINI") != "") ? filter_var(getenv("MINI"), FILTER_VALIDATE_BOOLEAN) : false;

	$arrInfo = [];

	// Adds the .env details.
	if (!$error) {
		if ($mini_mode) {
			(getenv('VERSION') != "") ? $arrInfo[] = "Version " . getenv('VERSION') : null;
			(getenv('STAGE') != "") ? $arrInfo[] = getenv('STAGE') : null;
			if (count($arrInfo) == 0) {
				$arrInfo[] = /*"ℹ️"*/"\u{2139}";
			}
		} else {
			$arrInfo[] = get_bloginfo('name'); 
			(getenv('VERSION') != "") ? $arrInfo[] = "Version " . getenv('VERSION') : null;
			(getenv('STAGE') != "") ? $arrInfo[] = getenv('STAGE') : null;
			(getenv('LASTUPDATE') != "") ? $arrInfo[] = "Last updated: " . getenv('LASTUPDATE') : null;
		}
	} else {
		$arrInfo[] = "An error occured. Error has been logged.";
	}

	$strHeadline = implode(" - ", $arrInfo); 

	$wp_admin_bar->add_node([
		'id'		=> 'version_no',
		'title'		=> $strHeadline,
	]);

	if ($mini_mode) {
		if (getenv('LASTUPDATE') != "") {
			$wp_admin_bar->add_node([
				'id'     => 'mini_details',
				'title'  => 'Last updated: ' . getenv('LASTUPDATE'),
				'parent' => 'version_no'
			]);
		}
	}

	// Generates a switcher menu, if values are present (visibility based on _NAME value).
	for ($i = 1; $i < (getenv('SITE_TOTAL') + 1); $i++) { 
		if (getenv("SITE_{$i}_NAME") != "") {	
			$wp_admin_bar->add_node([
				'id'		=> "staging_site_{$i}",
				'title'		=> getenv("SITE_{$i}_NAME"),
				'parent'    => 'version_no',
				'href'      => getUrl($i)
			]);
		}
	}

	$wp_admin_bar->add_group([
		'id'		=> 'version_no_misc',
		'parent'    => 'version_no',
		'meta'      => [
			'class' => 'ab-sub-secondary ab-submenu'
		]
	]);

	$wp_admin_bar->add_node([
		'id'		=> "edit_env",
		'title'		=> "Edit",
		'parent'    => 'version_no_misc',
		'href'      => get_site_url().'/wp-admin/plugin-editor.php?file=staging-indicator%2Fconfig.env&plugin=staging-indicator%2Findex.php'
	]);
}

/**
 * WordPress Filter. Adds .env to the list of WordPress editable files.
 * @param array $editable_extensions
 * @return void
 */
function filter_editable_extensions( $editable_extensions ) { 
	$editable_extensions[] = 'env';
    return $editable_extensions; 
}

/**
 * Gets the site URL from the provided env ID, with params if URL following is enabled.
 * @param integer $id
 * @return void
 */
function getUrl($id) {
	global $pagenow;

	$followUrl = (getenv("SITE_FOLLOW_URL") != "") ? filter_var(getenv("SITE_FOLLOW_URL"), FILTER_VALIDATE_BOOLEAN) : false;

	// Check to see if a URL has been set.
	if (getenv("SITE_{$id}_NAME") != "") {
		// Does the config allow URL matching.
		if($followUrl) {
			// Check to see if their on a default page, or a particular one and pass along the get parameters. Hit and miss.
			if(empty($_GET)) {
				if (is_admin()) {
					return getenv("SITE_{$id}_URL") . '/wp-admin/' . $pagenow;
				} else {
					return getenv("SITE_{$id}_URL") . '/' . $pagenow;
				}
			} else {
				if (is_admin()) {
					return getenv("SITE_{$id}_URL") . '/wp-admin/' . $pagenow . '?' . http_build_query($_GET);
				} else {
					return getenv("SITE_{$id}_URL") . '/' . $pagenow . '?' . http_build_query($_GET);
				}
				
			}
		} else {
			return getenv("SITE_{$id}_URL");
		}
	} else {
		return "";
	}
}