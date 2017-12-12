<?php namespace Phlex\RedFox\Attachment;

use App\Env;
use Phlex\RedFox\Entity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Class AttachmentManager
 * @package Phlex\RedFox\Attachment
 * @property-read \Phlex\RedFox\Attachment\Attachment[] $files
 * @property-read \Phlex\RedFox\Attachment\Attachment $first
 */

class AttachmentManager{

	/** @var  \Phlex\RedFox\Attachment\Attachment[] */
	protected $attachments = null;

	protected $path;
	protected $pathId;
	protected $urlBase;
	protected $owner;
	protected $descriptor;

	public function getPath(): string { return $this->path; }
	public function getPathId(): string { return $this->pathId; }
	public function getUrlBase(): string { return $this->urlBase; }
	public function getOwner(): string { return $this->owner; }

	/**
	 * @return \Phlex\RedFox\Attachment\Attachment[]
	 */
	public function getAttachments() {
		if(is_null($this->attachments)) $this->collect();
		return $this->attachments;
	}
	public function getAttachmentCount() { return count($this->getAttachments()); }
	public function hasAttachments() { return (bool)count($this->getAttachments()); }


	public function __construct(Entity $owner, AttachmentDescriptor $descriptor) {
		$this->owner = $owner;
		$this->descriptor = $descriptor;
		$this->path = Env::get('path_files') . $descriptor->getEntityShortName() . '/' . $owner->id. '/'.$descriptor->getName().'/';
		$this->pathId = $descriptor->getEntityShortName().'-'.$owner->id.'-'.$descriptor->getName();
		$this->urlBase = Env::get('url_files').$descriptor->getEntityShortName() . '/' . $owner->id. '/'.$descriptor->getName().'/';
		if(!is_dir($this->path)){ mkdir($this->path, 0777, true); }
	}

	public function uploadFile(UploadedFile $file, $replace = false) {
		if($this->getAttachmentCount() >= $this->descriptor->getMaxFileCount() && !$replace) {
			throw new Exception("Too many files", Exception::FILE_COUNT_ERROR);
		}elseif( $file->getClientSize() > $this->descriptor->getMaxFileSize() ) {
			throw new Exception("Too big file", Exception::FILE_SIZE_ERROR);
		}elseif( ! in_array($file->getClientOriginalExtension(), $this->descriptor->getAcceptedExtensions() )) {
			throw new Exception("File type is not accepted", Exception::FILE_TYPE_ERROR);
		}else{
			if($this->descriptor->getMaxFileCount() == 1 && $this->getAttachmentCount()==1 && $replace){
				$this->first->delete();
			}
			$file->move($this->path, $file->getClientOriginalName());
			$this->attachments = null;
			return true;
		}
	}

	public function addFile(File $file, $replace = false){
		if($this->getAttachmentCount() >= $this->descriptor->getMaxFileCount() && !$replace) {
			throw new Exception("Too many files", Exception::FILE_COUNT_ERROR);
		}elseif( $file->getSize() > $this->descriptor->getMaxFileSize() ) {
			throw new Exception("Too big file", Exception::FILE_SIZE_ERROR);
		}elseif( !in_array($file->getExtension(), $this->descriptor->getAcceptedExtensions()) ) {
			throw new Exception("File type is not accepted", Exception::FILE_TYPE_ERROR);
		}else{
			if($this->descriptor->getMaxFileCount() == 1 && $this->getAttachmentCount()==1 && $replace){
				$this->first->delete();
			}
			copy($file->getPath().'/'.$file->getFilename(), $this->path.$file->getFilename());
			$this->attachments = null;
			return true;
		}
	}

	//public function copyAttachment(Attachment $attachment) {
	//	//TODO: should check like upload
	//	copy($attachment->getFile(), $this->path.$attachment->getFilename());
	//	$this->attachments = null;
	//}


	public function deleteFile($filename){
		$attachments = $this->getAttachments();
		if(array_key_exists($filename, $attachments)){
			$attachments[$filename]->delete();
			unset($attachments[$filename]);
		}
	}

	protected function collect(){
		$files = glob($this->getPath().'/*');
		$attachments = [];
		foreach ($files as $file){
			$attachment =  new Attachment($file, $this);
			$attachments[$attachment->getFilename()] = $attachment;
		}
		$this->attachments = $attachments;
		return $attachments;
	}

	public function __get($name) {
		$attachments = $this->getAttachments();
		switch ($name){
			case 'files': return $attachments; break;
			case 'first':
				if($this->hasAttachments()) return reset($attachments);
				else return null;
				break;
		}
	}


}

