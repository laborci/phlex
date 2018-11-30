<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class IntegerField extends Field{
	public function getDataType(){return 'int';}
	public function import($value){ return intval($value); }
	public function set($value){ return intval($value); }
}