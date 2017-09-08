<?php namespace Phlex\Chameleon;

use App\ServiceManager;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class JsonResponder extends Responder{

	final public function __invoke($method = 'respond') {
		$this->getResponse()->headers->set('Content-Type', 'application/json');
		$this->getResponse()->setContent(json_encode($this->$method()))->send();
	}

	protected function respond(){return null;}

	final protected function getJsonPayload(): array { return json_decode($this->getRequest()->getContent(), true); }
	final protected function getJsonParamBag(): ParameterBag { return new ParameterBag(json_decode($this->getRequest()->getContent(), true)); }
}