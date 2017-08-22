<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class DateTimeField extends Field {

	public function getDataType(){return '\DateTime';}

	public function import($value) { return \DateTime::createFromFormat('Y-m-d H:i:s', $value); }

	/**
	 * @param \DateTime $value
	 * @return string
	 */
	public function export($value) { return $value->format('Y-m-d H:i:s'); }

	public function set($value) {
		if(get_class($value) !== \DateTime::class) {
			throw new \Exception('DateTime Field type set error');
		}
		return $value;
	}
}