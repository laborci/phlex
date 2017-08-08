<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class PasswordField extends StringField {

	protected $salt;

	public function __construct($dbtype, $salt) {
		$this->salt = $salt;
		parent::__construct($dbtype);
	}

	public function getDataType(){return 'string';}

	public function set($value) { return $this->hash($value); }

	public function check($value, $hash){ return md5($this->salt.(string)$value) === $hash; }

}