<?php namespace Phlex\RedFox\Fields;

class PasswordField extends StringField {

	protected $salt = null;

	public function __construct($entityClass, $name, $salt=null) {
		parent::__construct($entityClass, $name);
		$this->salt = $salt;
	}
	public function getDataType() { return 'string'; }
	public function set($value) { return $this->hash($value); }
	public function check($value, $hash) { return $this->hash($value) === $hash; }
	protected function hash($value) { return md5($this->salt . (string)$value); }
	public function setSalt($salt){
		if(is_null($this->salt)) $this->salt = $salt;
		else throw new \Exception('SALT SET TWICE!');
	}

}