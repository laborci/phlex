<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class StringField extends Field {

	public function getDataType(){return 'string';}

	public function set($value) { return (string)$value; }

}