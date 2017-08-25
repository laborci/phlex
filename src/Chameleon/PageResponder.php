<?php namespace Phlex\Chameleon;


abstract class PageResponder extends Responder {

	private $httpResponseCode = 200;
	private $responseHeaders = [];

	public function __invoke() {
		$this->prepare();
		register_shutdown_function([$this, 'shutDown']);
		$this->sendHeaders();
		$this->respond();
	}

	abstract protected function prepare();

	abstract protected function respond();

	protected function sendHeaders() {
		http_response_code($this->httpResponseCode);
		foreach ($this->responseHeaders as $header => $value) {
			header($header . ': ' . $value);
		}
	}

	protected function addResponseHeader(string $name, string $value) { $this->responseHeaders[$name] = $value; }
	protected function setHttpResponseCode(int $code) { $this->httpResponseCode = $code; }
	public function shutDown(){}
}