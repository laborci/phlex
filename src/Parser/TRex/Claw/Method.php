<?php namespace Phlex\Parser\TRex\Claw;

class Method {
	public $name, $args;
	public function __construct($name, $args) {
		$this->name = $name;
		$this->args = $args;
	}
}