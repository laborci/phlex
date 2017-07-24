<?php namespace Phlex\Chameleon;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class JsonResponder extends PageResponder{

	protected $data;

	final public function __invoke() {
		$this->addResponseHeader('Content-Type', 'application/json');
		parent::__invoke();
	}

	final protected function respond(){	echo json_encode($this->data); }
	final protected function getJsonPayload(): array { return json_decode($this->getRequest()->getContent(), true); }
	final protected function getJsonParamBag(): ParameterBag { return new ParameterBag(json_decode($this->getRequest()->getContent(), true)); }
}