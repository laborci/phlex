<?php namespace Phlex\Chameleon;


abstract class TRexPageResponder extends PageResponder {

	use TrexParser;

	final protected function respond() { $this->respondTemplate(); }

	abstract protected function template();

}