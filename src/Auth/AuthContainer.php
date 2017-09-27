<?php namespace Phlex\Auth;

use Phlex\Session\Container;

/**
 * Basic AuthContainer.
 * Good for most of the cases
 * @package Phlex\Auth
 */
class AuthContainer extends Container implements AuthContainerInterface {

	protected $userId;

	public function setUserId(int $userId) {
		$this->userId = $userId;
	}

	public function getUserId(): int {
		return $this->userId;
	}

	public function hasUserId(): bool {
		return !is_null($this->userId);
	}

}