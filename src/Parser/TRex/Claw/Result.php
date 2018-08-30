<?php namespace Phlex\Parser\TRex\Claw;

class Result {
	public $modifier = null;
	public $scope = '';
	public $chain = [];

	function setModifier($mod) { $this->modifier = $mod; }
	function setScope($scope) {
		if (get_class($scope) === Property::class) {
			$this->scope = '$this';
			$this->add($scope);
		} else {
			$this->scope = $scope->name;
		}
	}

	function add($value) {
		$this->chain[] = $value;
	}

	function __toString() {
		if(!count($this->chain)){ return $this->scope; }

		$output = '';
		if ($this->modifier) $output .= $this->modifier;
		$output .= '$this->claw(' . $this->scope;
		foreach ($this->chain as $item) {
			$class = get_class($item);
			if ($class === Property::class) {
				$output .= ', "' . $item->name . '"';
			} else if ($class === Result::class) {
				$output .= ', ' . $item;
			} else if ($class === Method::class) {
				$arguments = [];
				foreach ($item->args as $arg){
					if(get_class($arg) === Value::class){
						$arguments[] = $arg->name;
					}else{
						$arguments[] = (string) $arg;
					}
				}
				$args = join(', ', $arguments);
				$output .= ', ' . '[function($o, $m, $a){return $o->$m(...$a);}, "' . $item->name . '", ['.$args.']]';
			}
		}
		$output .= ')';
		return $output;
	}
}