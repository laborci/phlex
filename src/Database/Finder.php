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

	/**
	 * @param \Closure|null $converter
	 * @return static
	 */
	public function setConverter(\Closure $converter = null):self{
		$this->converter = $converter;
		return $this;
	}

	/**
	 * @param string $key
	 * @return static
	 */
	public function key(string $key):self  {
		$this->key = $key;
		return $this;
	}

	/**
	 * @param string $sql
	 * @param mixed ...$sqlParams
	 * @return static
	 */
	public function select(string $sql, ...$sqlParams):self  {
		$this->select = $this->access->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	/**
	 * @param string $sql
	 * @param mixed ...$sqlParams
	 * @return static
	 */
	public function from(string $sql, ...$sqlParams):self  {
		$this->from = $this->access->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	/**
	 * @param Filter|null $filter
	 * @return static
	 */
	public function where(Filter $filter = null):self  {
		if(!is_null($filter)) {
			$this->where = $filter;
		}
		return $this;
	}

	#region order

	/**
	 * @param $order
	 * @return static
	 */
	function order($order):self {
		if(is_array($order)) foreach($order as $field => $dir) $this->order[] = $this->access->escapeSQLEntity($field).' '.$dir;
		else $this->order[] = $order;
		return $this;
	}

	/**
	 * @param $field
	 * @return static
	 */
	function asc($field):self { return $this->order($this->access->escapeSQLEntity($field) . ' ASC'); }

	/**
	 * @param $field
	 * @return static
	 */
	function desc($field):self { return $this->order($this->access->escapeSQLEntity($field) . ' DESC'); }
	/**
	 * @param bool $cond
	 * @param string $field
	 * @return static
	 */
	function ascIf(bool $cond, string $field):self { return $cond ? $this->asc($field) : $this; }
	/**
	 * @param bool $cond
	 * @param string $field
	 * @return static
	 */
	function descIf(bool $cond, string $field):self { return $cond ? $this->desc($field) : $this; }
	/**
	 * @param bool $cond
	 * @param $order
	 * @return static
	 */
	function orderIf(bool $cond, $order):self { return $cond ? $this->order($order) : $this; }

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
		$false = false;
		$data = $this->collectData(1, null, $false);
		if($data) {
			$data = array_shift($data);
			if($converter = $this->converter){
				$data = $converter($data);
			}
			return $data;
		} else return null;
	}

	public function collectData($limit = null, $offset = null, &$count = null):array {
		$doCounting = !is_null($limit) && $count!==false;

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
