<?php namespace Phlex\ErrorHandling;

use App\ServiceManager;
use Phlex\Chameleon\Middleware;

class ErrorCatcher extends Middleware {

	function run() {
		try {
			$this->next();
		} catch (\Throwable $exception) {
			$this->respond(ServiceManager::get('Error-Page'), [ 'exception' => $exception ]);
		}
	}

}