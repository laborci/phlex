<?php namespace Phlex\Form;

use App\Entity\User\User;
use App\Site\Admin\Form\FormDefinition;
use Phlex\Chameleon\HandyResponder;
use Phlex\Form\FormResponder;

/**
 * @css style
 * @jsappmodule Attachment
 */
class Attachments extends HandyResponder {

	protected function prepare() {

	}

	protected function BODY() {?>
		<div class="attachments-modal">
			<div class="attachments">

			</div>
		</div>
	<?php }

}


