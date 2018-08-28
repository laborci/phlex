<?php namespace Phlex\RedFox\Relation;

use Phlex\RedFox\Entity;

class Reference {

	protected $class;
	protected $field;

	/**
	 * Reference constructor.
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
	 * @return mixed|null
	 */
	public function __invoke(Entity $object){
		$class = $this->class;
		$field = $this->field;
		/** @var \Phlex\RedFox\Repository $repository */
		$repository = $class::repository();
		return is_null($object->$field) ? null : $repository->pick($object->$field);
	}

	/**
	 * @return string
	 */
	public function getRelatedClass(): string {
		return '\\'.$this->class;
	}

}