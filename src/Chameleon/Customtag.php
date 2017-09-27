<?php namespace Phlex\Chameleon;

use App\ServiceManager;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class Customtag implements SmartPageComponentInterface {

	use TrexParser;

	/** @var  ParameterBag */
	private $attributes;
	/** @var  \Phlex\Chameleon\SmartPageComponentInterface */
	private $parent;

	public function __invoke() {
		$this->prepare();
		$this->respond();
	}

	abstract protected function prepare();

	public function addJsInclude($src) {
		if (!is_null($this->parent))
			$this->parent->addJsInclude($src);
	}

	public function addCssInclude($src) {
		if (!is_null($this->parent))
			$this->parent->addCssInclude($src);
	}

	protected function getSurroundAttribute($attr, $seek = false) {

		if (count(DoubleCustomtag::$tags))
			for ($i = count(DoubleCustomtag::$tags) - 1; $i >= 0; $i--) {
				$parentTag = DoubleCustomtag::$tags[ $i ];
				/** @var ParameterBag $attrs */
				$attrs = $parentTag->getAttributeParamBag();
				if ($attrs->has($attr))
					return $attrs->get($attr);
				if (!$seek)
					break;
			}

		return null;
	}

	public function setup(ParameterBag $attributes, $parent) {
		$this->parent = $parent;
		$this->attributes = $attributes;
	}

	final public static function show($data, $parent = null) {
		/** @var static $tag */
		$tag = ServiceManager::get(get_called_class());
		$tag->setup(new ParameterBag($data), $parent);
		$tag();
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\ParameterBag
	 */
	final protected function getAttributeParamBag(): ParameterBag { return $this->attributes; }

	protected function respond() { echo $this->respondTemplate('tag'); }

	abstract protected function tag();
}