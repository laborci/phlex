<?php namespace Phlex\Auth;

interface AuthServiceInterface {

	public function isAuthenticated(): bool;
	public function authenticate($login, $password): bool;
	public function getUser();
	public function logout();
	public function authenticateUserId(int $userId);

}