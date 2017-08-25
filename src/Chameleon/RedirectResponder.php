<?php namespace Phlex\Chameleon;

abstract class RedirectResponder extends PageResponder {


	 public function __invoke() {
		 register_shutdown_function([$this, 'shutDown']);
		 $redirect = $this->redirect();
		 $this->sendHeaders();
		 header('Location:' . $redirect);
		 die();
	 }

	 abstract protected function redirect():string;

	 protected final function prepare() {}

	 protected final function respond() {}
 }