<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class IdField extends Field {

	public function getDataType(){return 'int';}

	public function import($value) { return is_null($value) || $value == 0 ? null : intval($value); }
	public function set($value) { return is_null($value) || $value == 0 ? null : intval($value); }

}