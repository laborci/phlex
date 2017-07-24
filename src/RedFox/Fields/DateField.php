<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class DateField extends Field {

	public function getDataType(){return '\DateTime';}

	public function import($value) { return \DateTime::createFromFormat('Y-m-d', $value); }

	/**
	 * @param \DateTime $value
	 * @return string
	 */
	public function export($value) { return $value->format('Y-m-d'); }

	public function set($value) {
		if(get_class($value) !== '\DateTime') {
			throw new \Exception('Date Field type set error');
		}
		return $value;
	}

}