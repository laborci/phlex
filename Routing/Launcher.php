<?php namespace Phlex\Routing;

use App\Env;

class Launcher {

	protected $sites = [];

	public function __construct(...$sites) {
		$this->sites = $sites;
	}

	public function __invoke(Request $request) {
		foreach ($this->sites as $site) {
			$site = new $site();
			$site($request);
		}
	}

	public static function launch(...$sites) {
		$launcher = new static(...$sites);
		/** @var Request $request */
		$request = Request::createFromGlobals();
		Env::bind('Request')->value($request);
		$launcher($request);
	}

}
