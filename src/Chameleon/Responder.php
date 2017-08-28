<?php namespace Phlex\Chameleon;

use Phlex\Routing\Request;
use Symfony\Component\HttpFoundation\Response;
use App\ServiceManager;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\ServerBag;


abstract class Responder{

	/** @var \Phlex\Routing\Request  */
	private $request;
	/** @var  \Symfony\Component\HttpFoundation\Response */
	private $response;

	public function __construct() {
		$this->request = ServiceManager::get('Request');
		$this->response = ServiceManager::get('Response');
	}

	abstract function __invoke();

	protected function getRequest(): Request { return $this->request; }
	protected function getResponse(): Response { return $this->response; }
	protected function setResponse(Response $response) { $this->response = $response; }
	protected function getRequestBag(): ParameterBag { return $this->getRequest()->request; }
	protected function getQueryBag(): ParameterBag { return $this->getRequest()->query; }
	protected function getPathBag(): ParameterBag { return $this->getRequest()->pathParams; }
	protected function getAttributesBag(): ParameterBag { return $this->getRequest()->attributes; }
	protected function getHeadersBag(): HeaderBag { return $this->getRequest()->headers; }
	protected function getServerBag(): ServerBag { return $this->getRequest()->server; }
	protected function getCookiesBag(): ParameterBag { return $this->getRequest()->cookies; }
	protected function getFileBag(): FileBag { return $this->getRequest()->files; }

}