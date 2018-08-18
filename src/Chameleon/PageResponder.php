<?php namespace Phlex\Chameleon;

use Symfony\Component\HttpFoundation\RedirectResponse;

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

	protected function prepare(){}
	abstract protected function respond():string;
	protected function redirect($url = '/', $status = 301){
		(new RedirectResponse($url, $status, $this->getResponse()->headers->all()))->send();
	}
}