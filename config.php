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

	public $Mini;
	public $PluginDir;
	public $Version;
	public $Stage;
	public $DateFormat;
	public $LastUpdated;
	public $FollowLinks;
	public $ExternalSites;
	public $Sites;

	/**
	 * User-friendly configuration for usage in PHP, from the dotenv file.
	 */
	public function __construct() {
		$this->dotenv = new Dotenv();
		$this->dotenv->load(__DIR__.'/config.env');

		$this->DateFormat    = (getenv('LUFORMAT') == "")        ? "d/m/Y" : getenv('LUFORMAT'); 
		$this->Mini          = (getenv('MINI') == "")            ? false   : filter_var(getenv("MINI"), FILTER_VALIDATE_BOOLEAN);
		$this->PluginDir     = (getenv('PLUGIN_DIR') == "")      ? false   : getenv('PLUGIN_DIR');
		$this->Version       = (getenv('VERSION') == "")         ? false   : getenv('VERSION');
		$this->Stage         = (getenv('STAGE') == "")           ? false   : getenv('STAGE');
		$this->LastUpdated   = (getenv('LASTUPDATE') == "")      ? false   : Carbon::createFromFormat( $this->DateFormat, getenv('LASTUPDATE') );		
		$this->Broadcast     = (getenv('SITE_BROADCAST') == "")  ? false   : filter_var(getenv("SITE_BROADCAST"), FILTER_VALIDATE_BOOLEAN);
		$this->FollowLinks   = (getenv('SITE_FOLLOW_URL') == "") ? false   : filter_var(getenv("SITE_FOLLOW_URL"), FILTER_VALIDATE_BOOLEAN);
		$this->ExternalSites = (getenv('SITE_EXT') == "")        ? false   : getenv('SITE_EXT');
		$this->Sites         = [];

		if ((int)getenv('SITE_TOTAL') != 0) {
			for ($i = 0; $i < (int)getenv('SITE_TOTAL'); $i++) { 
				$j = $i + 1;
				if (getenv("SITE_{$j}_NAME") !== false) {
					$this->Sites[] = [
						'Name' => getenv("SITE_{$j}_NAME"),
						'URL'  => getenv("SITE_{$j}_URL")
					];
				}
			}
		}
		
	}

	/**
	 * Pull sites config from an externally provided source.
	 * @param string $url
	 * @param boolean $cache Recommended.
	 * @return object
	 */
	public function loadExternalSites($url, $cache = true) {
		$pluginUrl = "{$url}/wp-content/staging-indicator/index.php?c=1";
		if ( !$this->validateResource($pluginUrl) ) {
			return [];
		}

		if($cache) {
			$cache_file = __DIR__.'/externalsites.json.cache';
			if(file_exists($cache_file) && file_get_contents( $cache_file ) !== "") {
				if(time() - filemtime($cache_file) > 1200) {
					// Timeout of 20 minutes.
					$cache = file_get_contents( $pluginUrl );
					file_put_contents($cache_file, $cache);
				}
			} else {
				// Called when no cache file exists.
				$cache = file_get_contents($pluginUrl);
				file_put_contents($cache_file, $cache);
			}

			return json_decode( file_get_contents($cache_file), true );
		} else {
			return json_decode( file_get_contents($pluginUrl), true );
		}
	}

	/**
	 * Checks to see if the external resource exists, and responds what is expected.
	 * @todo  Test on Apache/NGINX. Works on IIS.
	 * @param string $url
	 * @return boolean
	 */
	private function validateResource($url) {
		$getHeaders = get_headers($url);
		$headers = [
			'code' => substr( $getHeaders[0] , 9, 3 ),
			'type' => substr( $getHeaders[1] , 14 ) 
		];

		if ($headers['code'] == '200' && $headers['type'] == 'application/json')
			return true;
		else
			return false;
	}
}