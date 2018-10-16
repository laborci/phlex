<?php namespace Phlex\RedFox\Fields;

use Phlex\RedFox\DataType\Time;
use Phlex\RedFox\Field;

class TimeField extends Field {

	public function getDataType() { return '\\'.Time::class; }
	public function import($value) { return (!$value) ? null : new Time($value); }

	/**
	 * @param Time $value
	 * @return string
	 */
	public function export($value) { return is_null($value) ? null : $value->getTimestring(); }

	public function set($value) {
		if (is_string($value)) {
			$value = $this->import($value);
		}
		if (get_class($value) !== Time::class && !is_null($value)) {
			throw new \Exception('Time Field type set error');
		}
		return $value;
	}

}