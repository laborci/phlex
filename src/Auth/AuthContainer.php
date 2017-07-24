<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 2017. 07. 11.
 * Time: 8:01
 */

namespace Phlex\Auth;


use Phlex\Session\Container;

class AuthContainer extends Container implements AuthContainerInterface {

	protected $userId;

	public function setUserId($userId) {
		$this->userId = $userId;
	}

	public function getUserId(): int {
		return $this->userId;
	}

	public function hasUserId(): bool {
		return !is_null($this->userId);
	}

}