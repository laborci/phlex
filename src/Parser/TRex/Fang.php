<?php namespace Phlex\Parser\TRex;

use App\ServiceManager;
use zpt\anno\Annotations;

class Fang {

	protected $commands = [];
	protected $trex;

	public function __construct(TRex $trex) {
		$this->trex = $trex;
		$this->readAnnotations();

	}

	public function parse(&$line) {
		$l = $line;

		list($commandString, $rest) = array_pad(preg_split('/\s+/', $line, 2), 2, null);
		$commandString = str_replace('-', '', $commandString);
		if($rest === null) $l = $commandString;
		else $l = $commandString .' '.$rest;

		foreach($this->commands as $command){
			if (preg_match($command['pattern'], $l)) {
				$method = $command['method'];
				$line = $this->$method(trim($rest));
				return true;
			}
		}
		return false;
	}

	protected function readAnnotations() {
		$reflector = new \ReflectionClass($this);
		$methods = $reflector->getMethods();
		foreach ($methods as $method) {
			$docBlock = (new Annotations($method))->asArray();
			if (substr($method->name, 0, 3) == '___' && array_key_exists('pattern', $docBlock)) {
				$pattern = $docBlock['pattern'];
				$command = substr($method->name, 3);
				$this->commands[$command] = [
					'pattern'=>$pattern,
					'method'=>$method->name
				];
			}
		}
	}

	protected function claw($string) {
		if (substr($string, 0, 1) == '(' && substr($string, -1) == ')') return substr($string, 1, -1);
		else return \Phlex\Parser\TRex\Claw\Parser::parse($string);
	}

	protected function explode($string, $limit = null, $padValue = null, $delimeter = '/\s+/') {
		return array_pad(preg_split($delimeter, $string, $limit), $limit, $padValue);
	}

	/**
	 * @pattern /^\/\/|@rem/
	 */
	protected function ___rem($line) {
		return '';
	}



	/**
	 * @pattern /^@ctns|@namespace/
	 */
	protected function ___ctns($line) {
		$ctns = str_replace('.', '\\', trim($line, ". \t\n\r\0\x0B"));
		$this->trex->setCtns($ctns);
		return '';
	}
	/**
	 * @pattern /^@use/
	 */
	protected function ___use($line) {
		$use = trim(str_replace('.', '\\', $line));
		$this->trex->addUse($use);
		return '';
	}
	/**
	 * @pattern /^@php/
	 */
	protected function ___php($line) {
		return '<?php ' . $line . ';?>';
	}
	/**
	 * @pattern /^@var/
	 */
	protected function ___var($line) {
		list($var, $value) = $this->explode($line, 2);
		return '<?php $' . $var . ' = ' . $this->claw($value) . '; ?>';
	}

	/**
	 * @pattern /^@end/
	 */
	protected function ___end($line) {
		return '<?php } ?>';
	}

	#region - - - - EACH AS - - - -

	protected $eachCount = 1;
	protected function ___each($line) {
		list($subject, $as, $value, $key, $loop) = $this->explode($line, 5);
		$iteration = '$__i_' . $eachCount++;
		$line = '<?php ' . $iteration . ' = ' . $this->claw($subject) . ';';
		if (is_null($as)) {
			$line .= 'if(is_array(' . $iteration . ') and count(' . $iteration . ')){ ?>';
		} else {
			if (!is_null($loop)) {
				$line .= '$' . $loop . ' = ["count"=>count(' . $iteration . '), "index"=>-1, "number"=>0];';
			}
			$line .= 'if(is_array(' . $iteration . ') and count(' . $iteration . '))';
			if (!is_null($key)) {
				$line .= 'foreach(' . $iteration . ' as $' . $key . '_key => $' . $value . '){';
			} else {
				$line .= 'foreach(' . $iteration . ' as $' . $value . '){';
			}
			if (!is_null($loop)) {
				$line .= ' $' . $loop . '["index"]++; $' . $loop . '["number"]++;';
			}
		}
		$line .= ' ?>';
		return $line;
	}

	protected function ___as($line) {
		list($value, $key, $loop) = $this->explode($line, 3);
		$iteration = '$__i_' . $eachCount++;

		if (!is_null($loop)) {
			$line .= '$' . $loop . ' = ["count"=>count(' . $iteration . '), "index"=>-1, "number"=>0];';
		}
		if (!is_null($key)) {
			$line .= 'foreach(' . $iteration . ' as $' . $key . '_key => $' . $value . '){';
		} else {
			$line .= 'foreach(' . $iteration . ' as $' . $value . '){';
		}
		if (!is_null($loop)) {
			$line .= ' $' . $loop . '["index"]++; $' . $loop . '["number"]++;';
		}

		$line .= ' ?>';
		return $line;
	}

	#endregion

	#region - - -  IF - - - -
	/**
	 * @pattern /^@if\s+/
	 */
	protected function ___if($line) {
		$var = $this->claw($line);
		$line = '<?php if(' . $var . '){ ?>';
		return $line;
	}

	/**
	 * @pattern /^@else$/
	 */
	protected function ___else($line) {
		$var = $this->claw($line);
		$line = '<?php }elseif(' . $var . '){ ?>';
		return $line;
	}

	/**
	 * @pattern /^@notempty|@ifnotempty/
	 */
	protected function ___if_not_empty($line) {
		$var = $this->claw($line);
		$line = '<?php if(!empty(' . $var . ')){ ?>';
		return $line;
	}

	/**
	 * @pattern /^@elsenotempty|@elseifnotempty/
	 */
	protected function ___else_if_not_empty($line) {
		$var = $this->claw($line);
		$line = '<?php }elseif(!empty(' . $var . ')){ ?>';
		return $line;
	}

	/**
	 * @pattern /^@notnull|@ifnotnull/
	 */
	protected function ___if_not_null($line) {
		$var = $this->claw($line);
		$line = '<?php if(!is_null(' . $var . ')){ ?>';
		return $line;
	}
	/**
	 * @pattern /^@elsenotnull|@elseifnotnull/
	 */
	protected function ___else_if_not_null($line) {
		$var = $this->claw($line);
		$line = '<?php }elseif(!is_null(' . $var . ')){ ?>';
		return $line;
	}

	#endregion

	#region - - - - COMPARE - - - -
	/**
	 * @pattern /^@compare/
	 */
	protected function ___compare($line) {
		list($subject, $with, $value) = $this->explode($line);
		$line = '<?php switch(' . $this->claw($subject) . '){ case ' . $this->claw($value) . ': ?>';
		return $line;
	}
	/**
	 * @pattern /^@with/
	 */
	protected function ___with($line) {
		$line = '<?php break; case ' . $this->claw($line) . ': ?>';
		return $line;
	}
	/**
	 * @pattern /^@default/
	 */
	protected function ___default($line) {
		$line = '<?php break; default: ?>';
		return $line;
	}

	#endregion
}


