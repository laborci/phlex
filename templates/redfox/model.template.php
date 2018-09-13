<?php namespace App\Entity\{{name}};


class {{name}}Model extends \Phlex\RedFox\Model{

	use Helpers\ModelTrait;


	protected function relations(){
	    //TODO: To add relations use the "belongsTo", "hasMany" and "connectedTo" methods
	}

	protected function attachments(){
	    //TODO: To add attachment group use the "hasAttachmentGroup" method
	}

	/**
	 * @param \App\Entity\{{name}}\{{name}} $object
	 */
	public function setDefaults($object) {
	    //TODO: Set default values for the given object
	}

}
