<?php namespace Phlex\Routing;

use Phlex\Chameleon\Middleware;
use Phlex\Chameleon\PageResponder;
use Phlex\Chameleon\Responder;
use App\ServiceManager;


final class Handler {

	protected $request = null;
	protected $pipeline = [];

	function __construct(Request $request = null, $middlewares = []) {
		if (!is_null($request)) $this->request = $request;
		foreach ($middlewares as $middleware){
			list($middlewareClass, $attributes) = $middleware;
			$this->addMiddleware($middlewareClass, $attributes);
		}
	}

	protected function run() {
		$element = reset($this->pipeline);
		$element();
	}

	protected function runMiddleware($middlewareClass, $attributes) {
		$this->request->attributes->replace($attributes);
		$middleware = $this->createMiddleware($middlewareClass);
		$middleware->setNext( next($this->pipeline) );
		$middleware();
	}

	protected function runResponder($responderClass, $attributes) {
		$this->request->attributes->replace($attributes);
		if(is_array($responderClass)){
			$method = $responderClass[1];
			$responderClass = $responderClass[0];
		}else{
			$method = null;
		}
		$responder = $this->createResponder($responderClass);
		is_null($method) ? $responder() : $responder($method);
		die();
	}

	protected function runRedirect($route) {
		header('Location:' . $route);
		die();
	}


	/**
	 * @param $middlewareClass
	 * @param array $attributes
	 * @return $this
	 */
	public function addMiddleware($middlewareClass, $attributes = []) {
		if (!is_null($this->request)) $this->pipeline[] = function () use ($middlewareClass, $attributes) {
			$this->runMiddleware($middlewareClass, $attributes);
		};
		return $this;
	}

	public function addMiddlewares(array $group) {
		if (!is_null($this->request)) {
			foreach ($group as $item) {
				if(is_array($item)){
					list($middlewareClass, $attributes) = $item;
				}else{
					$middlewareClass = $item;
					$attributes = [];
				}
				$this->pipeline[] = function () use ($middlewareClass, $attributes) {
					$this->runMiddleware($middlewareClass, $attributes);
				};
			}
		}
		return $this;
	}

	/**
	 * @param $responderClass
	 * @param array $attributes
	 */
	public function respond($responderClass, $attributes = []) {
		if (is_null($this->request)) return;
		$this->pipeline[] = function () use ($responderClass, $attributes) {
			$this->runResponder($responderClass, $attributes);
		};
		$this->run();
	}

	/**
	 * @param $route
	 */
	public function redirect($route) {
		if (is_null($this->request)) return;
		$this->pipeline[] = function () use ($route) { $this->runRedirect($route); };
		$this->run();
	}


	private function createMiddleware($middlewareClass): Middleware {
		return ServiceManager::get($middlewareClass);
	}

	private function createResponder($responderClass): Responder {
		return ServiceManager::get($responderClass);
	}

}