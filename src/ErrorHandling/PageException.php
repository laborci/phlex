<?php namespace Phlex\ErrorHandling;
use Throwable;

class PageException extends \Exception {

	static function notFound($message=''){
		return new static($message, 404);
	}

	static function unauthorized($message=''){
		return new static($message, 401);
	}

	static function forbidden($message=''){
		return new static($message, 403);
	}

}