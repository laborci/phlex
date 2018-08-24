<?php namespace Phlex\RedFox;

use App\ServiceManager;
use Phlex\Database\DataSource;
use Phlex\Database\Filter;
use Phlex\Database\Finder;

abstract class Repository {

	/** @var \Phlex\Database\DataSource */
	protected $dataSource;
	protected $entityClass;
	/** @var \Phlex\RedFox\Cache */
	protected $cache;

	public function __construct(DataSource $dataSource, string $entityClass) {
		$this->entityClass = $entityClass;
		$this->dataSource = $dataSource;
		$this->cache = new Cache();
	}

	private function addToCache(Entity $object) {
		$this->cache->add($object);
	}

	/**
	 * Creates the default DataSource for the entity.
	 * @return \Phlex\Database\DataSource
	 */

	public function getDataSource(): DataSource { return $this->dataSource; }

	public function pick(int $id, bool $strict = true) {
		$cached = $this->cache->get($id);
		if (!is_null($cached)) return $cached;
		$data = $this->dataSource->pick($id);
		if ($data) {
			$object = new $this->entityClass($data, $this);
			$this->addToCache($object);
			return $object;

		}
		if ($strict) $this->throwExceptionOnEmpty($data);
		return null;
	}

	public function collect(array $ids, $map = false) {
		$objects = [];
		$ids = array_unique($ids);
		$requested = count($ids);
		if ($requested == 0) return [];

		foreach ($ids as $index => $id) {
			$cached = $this->cache->get($id);
			if (!is_null($cached)) {
				$objects[] = $cached;
				unset($ids[$index]);
			}
		}
		if (count($ids)) {
			$data = $this->dataSource->collect($ids);
			foreach ($data as $row) {
				$object = new $this->entityClass($row, $this);
				$this->addToCache($object);
				$objects[] = $object;
			}
		}
		if ($map) {
			return array_map(function (Entity $item) { return [$item->id => $item->__toString()]; }, $objects);
		} else {
			return $objects;
		}
	}

	protected function count(Filter $filter = null) {
		$count = array_pop($this->getDatabaseFinder()->select("count(id)")->from($this->dataSource->getTable())->where($filter)->pick());
		return $count;
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

	public function delete(Entity $object) {
		$this->cache->delete($object->id);
		$this->dataSource->delete($object->id);
	}

	private function getDatabaseFinder(): Finder {
		return new Finder($this->dataSource->getAccess());
	}

	public function search(Filter $filter = null, $map = false) {
		$table = $this->dataSource->getAccess()->escapeSQLEntity($this->dataSource->getTable());
		$finder = $this->getDatabaseFinder()
			->select($table . '.*')
			->from($table)
			->where($filter);
		if ($map === false) return $finder->setConverter(function ($record) {
			$object = new $this->entityClass($record, $this);
			$this->addToCache($object);
			return $object;
		});
		else return $finder
			->setConverter(function ($record) {
				$object = new $this->entityClass($record, $this);
				$this->addToCache($object);
				return ['key'=>$object->id, 'value'=>$object->__toString()];
			});
	}

	public function searchMap(Filter $filter = null){
		return $this->search($filter, true);
	}

	protected function throwExceptionOnEmpty($items) {
		if (is_null($items) || (is_array($items) && !count($items))) {
			throw new RepositoryException('', RepositoryException::EMPTY_RESULT);
		}
		return $items;
	}

}