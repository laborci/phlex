<?php namespace Phlex\RedFox\Relation;

use Phlex\Database\DataSource;
use Phlex\Database\Filter;
use Phlex\Database\Finder;
use Phlex\RedFox\Entity;
use Phlex\RedFox\Repository;

class CrossReference {

	protected $table;
	protected $class;
	protected $access;
	protected $selfField;
	protected $otherField;

	public function __construct(DataSource $dataSource, $class, $selfField, $otherField) {
		$this->class = $class;
		$this->selfField = $selfField;
		$this->otherField = $otherField;
		$this->table = $dataSource->getTable();
		$this->access = $dataSource->getAccess();
	}

	public function __invoke(Entity $object) {
		$class = $this->class;
		$req = new Finder($this->access);
		$req->select($this->otherField . " as __VALUE__")->from($this->table)->where(Filter::where('`' . $this->selfField . '`=$1', $object->id));
		$rows = $req->collect();
		$rels = [];
		foreach ($rows as $row){
			$rels[] = $row['__VALUE__'];
		}
		/** @var Repository $repository */
		$repository = $class::repository();
		return $repository->collect($rels);
	}

	public function getRelatedClass(): string {
		return '\\'.$this->class.'[]';
	}
}