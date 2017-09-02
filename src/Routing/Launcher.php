<?php namespace Phlex\Routing;

use App\ServiceManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;


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

		ServiceManager::getLogger()->request($request->getMethod(), $request->getHost().$request->getRequestUri());

		ServiceManager::bind('Request')->value($request);
		ServiceManager::bind('Response')->value(new Response());
		$launcher($request);
	}

}
