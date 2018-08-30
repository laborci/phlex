<?php namespace Phlex\Parser\TRex\Claw;

class Token {
	public $type, $value;
	public function __construct($type, $value) {
		$this->type = $type;
		$this->value = $value;
	}
}