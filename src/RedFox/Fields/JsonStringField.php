<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class JsonStringField extends Field {

	public function getDataType(){return 'array';}


	public function import($value) { return json_decode($value, true); }

	public function export($value) { return json_encode($value); }

	public function set($value) { return $value; }

}