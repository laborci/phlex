<?php namespace Phlex\Database;

/**
 * Class Request
 */
class Request {

	/** @var \Phlex\Database\Access */
	protected $dbAccess;
	/** @var string */
	protected $select = '*';
	/** @var string */
	protected $keyField = null;
	/** @var  string */
	protected $from = null;
	/** @var  Filter */
	protected $where = null;
	/** @var array */
	protected $order = Array();
	/** @var \Closure|null */
	protected $converter = null;

	public function __construct(Access $db, \Closure $converter = null) {
		$this->dbAccess = $db;
		$this->converter = $converter;
	}

#region initializers

	/**
	 * @param string $sql list of fields to retrieve
	 * @param string $sqlParams
	 * @return $this
	 */
	public function select(string $sql, ...$sqlParams) {
		$this->select = $this->dbAccess->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	/**
	 * @param      $sql
	 * @param null $sqlParams
	 * @return $this
	 */
	public function key(string $sql, ...$sqlParams) {
		$this->keyField = $this->dbAccess->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	/**
	 * @param string $sql mostly the table name
	 * @param string $sqlParams
	 * @return $this
	 */
	public function from(string $sql, ...$sqlParams) {
		$this->from = $this->dbAccess->buildSQL($sql . ' ', $sqlParams);
		return $this;
	}

	/**
	 * @param \Phlex\Database\Filter $filter
	 * @return $this
	 */
	public function where(Filter $filter) {
		$this->where = $filter;
		return $this;
	}
	#endregion

#region order
	/**
	 * @param string $field
	 * @return $this
	 */
	function asc($field) { return $this->Order($field . ' ASC'); }

	/**
	 * @param string $field
	 * @return $this
	 */
	function desc($field) { return $this->Order($field . ' DESC'); }

	/**
	 * @param string|array $param if array should look like this {'myField'=>'Asc', ...}
	 * @return $this
	 */
	function order($param) {
		if(is_array($param)) foreach($param as $field => $dir) $this->order[] = $field . ' ' . $dir;
		else $this->order[] = $param;
		return $this;
	}

	/**
	 * @param $cond
	 * @param $field
	 * @param mixed [$sqlParams]
	 * @return $this
	 */
	function ascIf(bool $cond, string $field, ...$sqlParams) {
		if($cond) {
			$this->Order($this->dbAccess->buildSQL($field . ' ASC', $sqlParams));
		}
		return $this;
	}

	/**
	 * @param $cond
	 * @param $field
	 * @param mixed [$sqlParams]
	 * @return $this
	 */
	function descIf(bool $cond, string $field, ...$sqlParams) {
		if($cond) {
			$this->order($this->dbAccess->buildSQL($field . ' DESC', $sqlParams));
		}
		return $this;
	}

	/**
	 * @param              $cond
	 * @param string|array $param if array should look like this {'myField'=>'Asc', ...}
	 * @param              mixed  [$sqlParams]
	 * @return $this
	 */
	function orderIf(bool $cond, $param, ...$sqlParams ) {
		if($cond) {
			if(is_array($param)) foreach($param as $field => $dir) $this->order[] = $this->dbAccess->buildSQL($field, $sqlParams) . ' ' . $dir;
			else $this->order[] = $this->dbAccess->buildSQL($param, $sqlParams);
		}
		return $this;
	}

	#endregion

	/**
	 * Returns all, or limited elements (convertable)
	 *
	 * @param null $limit
	 * @param int  $offset
	 * @return mixed
	 */
	public function collect($limit = null, $offset = 0) {
		$data = $this->collectData($limit, $offset);
		if($converter = $this->converter) {
			$data = $converter($data, true);
		}
		return $data;
	}

	/**
	 * Returns one element
	 *
	 * @return null
	 */
	public function pick() {
		$data = $this->collectData(1, 0);
		if($data) {
			$data = array_shift($data);
			if($converter = $this->converter) $data = $converter($data, false);
			return $data;
		} else return null;
	}

	/**
	 * Returns all, or limited elements (without convertion)
	 *
	 * @param null $limit
	 * @param int  $offset
	 * @return mixed
	 */
	public function collectData($limit = null, $offset = 0) {
		$sql = $this->getSql();
		if(!is_null($limit)) $sql .= ' LIMIT ' . $offset . ', ' . $limit;
		return $this->dbAccess->getRows($sql);
	}

	/**
	 * @param $pageSize
	 * @param $page
	 * @param $count
	 * @return mixed
	 */
	public function collectPage($pageSize, $page, &$count) {
		$data = $this->collectPageData($pageSize, $page, $count);
		if($converter = $this->converter) $data = $converter($data, true);
		return $data;
	}

	/**
	 * @param $pageSize
	 * @param $page
	 * @param $count
	 * @return mixed
	 */
	public function collectPageData($pageSize, $page, &$count) {
		$pageSize = abs(intval($pageSize));
		$page = abs(intval($page));
		$count = $this->count();
		if(!$count) return array();
		$pages = ceil($count / $pageSize);

		if($page > $pages) $page = $pages;

		return $this->collectData($pageSize, $pageSize * ($page - 1));
	}

	/**
	 * @return string
	 */
	public function getSql() {
		return
			'SELECT ' .
			$this->select . ' ' .
			(($this->keyField) ? (', ' . $this->keyField . ' AS __KEY__ ') : ('')) .
			'FROM ' . $this->from . ' ' .
			(($this->where != null) ? (' WHERE ' . $this->where->getSql($this->dbAccess) . ' ') : ('')) .
			((count($this->order)) ? (' ORDER BY ' . join(', ', $this->order)) : (''));
	}


	/**
	 * @return mixed
	 */
	public function count() {
		$sql = $this->getSql();
		$sql = preg_replace('/^\s*SELECT(.+?)FROM/', 'SELECT COUNT(1) FROM', $sql);
		$sql = explode('ORDER BY', $sql);
		return $this->dbAccess->getValue($sql[0]);
	}
} // End of class
