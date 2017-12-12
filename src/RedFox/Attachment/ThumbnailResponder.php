<?php

namespace Phlex\RedFox\Attachment;


use App\Env;
use Phlex\Chameleon\PageResponder;


class ThumbnailResponder extends PageResponder {

	protected $target;
	protected $source;
	protected $img;
	protected $originalHeight;
	protected $originalWidth;
	protected $ext;

	protected function prepare(): bool {
		$uri = explode('/', $this->getRequest()->getRequestUri());
		$uri = urldecode(array_pop($uri));
		$parts = explode('.', $uri);
		$ext = array_pop($parts);
		$hash = array_pop($parts);
		$pathId = array_pop($parts);
		if($ext == 'jpg'){ $jpegquality = array_pop($parts); }else{$jpegquality = null;}
		$op = array_pop($parts);
		$file = join('.', $parts);
		$path = Env::get('path_files').preg_replace("/-/",'/',$pathId,2).'/'.$file;

		$url = $file . '.' . $op . (($jpegquality)?('.'.$jpegquality):('')).'.' . $pathId.'.' . $ext;
		$newHash = base_convert(crc32($url . Env::get('thumbnailCrc32Salt')), 10, 32);

		if(!is_dir(Env::get('path_thumbnails'))) mkdir(Env::get('path_thumbnails'));
		$this->target = Env::get('path_thumbnails').$uri;
		$this->source = $path;
		$this->ext = $ext;

		if($newHash != $hash || !file_exists($path)){
			// TODO: 404
			die('404');
		}

		$imgInfo = getimagesize($this->source);
		$oType = $imgInfo['2'];
		switch ($oType) {
			case 1: $this->img = imageCreateFromGif($this->source); break;
			case 2: $this->img = imageCreateFromJpeg($this->source); break;
			case 3: $this->img = imageCreateFromPng($this->source); break;
			default: die('404');
		}
		$this->originalWidth = $imgInfo[0];
		$this->originalHeight = $imgInfo[1];

		switch (substr($op,0,1)){
			case 'h': $this->height(substr($op, 1)); break;
			case 'w': $this->width(substr($op, 1)); break;
			case 'c': $this->crop(substr($op, 1)); break;
			case 's': $this->scale(substr($op, 1)); break;
			case 'b': $this->box(substr($op, 1)); break;
			default: die('404');
		}

		switch ($ext) {
			case 'gif':ImageGIF($this->img, $this->target); break;
			case 'jpg':
				$jpegquality = base_convert($jpegquality, 32, 10) * 4;
				ImageJPEG($this->img, $this->target, $jpegquality);
				break;
			case 'png':
				imagesavealpha($this->img, true);
				ImagePng($this->img, $this->target);
				break;
		}

		return true;
	}

	protected function height($op){
		$cropmode = $this->getCropMode($op);
		$arglen = strlen($op)/2;
		$height = base_convert(substr($op, 0, $arglen), 32, 10);
		$maxWidth = base_convert(substr($op, $arglen), 32, 10);

		$oAspect = $this->originalWidth / $this->originalHeight;
		$width = $height * $oAspect;
		$this->doResize($width, $height);
		if($maxWidth != 0 and $width>$maxWidth) $this->doCrop($maxWidth, $height, $cropmode);
	}

	protected function width($op){
		$cropmode = $this->getCropMode($op);
		$arglen = strlen($op)/2;
		$width = base_convert(substr($op, 0, $arglen), 32, 10);
		$maxHeight = base_convert(substr($op, $arglen), 32, 10);

		$oAspect = $this->originalWidth / $this->originalHeight;
		$height = $width / $oAspect;
		$this->doResize($width, $height);
		if($maxHeight != 0 and $height>$maxHeight) $this->doCrop($width, $maxHeight, $cropmode);
	}

	protected function crop($op){
		$cropmode = $this->getCropMode($op);
		$arglen = strlen($op)/2;
		$width = base_convert(substr($op, 0, $arglen), 32, 10);
		$height = base_convert(substr($op, $arglen), 32, 10);

		$oAspect = $this->originalWidth / $this->originalHeight;
		$aspect = $width/$height;
		$resizeWidth = $width;
		$resizeHeight = $height;
		if($aspect > $oAspect) $resizeHeight = $width / $oAspect;
		elseif($aspect < $oAspect) $resizeWidth = $height * $oAspect;
		$this->doResize($resizeWidth, $resizeHeight);
		$this->doCrop($width, $height, $cropmode);
	}

	protected function box($op){
		$arglen = strlen($op)/2;
		$width = base_convert(substr($op, 0, $arglen), 32, 10);
		$height = base_convert(substr($op, $arglen), 32, 10);
		$aspect = $width/$height;
		$oAspect = $this->originalWidth / $this->originalHeight;
		if($aspect < $oAspect) $height = $width / $oAspect;
		elseif($aspect > $oAspect) $width = $height * $oAspect;
		$this->doResize($width, $height);
	}

	protected function scale($op){
		$arglen = strlen($op)/2;
		$width = base_convert(substr($op, 0, $arglen), 32, 10);
		$height = base_convert(substr($op, $arglen), 32, 10);
		$this->doResize($width, $height);
	}

	protected function doResize($width, $height){
		$newImg = imagecreatetruecolor($width, $height);
		$oWidth = imagesx($this->img);
		$oHeight = imagesy($this->img);
		imagefill($newImg, 0, 0, imagecolorallocatealpha( $newImg, 0, 0, 0, 127 ));
		imagecopyresampled($newImg, $this->img, 0, 0, 0, 0, $width, $height, $oWidth, $oHeight);
		imagedestroy($this->img);
		$this->img = $newImg;
	}

	protected function doCrop($width, $height, $mode){
		$newImg = imageCreateTrueColor($width, $height);
		imagefill($newImg, 0, 0, imagecolorallocatealpha( $newImg, 0, 0, 0, 127 ));
		$sx = $sy = 0;

		$oWidth = imagesx($this->img);
		$oHeight = imagesy($this->img);

		if($mode == -1){
			// do nothing
		}elseif($mode == 1){
			if($oWidth == $width) $sy = $oHeight - $height;
			else $sx = $oWidth - $width;
		}else{
			if($oWidth == $width) $sy = $oHeight/2 - $height/2;
			else $sx = $oWidth/2 - $width/2;
		}

		imagecopyresampled($newImg, $this->img, 0, 0, $sx, $sy, $width, $height, $width, $height);
		imagedestroy($this->img);
		$this->img = $newImg;
	}

	protected function getCropMode(&$op){
		if(substr($op, 0, 1) == '-'){
			$op = substr($op, 1);
			return 1;
		}
		if(substr($op, 0, 1) == '_'){
			$op = substr($op, 1);
			return -1;
		}
		return 0;
	}

	protected function respond():string {
		header('HTTP/1.0 200 OK');
		header('Content-type: image/'.strtolower($this->ext));
		$fd = fopen($this->target,'rb');
		fpassthru($fd);
		fclose($fd);
		return '';
	}

}