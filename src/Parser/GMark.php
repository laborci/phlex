<?php namespace Phlex\Parser;

use zpt\anno\Annotations;

class GMark {

	private $commands = [];

	private $defaultBlockMethod;

	public function __construct() {
		$reflector = new \ReflectionClass($this);
		$methods = $reflector->getMethods();
		foreach($methods as $method){
			$docBlock = (new Annotations($method))->asArray();
			if(array_key_exists('gmarkdefaultblock', $docBlock)){
				$this->defaultBlockMethod = $method->name;
			}elseif(array_key_exists('gmarkblock', $docBlock) && array_key_exists('command', $docBlock)){
				$attrType = $method->getParameters()[1]->getType()->__toString();
				if($attrType !== 'array' and $attrType !== 'string'){
					throw new \Exception('GMarkParser '.$method->name.' argument $attr type must be string or array, '.$attrType.' given.');
				}
				if(array_key_exists('requiredattributes', $docBlock)){
					$requiredAttributes = preg_split('/\s+/', trim($docBlock['requiredattributes']));
				}else{
					$requiredAttributes = [];
				}
				$commands = $docBlock['command'];
				if(is_string($commands)) $commands = [$commands];
				foreach ($commands as $command){
					$command = trim($command);
					list($command, $as) = preg_split('/\s+/', $command, 2);
					$as = $as ? $as : $command;
					$this->commands[$command] = [
						'method'=>$method->name,
						'as'=>$as,
						'requiredAttributes'=>$requiredAttributes,
						'attrType'=>$attrType
					];
				}
			}
		}
	}

	public function parse($string){
		$blocks = explode("\n\n", $string);
		$output = [];
		foreach ($blocks as $block) $output[] = $this->parseBlock(trim($block));
		return $this->joinBlocks($output);
	}

	protected function joinBlocks($blocks){
		return join("\n", $blocks);
	}


	private function parseBlock($block){

		$command = preg_split('/\s+/', $block, 2)[0];


		if(array_key_exists($command, $this->commands)){
			$command = $this->commands[$command];
			$method = $command['method'];
			list($commandLine, $body) = explode("\n", $block, 2);
			$attr = trim(preg_split('/\s+/', $commandLine, 2)[1]);
			if($command['attrType'] === 'array'){
				try{
					$attr = $this->parseAttributes($attr);
				}catch (\Throwable $exception){
					return '<error>ATTRIBUTES COULD NOT BE PARSED in line: '.$commandLine.'</error>';
				}
				foreach ($command['requiredAttributes'] as $requiredAttribute){
					if(!array_key_exists($requiredAttribute, $attr)){
						return '<error>ATTRIBUTE '.$requiredAttribute.' MISSING in line: '.$commandLine.'</error>';
					}
				}
			}
			return $this->$method( $body ? $body : '', $attr, $command['as']);
		}else if($this->defaultBlockMethod){
			$method = $this->defaultBlockMethod;
			return $this->$method($block);
		}
	}

	private function parseAttributes($attributes){
		$x = (array)new \SimpleXMLElement("<element $attributes />");
		return current($x);
	}
}