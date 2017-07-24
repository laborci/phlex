<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class BoolField extends Field{

	public function getDataType(){return 'bool';}

	public function import($value){
		return (bool)$value;
	}

	public function set($value){
		return (bool)$value;
	}

}