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
	public function stager_customize_toolbar( $wp_admin_bar ) {
		$arrInfo = [];

		// Adds the .env details.
		if ($this->config->Mini) {
			($this->config->Version !== false) ? $arrInfo[] = "Version " . $this->config->Version : null;
			($this->config->Stage   !== false) ? $arrInfo[] = $this->config->Stage                : null;
			if (count($arrInfo) == 0) {
				$arrInfo[] = "\u{2139}";
			}
		} else {
			$arrInfo[] = get_bloginfo('name'); 
			($this->config->Version     !== false) ? $arrInfo[] = "Version " . $this->config->Version                            : null;
			($this->config->Stage       !== false) ? $arrInfo[] = $this->config->Stage                                           : null;
			($this->config->LastUpdated !== false) ? $arrInfo[] = "Last updated: " . $this->config->LastUpdated->format('d/m/Y') : null;
		}

		$strHeadline = implode(" - ", $arrInfo); 

		$wp_admin_bar->add_node([
			'id'		=> 'version_no',
			'title'		=> $strHeadline,
		]);

		if ($this->config->Mini) {
			if ($this->config->LastUpdated !== false) {
				$wp_admin_bar->add_node([
					'id'     => 'mini_details',
					'title'  => 'Last updated: ' . $this->config->LastUpdated->format('d/m/Y'),
					'parent' => 'version_no'
				]);
			}
		}

		foreach ($this->config->Sites as $site) {
			$wp_admin_bar->add_node([
				'id'		=> "staging_site_{$site['Name']}",
				'title'		=> $site['Name'],
				'parent'    => 'version_no',
				'href'      => ($this->config->FollowLinks) ? $this->getUrlParams($site['URL']) : $site['URL']
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
}