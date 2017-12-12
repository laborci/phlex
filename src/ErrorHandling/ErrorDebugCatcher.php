<?php namespace Phlex\ErrorHandling;


use Phlex\Chameleon\Middleware;

class ErrorDebugCatcher extends Middleware {

	function run() {
		try{
			$this->next();
		}catch (\Throwable $exception){
			$this->respond(ErrorResponder::class, ['exception'=>$exception]);
		}
	}

}