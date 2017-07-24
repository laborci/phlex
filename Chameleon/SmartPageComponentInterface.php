<?php

namespace Phlex\Chameleon;


interface SmartPageComponentInterface {
	public function addJsInclude($src);
	public function addCssInclude($src);
}