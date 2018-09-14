<?php namespace Phlex\Database;

use App\ServiceManager;

/**
 * Class Request
 */
class Finder {

	/** @var \Phlex\Database\Access */
	protected $access;
	/** @var string */
	protected $select = '*';
	/** @var string */
	protected $key = null;
	/** @var  string */
	protected $from = null;
	/** @var  Filter */
	protected $where = null;
	/** @var array */
	protected $order = Array();
	/** @var \Closure|null */
	protected $converter = null;

	public function __construct(Access $access, \Closure $converter = null) {
		$this->access = $access;
		$this->converter = $converter;
	}

	public function setConverter(\Closure $converter = null):Finder{
		$this->converter = $converter;
		return $this;
	}

	public function key(string $key):Finder  {
		$this->key = $key;
		return $this;
	}

	public function select(string $sql, ...$sqlParams):Finder  {
		$this->select = $this->access->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	public function from(string $sql, ...$sqlParams):Finder  {
		$this->from = $this->access->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	public function where(Filter $filter = null):Finder  {
		if(!is_null($filter)) {
			$this->where = $filter;
		}
		return $this;
	}

	#region order

	function order($order):Finder {
		if(is_array($order)) foreach($order as $field => $dir) $this->order[] = $this->access->escapeSQLEntity($field).' '.$dir;
		else $this->order[] = $order;
		return $this;
	}

	function asc($field):Finder { return $this->order($this->access->escapeSQLEntity($field) . ' ASC'); }
	function desc($field):Finder { return $this->order($this->access->escapeSQLEntity($field) . ' DESC'); }
	function ascIf(bool $cond, string $field):Finder { return $cond ? $this->asc($field) : $this; }
	function descIf(bool $cond, string $field):Finder { return $cond ? $this->desc($field) : $this; }
	function orderIf(bool $cond, $order):Finder { return $cond ? $this->order($order) : $this; }

	#endregion

	/**
	 * @param null $limit
	 * @param null $offset
	 *
	 * @return array
	 */
	public function collect($limit = null, $offset = null, &$count = null) {
		$data = $this->collectData($limit, $offset,$count);
		if($converter = $this->converter) {
			$data = array_map($converter, $data);
		}
		return $data;
	}

	public function pick() {
		$data = $this->collectData(1);
		if($data) {
			$data = array_shift($data);
			if($converter = $this->converter){
				$data = $converter($data);
			}
			return $data;
		} else return null;
	}

	public function collectData($limit = null, $offset = null, &$count = null):array {
		$doCounting = !is_null($limit);

		$sql = $this->getSql($doCounting);
		if (!is_null($limit)) {
			$sql .= ' LIMIT ' . $limit;
			if (!is_null($offset))
				$sql .= ' OFFSET ' . $offset;
		}
		if (!is_null($this->key)) {
			return $this->access->getRowsWithKey($sql);
		} else {
			return $this->access->getRows($sql);
		}
		if($doCounting) $count = $this->access->getFoundRows();
	}

	public function collectPage($pageSize, $page, &$count = 0):array {
		$data = $this->collectPageData($pageSize, $page, $count);
		if($converter = $this->converter) $data = array_map($converter, $data);
		return $data;
	}

	public function collectPageData($pageSize, $page, &$count = 0):array {
		$pageSize = abs(intval($pageSize));
		$page = abs(intval($page));
		//$count = $this->count();
		//if(!$count) return array();
		$data = $this->collectData($pageSize, $pageSize * ($page - 1));
		$count = $this->access->getFoundRows();
//		$pages = ceil($count / $pageSize);
//		if($page > $pages) $page = $pages;
		return $data;
	}

	public function getSql($count = false):string {
		return
			'SELECT ' . ($count ? 'SQL_CALC_FOUND_ROWS ' : '') .
			((!is_null($this->key)) ? ($this->access->escapeSQLEntity($this->key).', ') : ('')) .
			$this->select . ' ' .
			'FROM ' . $this->from . ' ' .
			(($this->where != null) ? (' WHERE ' . $this->where->getSql($this->access) . ' ') : ('')) .
			((count($this->order)) ? (' ORDER BY ' . join(', ', $this->order)) : (''));
	}

	public function getCountSql(){
		return 'SELECT Count(1) FROM ' . $this->from . ' ' .( $this->where != null ? ' WHERE '.$this->where->getSql($this->access).' ' : '');
	}

	public function count():int {
		return $this->access->getValue($this->getCountSql());
		//$sql = preg_replace('/^\s*SELECT(.+?)FROM/', 'SELECT COUNT(1) FROM', $sql);
		//$sql = explode('ORDER BY', $sql);
		//return $this->access->getValue($sql[0]);
	}
}
