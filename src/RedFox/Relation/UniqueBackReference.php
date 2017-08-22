<?php namespace Phlex\RedFox\Relation;

use Phlex\Database\Filter;
use Phlex\RedFox\Entity;

class UniqueBackReference {

	protected $class;
	protected $field;

	public function __construct(string $class, string $field) {
		$this->class = $class;
		$this->field = $field;
	}

	public function __invoke(Entity $object){
		$class = $this->class;
		$field = $this->field;
		/** @var \Phlex\RedFox\Repository $repository */
		$repository = $class::repository();
		return $repository->search(Filter::where($field.'=$1', $object->id))->pick();
	}


	public function getRelatedClass(): string {
		return '\\'.$this->class;
	}
}