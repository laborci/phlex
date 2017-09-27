<?php namespace Phlex\Auth;

/**
 * Interface AuthenticableInterface.
 * What your user should implement.
 * @package Phlex\Auth
 */
interface AuthenticableInterface {
	public function getId();
	public function checkPassword($password): bool;
}