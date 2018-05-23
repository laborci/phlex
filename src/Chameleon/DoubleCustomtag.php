<?php namespace Phlex\Chameleon;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class DoubleCustomtag extends Customtag{

	/** @var static[]  */
	static $tags = [];

	public function setup(ParameterBag $attributes, $parent) {
		parent::setup($attributes, $parent);
		static::$tags[] = $this;
	}

	final public static function close(){ array_pop(static::$tags)->respondCloser(); }
	protected function respondCloser(){ echo $this->respondTemplate('closer'); }
	abstract protected function closer();
}