<?php namespace Phlex\Chameleon;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class JsonResponder extends Responder {

	final public function __invoke($method = 'respond') {
		$this->getResponse()->headers->set('Content-Type', 'application/json');
		$response = json_encode($this->$method());
		$this->getResponse()->setContent($response)->send();
	}

	protected function respond() { return null; }

	final protected function getJsonPayload(): array { return json_decode($this->getRequest()->getContent(), true); }
	final protected function getJsonParamBag(): ParameterBag {
		$data = json_decode($this->getRequest()->getContent(), true);
		$data = is_array($data) ? $data : [];
		return new ParameterBag($data);
	}
}