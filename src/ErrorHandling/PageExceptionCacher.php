<?php namespace Phlex\ErrorHandling;

use App\ServiceManager;
use Phlex\Chameleon\Middleware;

class PageExceptionCacher extends Middleware {

	function run() {
		try{
			$this->next();
		}catch (PageException $exception){
			$this->respond(ServiceManager::get($exception->getCode().'-Error-Page'), ['exception'=>$exception]);
		}
	}
}