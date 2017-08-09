<?php namespace Phlex\RedFox;

abstract class Seeker {
	static function find($shortName, $id){
		$class = static::getClass($shortName);
		return $class::repository()->pick($id);
	}

	static function getClass($shortName){
		return '\\App\Entity\\'.$shortName.'\\'.$shortName;
	}
}