<?php namespace Phlex\Parser;

class TRex{

	protected $uses = [];
	protected $ctns = null;

	public static function parseString($string){
		$parser = new static();
		$output = $parser->parse($string);
		$lines = explode("\n", $output);
		$output = '';
		foreach ($lines as $line) if(trim($line)) {
			$output .= trim($line) . "\n";
		}
		return $output;
	}

	protected function parse($string){
		$lines = explode("\n", $string);
		$output = '';

		foreach($lines as $line){
			$line = trim($line);
			if(strlen($line)){
				if(substr($line, 0 ,1) == '@') {
					$line = $this->parseLineCTNS($line);
					$line = $this->parseLineUSE($line);
					$line = $this->parseLinePHP($line);
					$line = $this->parseLineVAR($line);
					$line = $this->parseLineEXP($line);
					$line = $this->parseLineEVAL($line);
					$line = $this->parseLineEACH($line);
					$line = $this->parseLineEND($line);
					$line = $this->parseLineAS($line);
					$line = $this->parseLineIFISSET($line);
					$line = $this->parseLineIF($line);
					$line = $this->parseLineELSEIFISSET($line);
					$line = $this->parseLineELSEIF($line);
					$line = $this->parseLineELSE($line);
					$line = $this->parseLineCOMPARE($line);
					$line = $this->parseLineWITH($line);
					$line = $this->parseLineDEFAULT($line);
				}else{
					$line = $this->parseINLINE_IF($line);
					$line = $this->parseINLINE_ELSE($line);
					$line = $this->parseINLINE_ELSEIF($line);
					$line = $this->parseINLINE_CLOSE($line);
					$line = $this->parseECHO($line);
					$line = $this->parseCustomTag($line);
					$line = $this->parseCustomTagCloser($line);
				}
				$output .= $line."\n";
			}

		}

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

	#region ONELINERS
	protected function parseLineCTNS($line){
		if(substr($line, 0, 5) == '@ctns'){
			$this->ctns = str_replace('.','\\', trim(substr($line, 6), ". \t\n\r\0\x0B"));
			$line = '';
		}
		return $line;
	}

	protected function parseLineUSE($line){
		if(substr($line, 0, 4) == '@use'){
			$this->uses[] = trim(str_replace('.','\\',substr($line, 4)));
			$line = '';
		}
		return $line;
	}

	protected function parseLinePHP($line){
		if(substr($line, 0, 4) == '@php'){
			$line = '<?php '.trim(substr($line, 4)).'?>';
		}
		return $line;
	}

	protected function parseLineVAR($line){
		if(substr($line, 0, 4) == '@var'){
			$line = '<?php $'.trim(substr($line, 4)).'?>';
		}
		return $line;
	}

	protected $expressions=[];

	protected function parseLineEXP($line){
		if(substr($line, 0, 4) == '@exp'){
			list($name, $exp) = explode('=', substr($line, 4), 2);
			$name = trim($name);
			$exp = trim($exp);
			$this->expressions[trim($name)] = trim($exp);
			$line = '';
		}
		return $line;
	}

	protected function parseLineEVAL($line){
		if(substr($line, 0, 5) == '@eval'){
			list($var, $exp) = explode('=', substr($line, 5), 2);
			$var = trim($var);
			$exp = trim($exp);
			$line = '<?php $'.$var.' = '.$this->expressions[$exp].'; ?>';
		}
		return $line;
	}
	#endregion

	#region EACH

	protected $eachExpressionCount = 0;

	protected function parseLineEACH($line){
		if(substr($line, 0, 5) == '@each'){
			$parts = explode(' ', $line);
			if($parts[count($parts)-2] == 'as') {
				$line = $this->parseLineEACHAS($line);
			}else{
				$value = trim(substr($line, 5));
				$this->eachExpressionCount++;
				$line = '<?php $__iterateOn_' . $this->eachExpressionCount . ' = ' . $this->parseVALUE($value) . ';if(is_array($__iterateOn_' . $this->eachExpressionCount . ') and count($__iterateOn_' . $this->eachExpressionCount . ')){ ?>';
			}
		}
		return $line;
	}

	protected function parseLineEACHAS($line){
		$line = trim(substr($line, 5));
		$parts = explode(' ', $line);
		$var = array_pop($parts);
		$as = array_pop($parts);
		$value = join(' ', $parts);
		$this->eachExpressionCount++;
		$line = '<?php $__iterateOn_' . $this->eachExpressionCount . ' = ' . $this->parseVALUE($value) .
			';$'.$var.'_index = -1; $'.$var.'_number = 0;if(is_array($__iterateOn_' . $this->eachExpressionCount . ') and count($__iterateOn_' . $this->eachExpressionCount . '))
			foreach($__iterateOn_'.$this->eachExpressionCount.' as $'.$var.'_key => $'.$var.'){ $'.$var.'_index++; $'.$var.'_number++;
			?>';
		return $line;
	}

	protected function parseLineAS($line){
		if(substr($line, 0, 3) == '@as'){
			$var =  trim(substr($line, 3));
			$line = '<?php $'.$var.'_index = -1; $'.$var.'_number = 0; foreach($__iterateOn_'.$this->eachExpressionCount.' as $'.$var.'_key => $'.$var.'){ $'.$var.'_index++; $'.$var.'_number++; ?>';
		}
		return $line;
	}
	#endregion

	#region IF

	protected function parseLineIFISSET($line){
		if(substr($line, 0, 5) == '@if ?') {
			$value = trim(substr($line, 5));
			$value = $this->parseVALUE($value);
			$line = '<?php if(isset('.$value.') && ('.$value.') ){ ?>';
		}
		return $line;
	}
	protected function parseLineIF($line){
		if(substr($line, 0, 3) == '@if') {
			$value = trim(substr($line, 3));
			$value = $this->parseVALUE($value);
			$line = '<?php if('.$value.'){ ?>';
		}
		return $line;
	}

	protected function parseLineELSEIFISSET($line){
		if(substr($line, 0, 9) == '@elseif ?') {
			$value = trim(substr($line, 9));
			$value = $this->parseVALUE($value);
			$line = '<?php }elseif(isset('.$value.') && ('.$value.')){ ?>';
		}
		return $line;
	}

	protected function parseLineELSEIF($line){
		if(substr($line, 0, 7) == '@elseif') {
			$value = trim(substr($line, 7));
			$value = $this->parseVALUE($value);
			$line = '<?php }elseif('.$value.'){ ?>';
		}
		return $line;
	}

	protected function parseLineELSE($line){
		if(substr($line, 0, 5) == '@else') {
			$line = '<?php }else{ ?>';
		}
		return $line;
	}
	#endregion

	#region COMPARE
	protected function parseLineCOMPARE($line){
		if(preg_match('/^@compare\s+(.*?)\s+with\s+(.*?)$/', $line, $matches)){
			$line = '<?php switch('.$this->parseVALUE($matches[1]).'){ case '.$this->parseVALUE($matches[2]).': ?>';
		}
		return $line;
	}

	protected function parseLineWITH($line){
		if(preg_match('/^@with\s+(.*?)$/', $line, $matches)){
			$line = '<?php break; case '.$this->parseVALUE($matches[1]).': ?>';
		}
		return $line;
	}

	protected function parseLineDEFAULT($line){
		if($line == '@default'){
			$line = '<?php break; default: ?>';
		}
		return $line;
	}

	#endregion

	#region INLINEIF

	protected function parseINLINE_IF($line){
		if($num = preg_match_all('/{\?(.*?)}/', $line, $matches)){
			for($i = 0; $i<$num; $i++){
				$line = str_replace($matches[0][$i], '<?php if('.$this->parseVALUE($matches[1][$i]).'){?>' ,$line);
			}
		}
		return $line;
	}
	protected function parseINLINE_ELSE($line){
		if($num = preg_match_all('/{:}/', $line, $matches)){
			for($i = 0; $i<$num; $i++){
				$line = str_replace($matches[0][$i], '<?php }else{?>' ,$line);
			}
		}
		return $line;
	}
	protected function parseINLINE_ELSEIF($line){
		if($num = preg_match_all('/{:(.+?)}/', $line, $matches)){
			for($i = 0; $i<$num; $i++){
				$line = str_replace($matches[0][$i], '<?php }elseif('.$this->parseVALUE($matches[1][$i]).'){?>' ,$line);
			}
		}
		return $line;
	}
	protected function parseINLINE_CLOSE($line){
		if($num = preg_match_all('/{\.}/', $line, $matches)){
			for($i = 0; $i<$num; $i++){
				$line = str_replace($matches[0][$i], '<?php }?>' ,$line);
			}
		}
		return $line;
	}

	#endregion

	protected function parseLineEND($line){
		if($line == '@' || substr($line, 0, 4) == '@end'){
			$line = '<?php } ?>';
		}
		return $line;
	}

	protected function parseECHO($line){
		if($num = preg_match_all('/{{(.*?)}}/', $line, $matches)){
			for($i = 0; $i<$num; $i++){
				$line = str_replace($matches[0][$i], '<?php echo '.$this->parseVALUE($matches[1][$i]).'; ?>' ,$line);
			}
		}
		return $line;
	}

	protected function parseVALUE($value){
		$value = trim($value);
		if(substr($value, 0, 1) === '!'){
			$prefix = '!';
			$value = substr($value, 1);
		}else{
			$prefix = '';
		}
		
		$firstChar = substr($value, 0, 1);
		$firstTwoChar = substr($value, 0, 2);
		if($firstChar == "'" || $firstChar == '"' || is_numeric($value)){
			$realValue = $value;
		}elseif($firstChar == '.'){
			$realValue = '$this->'.$this->parseVarIndex(substr($value, 1));
		}elseif($firstChar == '@'){
			$realValue = $this->expressions[substr($value, 1)];
		}elseif($firstTwoChar == '(?'){
			$realValue = substr($value,2,-2);
		}else{
			$realValue = '$'.$this->parseVarIndex($value);
		}
		return $prefix . $realValue;
	}

	protected function parseVarIndex($var){
		$var = str_replace('.','->',$var);
		$var = preg_replace('/:([a-zA-Z0-9_]*)/', '[\'$1\']', $var, -1, $count);
		return $var;
	}

	#region CUSTOMTAG
	protected function parseCustomTag($line){
		if($count = preg_match_all('/<ct:([\w\d_]+)((\s+[\w\d-:]+="((\\"|.)*?)")*)\s*\/?>/', $line, $matches)){
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


