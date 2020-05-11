<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy\Custom;

use Contributte\NewRelic\Tracy\Bootstrap;

class Parameters
{

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public static function addParameter($name, $value)
	{
		if (Bootstrap::isEnabled()) {
			newrelic_add_custom_parameter($name, $value);
		}
	}

}
