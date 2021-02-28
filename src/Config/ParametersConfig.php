<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Config;

use Nette\PhpGenerator\Method;

final class ParametersConfig
{

	/**
	 * @var bool
	 */
	public $capture = false;

	/**
	 * @var string[]
	 */
	public $ignored = [];

	public function addInitCode(Method $method): void
	{
		$method->addBody("ini_set('newrelic.capture_params', ?);", [
			$this->capture,
		]);
		$method->addBody("ini_set('newrelic.ignored_params', ?);", [
			implode(',', $this->ignored),
		]);
	}

}
