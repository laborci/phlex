<?php namespace Phlex\Chameleon;

use Phlex\Sys\ServiceManager;
use Symfony\Component\HttpFoundation\RedirectResponse;


abstract class Middleware extends Responder {

	private $nextItem;

	public function __invoke(){
		$this->run();
	}

	public function setNext(callable $next){
		$this->nextItem = $next;
	}

	public function next(){
		($this->nextItem)();
	}

	abstract protected function run();

	protected function respond($responderClass, $attributes = []){
		$request = $this->getRequest();
		$request->attributes->replace($attributes);
		/** @var PageResponder $responder */
		$responder = ServiceManager::get($responderClass);
		$responder();
		die();
	}

	protected function redirect($url, $statusCode = 302) {
		RedirectResponse::create($url, $statusCode, $this->getResponse()->headers->all())->send();
		die();
	}
}