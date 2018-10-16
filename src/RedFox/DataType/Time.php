<?php namespace Phlex\RedFox\DataType;


class Time {

	protected $date = null;

	public function __construct($time = '00:00:00') {
		$this->date = (new \DateTime("1970-01-01 ".$time));
	}

	public function getTimestamp():int {
		return $this->date->getTimestamp();
	}

	public function getTimestring():string {
		return $this->date->format('H:i:s');
	}

	public function __toString():string {
		return $this->getTimestring();
	}
}