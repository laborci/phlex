<?php namespace Phlex\RedFox\Relation;

use Phlex\Database\Filter;
use Phlex\RedFox\Entity;

class BackReference {

	protected $class;
	protected $field;

	/**
	 * BackReference constructor.
	 *
	 * @param string $class
	 * @param string $field
	 */
	public function __construct(string $class, string $field) {
		$this->class = $class;
		$this->field = $field;
	}

	/**
	 * @param \Phlex\RedFox\Entity $object
	 * @return mixed
	 */
	public function __invoke(Entity $object){
		$class = $this->class;
		$field = $this->field;
		/** @var \Phlex\RedFox\Repository $repository */
		$repository = $class::repository();
		return $repository->getSourceRequest(Filter::where($field.'=$1', $object->id))->collect();
	}
	public function getRelatedClass(): string {
		return '\\'.$this->class.'[]';
	}
}