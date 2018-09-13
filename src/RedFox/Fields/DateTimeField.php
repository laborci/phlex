<?php namespace Phlex\RedFox\Fields;


use App\ServiceManager;
use Phlex\RedFox\Field;

class DateTimeField extends Field {

	public function getDataType(){return '\DateTime';}

	public function import($value) {
		return (!$value) ? null : \DateTime::createFromFormat('Y-m-d H:i:s', $value);
	}

	/**
	 * @param \DateTime $value
	 * @return string
	 */
	public function export($value) { return is_null($value) ? null : $value->format('Y-m-d H:i:s'); }

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