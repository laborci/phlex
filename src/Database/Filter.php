<?php namespace Phlex\Database;


class Filter {

	protected function __construct() { }

	protected $where = Array();

	const LIKE_STARTSWITH = 1;
	const LIKE_ENDSWITH = 2;
	const LIKE_INSTRING = 3;

	static function like($string, $mode = self::LIKE_INSTRING){
		if($mode & self::LIKE_STARTSWITH) $string = '%'.$string;
		if($mode & self::LIKE_ENDSWITH) $string = $string.'%';
		return $string;
	}

	static function explode($string, $delimeter = ',', $trim = true){
		$array = explode($delimeter, $string);
		if($trim) $array = array_map('trim', $array);
		return $array;
	}

	/**
	 * @param string $sql
	 * @param mixed  $sqlParams
	 *
	 * @return Filter
	 */
	static function where($sql, ...$sqlParams) {
		$filter = new static();
		return $filter->addWhere('WHERE', $sql, $sqlParams);
	}

	/**
	 * @param bool   $cond
	 * @param string $sql
	 * @param mixed  $sqlParams
	 *
	 * @return Filter
	 */
	static function whereIf($condition, $sql, ...$sqlParams) {
		$filter = new static();
		if (!$condition)
			return $filter;
		return $filter->addWhere('WHERE', $sql, $sqlParams);
	}


	/**
	 * @param string $sql
	 * @param array  ...$sqlParams
	 *
	 * @return \Phlex\Database\Filter
	 */
	function and ($sql, ...$sqlParams) {
		return $this->addWhere('AND', $sql, $sqlParams);
	}

	/**
	 * @param string $sql
	 * @param array  ...$sqlParams
	 *
	 * @return \Phlex\Database\Filter
	 */
	function or ($sql, ...$sqlParams) {
		return $this->addWhere('OR', $sql, $sqlParams);
	}

	/**
	 * @param bool   $cond
	 * @param string $sql
	 * @param mixed  $sqlParams
	 *
	 * @return $this
	 */
	function andIf($condition, $sql, ...$sqlParams) {
		if (!$condition)
			return $this;
		return $this->addWhere('AND', $sql, $sqlParams);
	}

	/**
	 * @param bool   $cond
	 * @param string $sql
	 * @param mixed  $sqlParams
	 *
	 * @return $this
	 */
	function orIf($condition, $sql, ...$sqlParams) {
		if (!$condition)
			return $this;
		return $this->addWhere('OR', $sql, $sqlParams);
	}

	/**
	 * @param \Phlex\Database\Access $db
	 *
	 * @return string
	 */
	public function getSql(Access $access) {
		if (!$this->where)
			return null;

		$sql = '';
		foreach ($this->where as $filterSegment) {

			if ($filterSegment['sql'] instanceof Filter)
				$filterSegment['sql'] = $filterSegment['sql']->getSql($access);
			else if (is_array($filterSegment['sql']))
				$filterSegment['sql'] = $this->getSqlFromArray($filterSegment['sql'], $access);
			if (trim($filterSegment['sql'])) {
				if ($sql)
					$sql .= " " . $filterSegment['type'] . " ";
				$sql .= "(" . $access->buildSQL($filterSegment['sql'], $filterSegment['args']) . ")";
			}

		}
		return $sql;
	}


#region Helper methods

	/**
	 * @param array                  $filter
	 * @param \Phlex\Database\Access $db
	 *
	 * @return null
	 */
	protected function getSqlFromArray(array $filter, Access $access) {
		if (!$filter)
			return null;
		$sql = array();
		foreach ($filter as $key => $value) {
			if (is_array($value))
				$sql[] = $access->buildSQL(" `" . $key . "` IN ($1) ", $value);
			else $sql[] = $access->buildSQL(" `" . $key . "` = $1 ", $value);
		}
		$completeSql = implode(' AND ', $sql);
		return $completeSql;
	}

	/**
	 * @param string $type
	 * @param string  $sql
	 * @param array  $sqlParams
	 *
	 * @return $this
	 */
	protected function addWhere($type, $sql, $sqlParams) {
		if (!$this->where)
			$type = 'WHERE';
		else if ($type == 'WHERE')
			$type = 'AND';
		$this->where[] = array('type' => $type, 'sql' => $sql, 'args' => $sqlParams);
		return $this;
	}
	#endregion

} // End of class
