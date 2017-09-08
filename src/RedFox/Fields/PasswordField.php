<?php namespace Phlex\RedFox\Fields;

class PasswordField extends StringField {

	protected $salt;

	public function __construct($salt) { $this->salt = $salt; }
	public function getDataType() { return 'string'; }
	public function set($value) { return $this->hash($value); }
	public function check($value, $hash) { return $this->hash($value) === $hash; }
	protected function hash($value) { return md5($this->salt . (string)$value); }

}