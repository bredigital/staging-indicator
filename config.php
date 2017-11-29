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
	public $Sites;

	/**
	 * User-friendly configuration for usage in PHP, from the dotenv file.
	 */
	public function __construct() {
		$this->dotenv = new Dotenv();
		$this->dotenv->load(__DIR__.'/config.env');

		$this->DateFormat  = (getenv('LUFORMAT') == "")        ? "d/m/Y" : getenv('LUFORMAT'); 
		$this->Mini        = (getenv('MINI') == "")            ? false   : filter_var(getenv("MINI"), FILTER_VALIDATE_BOOLEAN);
		$this->PluginDir   = (getenv('PLUGIN_DIR') == "")      ? false   : getenv('PLUGIN_DIR');
		$this->Version     = (getenv('VERSION') == "")         ? false   : getenv('VERSION');
		$this->Stage       = (getenv('STAGE') == "")           ? false   : getenv('STAGE');
		$this->LastUpdated = (getenv('LASTUPDATE') == "")      ? false   : Carbon::createFromFormat( $this->DateFormat, getenv('LASTUPDATE') );
		$this->FollowLinks = (getenv('SITE_FOLLOW_URL') == "") ? false   : filter_var(getenv("SITE_FOLLOW_URL"), FILTER_VALIDATE_BOOLEAN);
		$this->Sites       = [];

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
}