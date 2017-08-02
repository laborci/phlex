<?php namespace Phlex\RedFox;

use CaseHelper\CaseHelperFactory;
use Phlex\Database\DataSource;
use Phlex\Database\Filter;
use Phlex\Database\Request;

abstract class Repository {

	/** @var \Phlex\Database\DataSource  */
	protected $source;
	protected $entityClass;

	public function __construct($source, $entityClass) {
		$this->entityClass = $entityClass;
		$this->source = $source;
	}

	/**
	 * Creates the default DataSource for the entity.
	 * @return \Phlex\Database\DataSource
	 */

	public function getDataSource():DataSource{ return $this->source; }

	/**
	 * @return Cache
	 */
	protected function getCache() {
		$class = $this->entityClass;
		return $class::cache();
	}

	public function pick(int $id) {
		$cached = $this->getCache()->get($id);
		if(!is_null($cached)) return $cached;
		$data = $this->source->pick($id);
		$object = new $this->entityClass($data);
		return $object;
	}

	public function collect(array $id_list) {
		$objects = [];
		foreach($id_list as $index => $id) {
			$cached = $this->getCache()->get($id);
			if(!is_null($cached)) {
				$objects[] = $cached;
				unset($id_list[$index]);
			}
		}
		if(count($id_list)) {
			$data = $this->source->collect($id_list);
			foreach($data as $row) {
				$objects[] = new $this->entityClass($row);
			}
		}
		return $objects;
	}

	public function insert(Entity $object) {
		$data = $object->getRawData();
		$id = $this->source->insert($data);
		return $id;
	}

	public function update(Entity $object) {
		$data = $object->getRawData();
		$this->source->update($data['id'], $data);
	}

	/**
	 * @return \Phlex\Database\Request
	 */
	protected function getDBRequest() {
		$repository = $this;
		return new Request(
			$this->source->getAccess(),
			function ($record, $multi = false) {
				if($multi) {
					$objects = array();
					$records = $record;
					foreach($records as $record) {
						$object = new $this->entityClass($record);
						$objects[] = $object;
					}
					return $objects;
				} else {
					$object = new $this->entityClass($record);
					return $object;
				}
			});
	}

	/**
	 * @param \Phlex\Database\Filter|null $filter
	 * @return \Phlex\Database\Request
	 */
	public function getSourceRequest(Filter $filter = null) {
		$request = $this->getDBRequest()->from($this->source->getAccess()->escapeSQLEntity($this->source->getTable()));
		if(!is_null($filter))
			$request->where($filter);
		return $request;
	}

	protected function throwExceptionOnEmpty($items){
		if(is_null($items) || (is_array($items) && !count($items))){
			throw new EmptyResultException();
		}
		return $items;
	}

}