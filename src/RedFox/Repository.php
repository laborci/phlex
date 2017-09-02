<?php namespace Phlex\RedFox;

use Phlex\Database\DataSource;
use Phlex\Database\Filter;
use Phlex\Database\Finder;

abstract class Repository {

	/** @var \Phlex\Database\DataSource  */
	protected $dataSource;
	protected $entityClass;
	/** @var \Phlex\RedFox\Cache  */
	protected $cache;

	public function __construct(DataSource $dataSource, string $entityClass) {
		$this->entityClass = $entityClass;
		$this->dataSource = $dataSource;
		$this->cache = new Cache();
	}

	private function addToCache(Entity $object){
		$this->cache->add($object);
	}

	/**
	 * Creates the default DataSource for the entity.
	 * @return \Phlex\Database\DataSource
	 */

	public function getDataSource():DataSource{ return $this->dataSource; }

	public function pick(int $id, bool $strict = true) {
		$cached = $this->cache->get($id);
		if(!is_null($cached)) return $cached;
		$data = $this->dataSource->pick($id);
		if($strict) $this->throwExceptionOnEmpty($data);
		$object = new $this->entityClass($data, $this);
		$this->addToCache($object);
		return $object;
	}

	public function collect(array $ids, bool $strict = true) {
		$objects = [];
		$ids = array_unique($ids);
		$requested = count($ids);
		foreach($ids as $index => $id) {
			$cached = $this->cache->get($id);
			if(!is_null($cached)) {
				$objects[] = $cached;
				unset($ids[$index]);
			}
		}
		if(count($ids)) {
			$data = $this->dataSource->collect($ids);
			foreach($data as $row) {
				$object =  new $this->entityClass($row, $this);
				$this->addToCache($object);
				$objects[] = $object;
			}
		}
		if($strict && ($requested !== count($objects))) throw new RepositoryException('', RepositoryException::MISSING_RESULT);
		return $objects;
	}

	public function insert(Entity $object) {
		$data = $object->getRawData();
		$id = $this->dataSource->insert($data);
		return $id;
	}

	public function update(Entity $object) {
		$data = $object->getRawData();
		$this->dataSource->update($data['id'], $data);
	}

	public function getActiveUsers(){
		return $this->search("status='active")->collect();
	}


	public function delete(Entity $object){
		$this->cache->delete($object->id);
		$this->dataSource->delete($object->id);
	}

	private function getDatabaseRequest() {
		return new Finder($this->dataSource->getAccess() );
	}

	public function search(Filter $filter = null) {
		$table = $this->dataSource->getAccess()->escapeSQLEntity($this->dataSource->getTable());
		return $this->getDatabaseRequest()
				->select($table.'.*')
				->from($table)
				->setConverter(function ($record){
					$object = new $this->entityClass($record, $this);
					$this->addToCache($object);
					return $object;
				})
				->where($filter);
	}

	protected function throwExceptionOnEmpty($items){
		if(is_null($items) || (is_array($items) && !count($items))){
			throw new RepositoryException('', RepositoryException::EMPTY_RESULT);
		}
		return $items;
	}

}