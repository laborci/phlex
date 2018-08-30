<?php namespace Phlex\Parser\TRex;


use App\ServiceManager;
use zpt\anno\Annotations;

class Dragon {

	protected $commands = [];
	protected $trex;
	protected $commandPrefix;

	public function __construct(TRex $trex, $commandPrefix = '@') {
		$this->trex = $trex;
		$this->commandPrefix = $commandPrefix;
		$this->readAnnotations();
	}

	public function parse($line) {
		foreach($this->commands as $command){
			if ($num = preg_match_all($command['pattern'], $line, $matches)) {
				$method = $command['method'];
				for ($i = 0; $i < $num; $i++) {
					preg_match($command['pattern'], $matches[0][$i], $localmatch);
					$match = array_shift($localmatch);
					$line = str_replace($match, $this->$method(...$localmatch), $line);
				}
			}
		}
		return $line;
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

	#region - - - - INLINE - - - -
	/**
	 * @pattern /{\?(.*?)}/
	 */
	protected function ___if($var){
		return '<?php if('.$this->claw($var).'){?>';
	}

	/**
	 * @pattern /{:}/
	 */
	protected function ___else(){
		return '<?php }else{?>';
	}

	/**
	 * @pattern /{:(.+?)}/
	 */
	protected function ___elseif($var){
		return '<?php }else if('.$this->claw($var).'){?>';
	}

	/**
	 * @pattern /{\.}/
	 */
	protected function ___end(){
		return '<?php }?>';
	}

	/**
	 * @pattern /{{(.*?)}}/
	 */
	protected function ___echo($var){
		return '<?php echo '.$this->claw($var).'; ?>';
	}

	#endregion

	#region - - - - CUSTOMTAG - - - -

	/**
	 * @pattern /<ct:([\w\d\\\\_]+)((\s+[\w\d-:]+="((\\"|.)*?)")*)\s*\/?>/
	 */
	protected function ___customtag($customtagClass, $customtagAttributes){
		$attributes = $this->parseCustomTagAttributes($customtagAttributes);
		return '<?php '.$customtagClass.'::show('.$attributes.', $this); ?>';
	}


	/**
	 * @pattern /<\/ct:(.*?)>/
	 */
	protected function ___customtag_close($customtagClass){
		return '<?php '.$customtagClass.'::close(); ?>';
	}

	protected function parseCustomTagAttributes($attributes){
		$x = (array)new \SimpleXMLElement("<element $attributes />");
		$attributes = current($x);
		$output = '[';
		foreach ($attributes as $attribute=>$value){
			list($attribute, $type) = array_pad(explode('.', $attribute), 2, null);
			switch ($type){
				case 'str': $val = '"'.$value.'"'; break;
				case 'num': $val = $value; break;
				case 'php': $val = $value; break;
				default: $val = $this->claw($value); break;
			}
			$output.= "'".trim($attribute)."'=>".$val.', ';
		}
		$output .= ']';
		return $output;
	}
	#endregion

}