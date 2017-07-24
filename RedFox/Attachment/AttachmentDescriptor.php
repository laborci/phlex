<?php namespace Phlex\RedFox\Attachment;

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
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $upload
	 *
	 * @return bool
	 */
	public function isValidUpload(UploadedFile $upload){
		if($upload->getSize() > $this->maxFileSize) {
			return false;
		}
		if(!is_null($this->acceptedExtensions) && !in_array($upload->getClientOriginalExtension(), $this->acceptedExtensions)){
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