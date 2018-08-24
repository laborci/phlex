<?php namespace Phlex\RedFox\Relation;

use App\ServiceManager;
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
		$relatedIds = $this->getRelatedIds($object);
		/** @var Repository $repository */
		$repository = $this->class::repository();
		return $repository->collect($relatedIds);
	}

	public function getRelatedIds(Entity $object){
		return $this->access->getValues("SELECT ".$this->otherField." FROM ".$this->table." WHERE ".$this->selfField."=$1", $object->id);
	}

	public function getRelatedClass(): string {
		return '\\'.$this->class.'[]';
	}

	public function store(Entity $item, $ids, $delete = true){
		$relateds = $this->getRelatedIds($item);
		$toDeletes = array_values(array_diff($relateds, $ids));
		$toAdds = array_values(array_diff($ids, $relateds));

		foreach ($toAdds as $toAdd) {
			$this->access->insert($this->table, [
				$this->selfField => $item->id,
				$this->otherField => $toAdd
			]);
		}

		foreach ($toDeletes as $toDelete){
			$this->access->delete($this->table,
				Filter::where($this->selfField.'='.$item->id)
					->and($this->otherField."=$1", $toDelete));
		}
	}
}