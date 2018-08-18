<?php namespace Phlex\Form;

use App\Entity\User\User;
use App\Site\Website\Form\FormRenderer\FormRenderer;
use App\Site\Website\Form\UserForm;
use Phlex\Chameleon\HandyResponder;

/**
 * @css style
 * @jsappmodule Admin
 */
class Index extends HandyResponder {

	protected $user;

	protected function prepare() {
		$this->bodyClass = 'frame';
	}

	protected function BODY() { ?>
		<iframe name="menu" src="/menu"></iframe>
		<iframe name="list" src=""></iframe>
		<ul role="tab-bar" src="/tab-bar"></ul>
	<?php }

}
