<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class FloatField extends Field {

	public function getDataType(){return 'float';}

	public function import($value) { return floatval($value); }
	public function set($value) { return floatval($value); }

}