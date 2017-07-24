<?php namespace Phlex\Database;


class Filter {

	protected function __construct() { }

	protected $where = Array();

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
	static function whereIf($cond, $sql, ...$sqlParams) {
		$filter = new static();
		if (!$cond)
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
	function andIf($cond, $sql, ...$sqlParams) {
		if (!$cond)
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
	function orIf($cond, $sql, ...$sqlParams) {
		if (!$cond)
			return $this;
		return $this->addWhere('OR', $sql, $sqlParams);
	}

	/**
	 * @param \Phlex\Database\Access $db
	 *
	 * @return string
	 */
	public function getSql(Access $db) {
		if (!$this->where)
			return null;

		$sql = '';
		foreach ($this->where as $filterSegment) {

			if ($filterSegment['sql'] instanceof Filter)
				$filterSegment['sql'] = $filterSegment['sql']->getSql($db);
			else if (is_array($filterSegment['sql']))
				$filterSegment['sql'] = $this->getSqlFromArray($filterSegment['sql'], $db);
			if (trim($filterSegment['sql'])) {
				if ($sql)
					$sql .= " " . $filterSegment['type'] . " ";
				$sql .= "(" . $db->buildSQL($filterSegment['sql'], $filterSegment['args']) . ")";
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
	protected function getSqlFromArray(array $filter, Access $db) {
		if (!$filter)
			return null;
		$sql = array();
		foreach ($filter as $key => $value) {
			if (is_array($value))
				$sql[] = $db->buildSQL(" `" . $key . "` IN ($1) ", $value);
			else $sql[] = $db->buildSQL(" `" . $key . "` = $1 ", $value);
		}
		return implode(' AND ', $sql);
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
