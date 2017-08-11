<?php namespace Phlex\Database;

/**
 * Class Request
 */
class Request {

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

	public function setConverter(\Closure $converter = null):Request{
		$this->converter = $converter;
		return $this;
	}

	public function key(string $key):Request  {
		$this->key = $key;
		return $this;
	}

	public function select(string $sql, ...$sqlParams):Request  {
		$this->select = $this->access->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	public function from(string $sql, ...$sqlParams):Request  {
		$this->from = $this->access->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	public function where(Filter $filter = null):Request  {
		if(!is_null($filter)) {
			$this->where = $filter;
		}
		return $this;
	}

	#region order

	function order($order):Request {
		if(is_array($order)) foreach($order as $field => $dir) $this->order[] = $this->access->escapeSQLEntity($field).' '.$dir;
		else $this->order[] = $order;
		return $this;
	}

	function asc($field):Request { return $this->order($this->access->escapeSQLEntity($field) . ' ASC'); }
	function desc($field):Request { return $this->order($this->access->escapeSQLEntity($field) . ' DESC'); }
	function ascIf(bool $cond, string $field):Request { return $cond ? $this->asc($field) : $this; }
	function descIf(bool $cond, string $field):Request { return $cond ? $this->desc($field) : $this; }
	function orderIf(bool $cond, $order):Request { return $cond ? $this->order($order) : $this; }

	#endregion

	public function collect($limit = null, $offset = null):array {
		$data = $this->collectData($limit, $offset);
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

	public function collectData($limit = null, $offset = null):array {
		$sql = $this->getSql();
		if (!is_null($limit)) $sql .= ' LIMIT '.$limit;
		if (!is_null($offset)) $sql .= ' OFFSET '.$offset;
		if (!is_null($this->key)) {
			return $this->access->getRowsWithKey($sql);
		} else {
			return $this->access->getRows($sql);
		}
	}

	public function collectPage($pageSize, $page, &$count):array {
		$data = $this->collectPageData($pageSize, $page, $count);
		if($converter = $this->converter) $data = array_map($converter, $data);
		return $data;
	}

	public function collectPageData($pageSize, $page, &$count):array {
		$pageSize = abs(intval($pageSize));
		$page = abs(intval($page));
		$count = $this->count();
		if(!$count) return array();
		$pages = ceil($count / $pageSize);

		if($page > $pages) $page = $pages;

		return $this->collectData($pageSize, $pageSize * ($page - 1));
	}

	public function getSql():string {
		return
			'SELECT ' .
			((!is_null($this->key)) ? ($this->access->escapeSQLEntity($this->key).', ') : ('')) .
			$this->select . ' ' .
			'FROM ' . $this->from . ' ' .
			(($this->where != null) ? (' WHERE ' . $this->where->getSql($this->access) . ' ') : ('')) .
			((count($this->order)) ? (' ORDER BY ' . join(', ', $this->order)) : (''));
	}

	public function count():int {
		$sql = $this->getSql();
		$sql = preg_replace('/^\s*SELECT(.+?)FROM/', 'SELECT COUNT(1) FROM', $sql);
		$sql = explode('ORDER BY', $sql);
		return $this->access->getValue($sql[0]);
	}
}
