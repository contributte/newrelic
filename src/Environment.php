<?php declare(strict_types = 1);

namespace Contributte\NewRelic;

class Environment
{

	/**
	 * @return string[] $argv
	 */
	public function getArgv(): array
	{
		return $_SERVER['argv'];
	}

	public function isCli(): bool
	{
		return PHP_SAPI === 'cli';
	}

}
