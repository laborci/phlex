<?php namespace Phlex\RedFox\Fields;

use Phlex\RedFox\Field;

class DateField extends Field {

	public function getDataType() { return '\DateTime'; }
	public function import($value) { return (!$value) ? null : \DateTime::createFromFormat('Y-m-d', $value); }

	/**
	 * @param \DateTime $value
	 * @return string
	 */
	public function export($value) { return is_null($value) ? null : $value->format('Y-m-d'); }

	public function set($value) {
		if (is_string($value)) {
			$value = $this->import($value);
		}
		if (get_class($value) !== 'DateTime' && !is_null($value)) {
			throw new \Exception('Date Field type set error');
		}
		return $value;
	}

}