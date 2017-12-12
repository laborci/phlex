<?php namespace Phlex\ErrorHandling;

use Phlex\Chameleon\SmartPageResponder;

class ErrorResponder extends SmartPageResponder {

	/** @var  \Throwable */
	protected $exception;
	protected $trace;

	protected function prepare() {

		$this->exception = $this->getAttributesBag()->get('exception');
		//print_r($this->exception);
		$this->trace = $this->exception->getTrace();
	}


	protected function BODY() { ?>
		<style>
			body{margin:0; background-color: #5e5e5e}
			*{font-family: arial; font-size:12px;}
			.error{padding:10px; color:gold; background-color:#333;}
			.message{font-weight: bold; font-size: 16px;}
			.error .file{color:whitesmoke;}
			.trace{
				background-color: whitesmoke;
				margin:8px;
				padding:10px;
				border-left: 8px solid dodgerblue;
			}
			.trace .file{color:gray;}
		</style>
		<div class="error">
			<div class="message">{{.exception.getMessage()}}</div>
			<div class="file"><b>{{.exception.getFile()}}</b> (line: {{.exception.getLine()}})</div>
		</div>
		@each .trace as trace
		<div class="trace">
			<div class="call"><b>{{trace:class}}</b> {{trace:type}} {{trace:function}}()</div>
			<div class="file"><b>{{trace:file}}</b> (line: {{trace:line}})</div>
			@php $args = var_export($trace['args'], true)
			<pre class="args">{{args}}</pre>
		</div>
		@end
	<?php }
}