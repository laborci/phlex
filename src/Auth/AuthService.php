<?php namespace Phlex\Auth;

use Phlex\Sys\ServiceManager\InjectDependencies;
use Phlex\Sys\ServiceManager\SharedService;

/**
 * Class AuthService.
 * Basic authentication service, good for most of the cases.
 * @package Phlex\Auth
 */
abstract class AuthService implements InjectDependencies, SharedService, AuthServiceInterface {

	protected $container;
	protected $isAuthenticated;

	public function __construct(AuthContainerInterface $container) {
		$this->container = $container;
		if (!$container->hasUserId()) {
			$this->isAuthenticated = false;
		} else {
			$user = $this->getUser();
			$this->isAuthenticated = !is_null($user) && $this->validateUser($user);
			if (!$this->validateUser($user)) {
				$container->forget();
			}
		}
	}

	public function isAuthenticated(): bool {
		return $this->isAuthenticated;
	}

	public function logout() { $this->container->forget(); }

	public function authenticate($login, $password): bool {
		try {
			$user = $this->findUser($login);
		} catch (\Exception $e) {
			return false;
		}
		if ($user->checkPassword($password)) {
			return $this->authenticateUser($user);
		} else {
			return false;
		}
	}

	public function authenticateUser(AuthenticableInterface $user): bool {
		if ($this->validateUser($user)) {
			$this->container->setUserId($user->getId());
			$this->isAuthenticated = true;
			return true;
		} else {
			return false;
		}
	}

	public function getUser() { return $this->pickUser($this->container->getUserId()); }

	abstract protected function pickUser(int $id): AuthenticableInterface;
	abstract protected function findUser(string $login): AuthenticableInterface;
	abstract protected function validateUser($user): bool;

}