<?php namespace Phlex\Routing;

use Symfony\Component\HttpFoundation\ParameterBag;


class Request extends \Symfony\Component\HttpFoundation\Request {


	/** @var ParameterBag */
	public $pathParams;

	public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
		parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
		$this->pathParams = new ParameterBag();
	}

	public function fnMatchHost($pattern) {
		return fnmatch($pattern, $this->getHost());
	}

	public function isMethod($method) {
		return $method === '*' || parent::isMethod($method);
	}

}