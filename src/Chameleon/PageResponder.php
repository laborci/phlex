<?php namespace Phlex\Chameleon;


use Symfony\Component\HttpFoundation\Response;


abstract class PageResponder extends Responder {

	public function __construct() {
		parent::__construct();
	}

	public function __invoke() {
		$this->prepare();
		if(method_exists($this, 'shutDown')){
			register_shutdown_function([$this, 'shutDown']);
		}
		$this->getResponse()->setContent($this->respond())->send();
	}

	abstract protected function prepare();
	abstract protected function respond():string;
}