<?php namespace Phlex\Chameleon;


abstract class TRexPageResponder extends PageResponder {

	use TrexParser;

	final protected function respond():string { return $this->respondTemplate(); }

	abstract protected function template();


	protected function claw($var, ...$properties) {
		$value = $var;
		foreach ($properties as $property) {
			$value = $this->clawprop($property, $value);
			if ($value === null or is_scalar($value)) return $value;
		}
		return $value;
	}

	protected function clawprop($property, $var) {
		if ($property === null || is_scalar($var)) return null;
		if (is_object($var)) {
			if (is_array($property)) return $property[0]($var, $property[1], $property[2]);
			//else if (is_callable($property)) return $property($var);
			else return $var->$property;
		} else if (is_array($var)) {
			if (array_key_exists($property, $var)) return $var[$property];
			else return null;
		}
		return $var;
	}
}