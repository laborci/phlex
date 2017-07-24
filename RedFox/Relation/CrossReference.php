<?php namespace Phlex\RedFox\Relation;

use Phlex\Database\DataSource;
use Phlex\Database\Filter;
use Phlex\Database\Request;
use Phlex\RedFox\Entity;

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
		$req = new Request($this->access);
		$req->select($this->otherField . " as __VALUE__")->from($this->table)->where(Filter::where('`' . $this->selfField . '`=$1', $object->id));
		$rels = $req->collect();
		return $class::repository()->collect($rels);
	}
	public function getRelatedClass(): string {
		return '\\'.$this->class.'[]';
	}
}