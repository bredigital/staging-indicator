<?php namespace wpstagingindicator;

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Carbon\Carbon;

/**
 * Processes configuration in the dotenv file.
 * @author Casey Lambie
 */
class config {
	protected $dotenv;
	public function __construct() {
		$this->dotenv = new Dotenv();
		$this->dotenv->load(__DIR__.'/config.env');
	}

	/**
	 * User-friendly configuration for usage in PHP, from the dotenv file.
	 * @return array[mixed]
	 */
	public function load() {
		$dFormat = (getenv('LUFORMAT') == "") ? "d/m/Y" : getenv('LUFORMAT'); 
		$config = [  
			'Mini'        => (getenv('MINI') == "")            ? false : filter_var(getenv("MINI"), FILTER_VALIDATE_BOOLEAN), 
			'PluginDir'   => (getenv('PLUGIN_DIR') == "")      ? false : getenv('PLUGIN_DIR'),
			'Version'     => (getenv('VERSION') == "")         ? false : getenv('VERSION'),
			'Stage'       => (getenv('STAGE') == "")           ? false : getenv('STAGE'),
			'DateFormat'  => $dFormat,
			'LastUpdated' => (getenv('LASTUPDATE') == "")     ? false : Carbon::createFromFormat( $dFormat, getenv('LASTUPDATE') ),
			'FollowLinks' => (getenv('SITE_FOLLOW_URL') == "") ? false : filter_var(getenv("SITE_FOLLOW_URL"), FILTER_VALIDATE_BOOLEAN),
			'Sites'       => []
		];

		if ((int)getenv('SITE_TOTAL') != 0) {
			for ($i = 0; $i < (int)getenv('SITE_TOTAL'); $i++) { 
				$j = $i + 1;
				if (getenv("SITE_{$j}_NAME") !== false) {
					$config['Sites'][] = [
						'Name' => getenv("SITE_{$j}_NAME"),
						'URL'  => getenv("SITE_{$j}_URL")
					];
				}
			}
		}

		return (object)$config;
	}
}