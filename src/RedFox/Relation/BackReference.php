<?php namespace Phlex\RedFox\Relation;

use Phlex\Database\Filter;
use Phlex\RedFox\Entity;

class BackReference {

	protected $class;
	protected $field;

	public function __construct(string $class, string $field) {
		$this->class = $class;
		$this->field = $field;
	}

	public function __invoke(Entity $object, $order=null, $limit=null, $offset = 0):array{
		$class = $this->class;
		$field = $this->field;
		/** @var \Phlex\RedFox\Repository $repository */
		$repository = $class::repository();
		return $repository->search(Filter::where($field.'=$1', $object->id))->orderIf(!is_null($order), $order)->collect($limit, $offset);
	}


	public function getRelatedClass(): string {
		return '\\'.$this->class.'[]';
	}
}