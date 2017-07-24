<?php namespace Phlex\Chameleon;

use App\Env;

abstract class Middleware extends Responder {

	abstract function __invoke(callable $next);

	protected function respond($responderClass, $attributes = []){
		$request = $this->getRequest();
		$request->attributes->replace($attributes);
		/** @var PageResponder $responder */
		$responder = Env::get($responderClass);
		$responder();
		die();
	}

	protected function redirect($route) {
		header('Location:' . $route);
		die();
	}
}