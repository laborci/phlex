<?php namespace Phlex\RedFox\Attachment;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class AttachmentDescriptor{

	protected $name;
	protected $acceptedExtensions = null;
	protected $maxFileSize = INF;
	protected $maxFileCount = INF;
	protected $entityShortName;

	function __construct($name, $entityShortName) {
		$this->name = $name;
		$this->entityShortName = $entityShortName;
	}

	public function acceptExtensions(...$extensions){
		$this->acceptedExtensions = $extensions;
		return $this;
	}

	public function maxFileSize(int $maxFileSizeInBytes){
		$this->maxFileSize = $maxFileSizeInBytes;
		return $this;
	}

	/**
	 * @return null
	 */
	public function getAcceptedExtensions() {
		return $this->acceptedExtensions;
	}

	/**
	 * @return mixed
	 */
	public function getMaxFileSize() {
		return $this->maxFileSize;
	}

	public function maxFileCount(int $maxFileCount){
		$this->maxFileCount = $maxFileCount;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\File\File $upload
	 *
	 * @return bool
	 */
	public function isValidUpload(File $upload){

		if($upload->getSize() > $this->maxFileSize) {
			return false;
		}

		if($upload instanceof UploadedFile){
			$ext = $upload->getClientOriginalExtension();
		}else{
			$ext = $upload->getExtension();
		}

		if(!is_null($this->acceptedExtensions) && !in_array($ext, $this->acceptedExtensions)){
			return false;
		}
		return true;
	}

	/**
	 * @return int
	 */
	public function getMaxFileCount() {
		return $this->maxFileCount;
	}

	/**
	 * @return mixed
	 */
	public function getEntityShortName() {
		return $this->entityShortName;
	}


}