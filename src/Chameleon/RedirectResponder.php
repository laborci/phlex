<?php namespace Phlex\Chameleon;

use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class RedirectResponder extends Responder {

	public function __construct() {
		parent::__construct();
		$this->setResponse( new RedirectResponse('/', 302, $this->getResponse()->headers->all()) );
	}

	public function __invoke() {
		if(method_exists($this, 'shutDown')){
			register_shutdown_function([$this, 'shutDown']);
		}
		/** @var RedirectResponse $response */
		$response = $this->getResponse();
		$response->setTargetUrl( $this->redirect() )->send();
	}

	/**
	 * @return string the redirect url
	 */
	abstract protected function redirect():string;
 }