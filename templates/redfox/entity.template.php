<?php namespace App\Entity\{{name}};

class {{name}} extends \Phlex\RedFox\Entity implements Helpers\EntityInterface{

	use Helpers\EntityTrait;

	public function __toString(){ return (string) $this->id; }

}