<?php namespace wpstagingindicator;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use wpstagingindicator\config;
use Carbon\Carbon;

/**
 * Manages interactions with the WP Admin toolbar, and with processing entries.
 * @author Casey Lambie
 */
class toolbar {
	protected $config;
	public function __construct($config) {
		$this->config = $config;
	}
	
	/**
	 * WordPress Action. Adds the stager option to the current WP Admin bar.
	 * @param WP_Admin_Bar $wp_admin_bar
	 * @return void
	 */
	public function hook( $wp_admin_bar ) {
		//die(var_dump( $this->pluginParameters( $this->config->PluginDir ) ));
		$contents = [];

		// Tracking a plugin? Else, load up the dotenv.
		if ($this->config->PluginDir !== false && $this->pluginParameters( $this->config->PluginDir ) !== false ) {
			$pluginParams = $this->pluginParameters( $this->config->PluginDir );
			$contents[] = $pluginParams->Name;
			$contents[] = 'Version: ' . $pluginParams->Version;
			$contents[] = 'Last Updated: ' . $pluginParams->LastModified->format( $this->config->DateFormat );
		} else {
			$contents[] = get_bloginfo('name');
			($this->config->Version !== false)     ? $contents[] = 'Version: ' . $this->config->Version : null;
			($this->config->LastUpdated !== false) ? $contents[] = 'Last Updated: ' . $this->config->LastUpdated->format( $this->config->DateFormat ) : null;
		}
		($this->config->Stage !== false) ? $contents[] = $this->config->Stage : null;
		
		$this->menuConstructor( 
			$wp_admin_bar, 
			$this->config->Mini, 
			$contents, 
			$this->config->Sites, 
			$this->config->FollowLinks 
		);
	}

		/**
	 * Constructs the WordPress admin bar option.
	 * @param WP_Admin_Bar $wp_admin_bar
	 * @param boolean $mini
	 * @param array[string] $contents
	 * @param array[string] $sites
	 * @param boolean $matchURL
	 * @return void Contents will be printed on the page
	 */
	private function menuConstructor($wp_admin_bar, $mini, $contents, $sites, $matchURL) {
		$flattenedContents = implode(" - ", $contents); 

		$wp_admin_bar->add_node([
			'id'		=> 'version_no',
			'title'		=> ($mini) ? "\u{2139}" : $flattenedContents,
		]);

		if ($mini) {
			foreach ($contents as $key => $content) {
				$wp_admin_bar->add_node([
					'id'     => "mini_details_{$key}",
					'title'  => $content,
					'parent' => 'version_no'
				]);
			}

			$wp_admin_bar->add_node([
				'id'     => "mini_details_seperator",
				'title'  => '',
				'parent' => 'version_no'
			]);
		}

		foreach ($sites as $site) {
			$wp_admin_bar->add_node([
				'id'		=> "staging_site_{$site['Name']}",
				'title'		=> $site['Name'],
				'parent'    => 'version_no',
				'href'      => ($matchURL) ? $this->getUrlParams($site['URL']) : $site['URL']
			]);
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
	 * Forms a similar URL for the provided URL.
	 * @param string $id
	 * @return string
	 */
	private function getUrlParams($url) {
		global $pagenow;

		// Check to see if their on a default page, or a particular one and pass along the get parameters. Hit and miss.
		if(empty($_GET)) {
			if (is_admin()) {
				return $url . '/wp-admin/' . $pagenow;
			} else {
				return $url . '/' . $pagenow;
			}
		} else {
			if (is_admin()) {
				return $url . '/wp-admin/' . $pagenow . '?' . http_build_query($_GET);
			} else {
				return $url . '/' . $pagenow . '?' . http_build_query($_GET);
			}	
		}
	}

	/**
	 * Gets plugin details from the specified folder.
	 * @param string $folderName Folder name of tracked folder
	 * @return object stdClass - Name, Version and LastModified (Carbon).
	 */
	private function pluginParameters($folderName) {
		$file = realpath(dirname(__FILE__) . '/../') . "/{$folderName}/";

		// Get the plugin file, which is either plugin.php or folder name.
		if (file_exists( $file . "plugin.php" )) {
			$file .= "plugin.php"; 
		} elseif (file_exists( $file . "index.php" )) {
			$file .= "index.php"; 
		} elseif (file_exists( $file . "{$folderName}.php" )) {
			$file .= "{$folderName}.php"; 
		} else {
			return false;
		}
		
		$contents = get_file_data($file, [
			'Name'    => 'Plugin Name',
			'Version' => 'Version'
		]);

		$contents['LastModified'] = Carbon::createFromTimestamp( filemtime($file) );

		return (object)$contents;
	}
}