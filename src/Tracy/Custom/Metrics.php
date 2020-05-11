<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy\Custom;

use Contributte\NewRelic\Tracy\Bootstrap;

class Metrics
{

	/**
	 * @param string $name
	 * @param float $value miliseconds
	 */
	public static function addMetric($name, $value)
	{
		if (Bootstrap::isEnabled()) {
			newrelic_custom_metric('Custom/' . $name, $value);
		}
	}

}
