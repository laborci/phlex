<?php namespace Phlex\RedFox;

use Phlex\Database\DataSource;
use Phlex\Database\Exception;
use Phlex\Database\Filter;
use Phlex\Database\Request;

abstract class Repository {

	/** @var \Phlex\Database\DataSource  */
	protected $source;
	protected $entityClass;
	/** @var \Phlex\RedFox\Cache  */
	protected $cache;

	public function __construct($source, $entityClass) {
		$this->entityClass = $entityClass;
		$this->source = $source;
		$this->cache = new Cache();
	}

	public function getCache(): Cache{
		return $this->cache;
	}

	/**
	 * Creates the default DataSource for the entity.
	 * @return \Phlex\Database\DataSource
	 */

	public function getDataSource():DataSource{ return $this->source; }

	public function pick(int $id) {
		$cached = $this->cache->get($id);
		if(!is_null($cached)) return $cached;
		$data = $this->source->pick($id);
		if(!$data) {
			throw new Exception('Entity could not be found: '.$this->entityClass.'('.$id.') in '.$this->source->getDatabase().'/'.$this->source->getTable(), 0);
		}
		$object = new $this->entityClass($data, $this);
		return $object;
	}

	public function collect(array $id_list) {
		$objects = [];
		foreach($id_list as $index => $id) {
			$cached = $this->cache->get($id);
			if(!is_null($cached)) {
				$objects[] = $cached;
				unset($id_list[$index]);
			}
		}
		if(count($id_list)) {
			$data = $this->source->collect($id_list);
			foreach($data as $row) {
				$objects[] = new $this->entityClass($row, $this);
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

	public function delete(Entity $object){
		$this->source->delete($object->id);
	}

	/**
	 * @return \Phlex\Database\Request
	 */
	protected function getDBRequest() {
		return new Request( $this->source->getAccess() );
	}

	/**
	 * @param \Phlex\Database\Filter|null $filter
	 * @return \Phlex\Database\Request
	 */
	public function getSourceRequest(Filter $filter = null) {
		$table = $this->source->getAccess()->escapeSQLEntity($this->source->getTable());
		return $this->getDBRequest()
				->select($table.'.*')
				->from($table)
				->setConverter(function ($record){ return new $this->entityClass($record, $this); })
				->where($filter);
	}

	protected function throwExceptionOnEmpty($items){
		if(is_null($items) || (is_array($items) && !count($items))){
			throw new EmptyResultException();
		}
		return $items;
	}

}