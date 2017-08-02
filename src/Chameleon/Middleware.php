<?php namespace Phlex\Chameleon;

use Phlex\Sys\ServiceManager;


abstract class Middleware extends Responder {

	abstract function __invoke(callable $next);

	protected function respond($responderClass, $attributes = []){
		$request = $this->getRequest();
		$request->attributes->replace($attributes);
		/** @var PageResponder $responder */
		$responder = ServiceManager::get($responderClass);
		$responder();
		die();
	}

	protected function redirect($route) {
		header('Location:' . $route);
		die();
	}
}