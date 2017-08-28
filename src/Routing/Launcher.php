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

		/** @var \Psr\Log\LoggerInterface $logger */
		$logger = ServiceManager::get(LoggerInterface::class);
		$logger->info($request->getHost().$request->getRequestUri(), ['method'=>$request->getMethod()]);

		ServiceManager::bind('Request')->value($request);
		ServiceManager::bind('Response')->value(new Response());
		$launcher($request);
	}

}
