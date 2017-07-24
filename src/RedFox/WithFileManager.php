<?php namespace Phlex\RedFox;

use Phlex\RedFox\Attachment\FileManager;

trait WithFileManager {
	public function getFileManager() {
		if ($this->isExists()){
			return new FileManager($this);
		}
		else throw new \Exception("can not craete FileManager for non existing entity object");
	}

}