<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 2017. 07. 11.
 * Time: 18:35
 */

namespace Phlex\ErrorHandling;


use Phlex\Chameleon\Middleware;

class ErrorCatcher extends Middleware {

	function __invoke(callable $next) {
		try{
			$next();
		}catch (\Throwable $exception){
			$this->respond(ErrorResponder::class, ['exception'=>$exception]);
		}
	}
}