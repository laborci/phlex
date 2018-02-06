<?php namespace Phlex\Routing;

use App\Env;
use Phlex\Chameleon\ReRouter;
use Phlex\Chameleon\Responder;
use Phlex\Chameleon\ResponderException;
use Phlex\Routing\Request;
use Phlex\Sys\ServiceManager\ServiceManager;
use Symfony\Component\HttpFoundation\ParameterBag;

class Router {

	/** @var Request */
	private $request;
	private $middlewares = [];

	function __construct(Request $request) {
		$this->request = $request;
	}

	public function get($pattern, $pageResponderClass = null, $attributes = []): Handler {
		return $this->route(Request::METHOD_GET, $pattern, $pageResponderClass, $attributes);
	}

	public function post($pattern, $pageResponderClass = null, $attributes = []): Handler {
		return $this->route(Request::METHOD_POST, $pattern, $pageResponderClass, $attributes);
	}

	public function delete($pattern, $pageResponderClass = null, $attributes = []): Handler {
		return $this->route(Request::METHOD_DELETE, $pattern, $pageResponderClass, $attributes);
	}

	public function put($pattern, $pageResponderClass = null, $attributes = []): Handler {
		return $this->route(Request::METHOD_PUT, $pattern, $pageResponderClass, $attributes);
	}

	public function patch($pattern, $pageResponderClass = null, $attributes = []): Handler {
		return $this->route(Request::METHOD_PATCH, $pattern, $pageResponderClass, $attributes);
	}

	public function any($pattern, $pageResponderClass = null, $attributes = []): Handler {
		return $this->route('*', $pattern, $pageResponderClass, $attributes);
	}

	/**
	 * @param $method
	 * @param $pattern
	 * @param null $pageResponderClass
	 * @param array $attributes
	 * @return $this|Handler
	 */
	public function route($method, $pattern, $pageResponderClass = null, $attributes = []) {

		if ($this->request->isMethod($method)) {
			$uri = rtrim($this->request->getPathInfo(), '/');
			if (!$uri) $uri = '/';

			$patternParts = explode('|', $pattern);

			$patterns = [];
			if(count($patternParts)>1) {
				$p = '';
				for ($i = 0; $i < count($patternParts); $i++) {
					$p.=$patternParts[$i];
					array_unshift($patterns, $p);
				}
			}else{
				$patterns[] = $pattern;
			}

			foreach($patterns as $pattern) {
				if ($this->testPattern($pattern, $uri)) {
					$this->request->pathParams->replace($this->getParams($pattern, $uri));
					$this->request->attributes->replace($attributes);
					$handler = new Handler($this->request, $this->middlewares);

					if (!is_null($pageResponderClass)) {
						$handler->respond($pageResponderClass, $attributes);
					} else {
						return $handler;
					}
				}
			}

		}
		return new Handler(); // Dummy handler - does nothing;
	}

	public function addMiddleware($middlewareClass, $attributes = []){
		$this->middlewares[] = [$middlewareClass, $attributes];
		return $this;
	}

	public function clearMiddlewares(){
		$this->middlewares = [];
	}

	protected function testPattern($pattern, $uri) {
		$pattern = preg_replace('/{.*?}/', '*', $pattern);
		return $pattern[0] == '/' ? fnmatch($pattern, $uri) : preg_match($pattern, $uri);
	}

	public function getParams($pattern, $uri) {
		if (preg_match_all('/{(.*?)}/', $pattern, $keys)) {
			$keys = $keys[1];
			$valuepattern = '@^' . preg_replace('/{.*?}/', '(.*?)', $pattern) . '$@';
			preg_match($valuepattern, $uri, $values);
			array_shift($values);
			$params = array_combine($keys, $values);
			return $params;
		} else {
			return [];
		}
	}

	public function getRequest(): Request {
		return $this->request;
	}


}
