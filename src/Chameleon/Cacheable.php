<?php namespace Phlex\Chameleon;

use App\ServiceManager;


trait Cacheable {

	protected $cacheable = true;

	private $responseCache;

	private function cacheKey(){
		return str_replace('\\', '_',$this->getCacheKey());
	}

	public function __invoke() {
		/** @var \Phlex\Sys\FileCache $cache */
		$cache = ServiceManager::get('cache.response');
		$key = $this->cacheKey();
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

	protected function getCacheKey(): string { return  get_class($this); }

	protected function getCacheInvalidationTime(): int { return 180; }

	protected function isCacheValid($key): bool {
		/** @var \Phlex\Sys\FileCache $cache */
		$cache = ServiceManager::get('cache.response');
		$cacheInvalidationTime = $this->getCacheInvalidationTime();
		return $cacheInvalidationTime == 0 || time() < ($cache->getTime($key) + $cacheInvalidationTime);
	}

	protected function invalidateCache(){
		/** @var \Phlex\Sys\FileCache $cache */
		$cache = ServiceManager::get('cache.response');
		$key = $this->cacheKey();
		$cache->delete($key);
	}
}