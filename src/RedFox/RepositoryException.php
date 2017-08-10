<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 2017. 07. 26.
 * Time: 20:22
 */

namespace Phlex\RedFox;


class RepositoryException extends \Exception {
	const EMPTY_RESULT = 1;
	const MISSING_RESULT = 2;
}