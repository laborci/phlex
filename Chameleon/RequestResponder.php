<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 2017. 07. 11.
 * Time: 10:50
 */

namespace Phlex\Chameleon;


use App\Env;
use Phlex\Routing\Request;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\ServerBag;

abstract class RequestResponder extends Responder{

	/** @var \Phlex\Routing\Request  */
	private $request;

	public function __construct() {
		$this->request = Env::get('Request');
	}

	protected function getRequest(): Request { return $this->request; }
	protected function getRequestBag(): ParameterBag { return $this->getRequest()->request; }
	protected function getQueryBag(): ParameterBag { return $this->getRequest()->query; }
	protected function getPathBag(): ParameterBag { return $this->getRequest()->pathParams; }
	protected function getAttributesBag(): ParameterBag { return $this->getRequest()->attributes; }
	protected function getHeadersBag(): HeaderBag { return $this->getRequest()->headers; }
	protected function getServerBag(): ServerBag { return $this->getRequest()->server; }
	protected function getCookiesBag(): ParameterBag { return $this->getRequest()->cookies; }
	protected function getFileBag(): FileBag { return $this->getRequest()->files; }

}