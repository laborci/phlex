<?php namespace Phlex\Codex\Responder;

use Phlex\Chameleon\HandyResponder;

class Index extends HandyResponder {

	protected function BODY() { ?>
		<px-admin menu="/menu"></px-admin>
	<?php }

}