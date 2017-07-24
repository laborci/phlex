<?php namespace Phlex\Routing;


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

	abstract protected function domainMatch(Request $request):bool;

}