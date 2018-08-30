<?php namespace Phlex\Parser\TRex;

use App\ServiceManager;
use Phlex\Parser\TRex\Fang;
use zpt\anno\Annotations;

class TRex {

	/** @var Fang */
	protected $fang;
	/** @var Dragon  */
	protected $dragon;

	public function __construct() {
		$this->fang = new Fang($this);
		$this->dragon = new Dragon($this);
	}

	public static function parse($string){
		$parser = new static();
		return $parser->parseString($string);
	}

	protected function parseString($string){
		$lines = explode("\n", $string);
		$output = [];
		foreach ($lines as $line) {
			$line = trim($line);
			if($this->fang->parse($line)){
				$output[] = $line;
			}else{
				$output[] = $this->dragon->parse($line);
			}
		}
		return $this->header().join("\n", $output);
	}

	#region - - - - PHP HEADER OPTIONS - - - -

	protected $uses = [];
	protected $ctns = null;
	public function setCtns($ctns){$this->ctns = $ctns;}
	public function addUse($use){$this->uses[] = $use;}
	protected function header(){
		$output = '';
		if(!is_null($this->ctns)){
			$output = '<?php namespace '.$this->ctns.';?>'.$output;
		}

		if(count($this->uses)){
			$uses = '<?php'."\n";
			foreach ($this->uses as $use){
				$uses .= 'use '.$use.';'."\n";
			}
			$uses.= '?>'."\n";
			$output = $uses.$output;
		}
		return $output;
	}

	#endregion




	#region CUSTOMTAG
	protected function parseCustomTag($line){
		if($count = preg_match_all('/<ct:([\w\d\\\\_]+)((\s+[\w\d-:]+="((\\"|.)*?)")*)\s*\/?>/', $line, $matches)){
			for($i = 0; $i < $count; $i++){
				$customtag = trim($matches[0][$i]);
				$customtagClass = trim($matches[1][$i]);
				$customtagAttributes = trim($matches[2][$i]);
				$attributes = $this->parseCustomTagAttributes($customtagAttributes);
				$line = str_replace($customtag, '<?php '.$customtagClass.'::show('.$attributes.', $this); ?>' ,$line);
			}
		}
		return $line;
	}

	protected function parseCustomTagCloser($line){
		if($count = preg_match_all('/<\/ct:(.*?)>/', $line, $matches)){
			for($i = 0; $i < $count; $i++){
				$customtag = trim($matches[0][$i]);
				$customtagClass = trim($matches[1][$i]);
				$line = str_replace($customtag, '<?php '.$customtagClass.'::close(); ?>' ,$line);
			}
		}
		return $line;
	}

	protected function parseCustomTagAttributes($attrString){
		if($count = preg_match_all('/(.*?)=\"((?:[^\"\\\\]|\\\\.)*)\"/', $attrString, $matches));
		$attr = '[';
		for($i = 0; $i < $count; $i++) {
			if(strpos($matches[1][$i],':') === false) $matches[1][$i].= ':php';
			list($name, $type) = explode(':', $matches[1][$i],2);
			if($type == 'str') $val = '"'.$matches[2][$i].'"';
			elseif($type == 'num') $val = $matches[2][$i];
			else $val = $this->parseVALUE($matches[2][$i]);
			$attr .= "'".trim($name)."'=>".$val.', ';
		}
		$attr .= ']';
		return $attr;
	}
	#endregion

}


