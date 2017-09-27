<?php namespace Phlex\Auth;

/**
 * Interface AuthContainerInterface
 * @package Phlex\Auth
 */
interface AuthContainerInterface {

	public function setUserId(int $userId);
	public function getUserId(): int;
	public function hasUserId(): bool;
	public function forget();

}