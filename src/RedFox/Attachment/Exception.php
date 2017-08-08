<?php namespace Phlex\RedFox\Attachment;

class Exception extends \Exception{
	const FILE_SIZE_ERROR = 1;
	const FILE_COUNT_ERROR = 2;
	const FILE_TYPE_ERROR = 3;
}