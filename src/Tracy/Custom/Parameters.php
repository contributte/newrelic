<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy\Custom;

class Parameters
{

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public static function addParameter($name, $value)
	{
		if ((bool) ini_get('newrelic.enabled')) {
			newrelic_add_custom_parameter($name, $value);
		}
	}

}
