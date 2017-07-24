<?php

namespace Phlex\Chameleon;


use App\Env;
use Phlex\RedFox\Cache;


trait Cacheable {

	protected $cacheable = true;

	private $responseCache;

	public function __invoke() {
		/** @var \Phlex\Sys\FileCache $cache */
		$cache = Env::get('cache.response');
		$key = $this->getCacheKey();
		$this->beforeCacheHandler();

		if ($cache->exists($key) && $this->isCacheValid($key)) {
			echo $cache->get($key);
		} else {
			ob_start();
			parent::__invoke();
			$content = ob_get_flush();
			if ($this->cacheable) {
				$cache->set($key, $content);
			}
		}
	}

	protected function beforeCacheHandler() { }

	protected function getCacheKey(): string { return str_replace('\\', '_', get_class($this)); }

	protected function getCacheInvalidationTime(): int { return 180; }

	protected function isCacheValid($key): bool {
		/** @var \Phlex\Sys\FileCache $cache */
		$cache = Env::get('cache.response');
		$cacheInvalidationTime = $this->getCacheInvalidationTime();
		return $cacheInvalidationTime == 0 || time() < ($cache->getTime($key) + $cacheInvalidationTime);
	}

	protected function invalidateCache(){
		/** @var \Phlex\Sys\FileCache $cache */
		$cache = Env::get('cache.response');
		$key = $this->getCacheKey();
		$cache->delete($key);
	}
}