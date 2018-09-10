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
		$lines = explode("\n", "\n".$string."\n");
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

}


