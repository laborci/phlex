<?php namespace Phlex\Routing;


use App\Env;
use Phlex\Sys\ServiceManager\ServiceManager;
use zpt\anno\Annotations;

abstract class Site{

	protected $charset = 'utf-8';
	protected $locale = 'hu_HU.UTF-8';
	protected $timezone = 'Europe/Budapest';
	/** @var  Request */
	protected $request;

	protected function init() {
		mb_internal_encoding(strtoupper($this->charset));
		setlocale(LC_ALL, $this->locale);
		date_default_timezone_set($this->timezone);
	}

	final public function __invoke(Request $request){
		if(!$this->domainMatch($request)) return;
		$this->request = $request;
		$this->init();
		$this->route( new Router($this->request) );
		die();
	}

	abstract protected function route(Router $router);

	protected function domainMatch(Request $request):bool{
		$annotations = (new Annotations(new \ReflectionClass($this)))->asArray();
		$domain = str_replace('@', Env::get('domain'), $annotations['domain']);
		return $request->fnMatchHost(...preg_split('/\s+/', $domain));
	}

}