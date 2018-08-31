<?php namespace Phlex\Parser\TRex\Claw;

const T_CHAR = 0;

class Parser {

	/** @var Result */
	protected $result;
	/** @var TokenContainer */
	protected $tc;

	public function __construct($tc) {
		$this->tc = $tc;
		$this->result = new Result();
	}

	static function parse($string) {
		$phptokens = token_get_all('<?php ' . trim($string) . ' ?>');
		$tc = new TokenContainer();
		foreach ($phptokens as $token) {
			if (!is_array($token)) {
				$tc->items[] = new Token(T_CHAR, $token);
			} else if ($token[0] != T_OPEN_TAG) {
				if($token[0] === T_DNUMBER && $token[1][0] === '.'){
					$tc->items[] = new Token(T_CHAR, '.');
					$tc->items[] = new Token(T_STRING, substr($token[1], 1));
				}else {
					$tc->items[] = new Token($token[0], $token[1]);
				}
			}
		}
		return static::parseTokens($tc);
	}

	protected static function parseTokens($tc) {
		$parser = new static($tc);
		return $parser();
	}

	public function __invoke() {
		if ($this->tc->items[0]->value === '!') {
			array_shift($this->tc->items);
			$this->result->setModifier('!');
		}
		$this->result->setScope($this->seekScope());
		while ($this->tc->items) {
			$token = $this->tc->items[0];
			if ($token->value == '.') {
				array_shift($this->tc->items);
				$this->result->add($this->seekScope());
			} else if ($token->value == '}' ) {
				array_shift($this->tc->items);
				return $this->result;
			} else if ($token->value == ',' || $token->type == T_WHITESPACE || $token->value == ')') {
				return $this->result;
			}else	array_shift($this->tc->items);
		}
		return $this->result;
	}

	protected function seekScope() {
		$token = array_shift($this->tc->items);
		if ($token->value == '{') {
			$scope = static::parseTokens($this->tc);
		} else if ($this->tc->items[0]->value == '(') {
			$args = $this->seekArguments();
			$scope = new Method($token->value, $args);
		} else if ($token->type == T_VARIABLE) {
			$scope = new Variable($token->value);
		} else if ($token->type == T_STRING) {
			$scope = new Property($token->value);
		} else {
			$scope = new Value($token->value);
		}
		return $scope;
	}

	protected function seekArguments() {
		array_shift($this->tc->items);
		$arguments = [];
		while (count($this->tc->items)) {
			$token = $this->tc->items[0];
			if ($token->value == ')') {
				array_shift($this->tc->items);
				return $arguments;
			} else if ($token->type == T_STRING || $token->type == T_VARIABLE || $token->value == '{') {
				$arguments[] = static::parseTokens($this->tc);
			} else if ($token->value != ',') {
				$arguments[] = new Value($token->value);
				array_shift($this->tc->items);
			} else {
				array_shift($this->tc->items);
			}
		}
	}

}