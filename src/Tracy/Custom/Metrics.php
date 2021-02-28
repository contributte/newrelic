<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy\Custom;

class Metrics
{

	/**
	 * @param string $name
	 * @param float $value miliseconds
	 */
	public static function addMetric($name, $value)
	{
		if ((bool) ini_get('newrelic.enabled')) {
			newrelic_custom_metric('Custom/' . $name, $value);
		}
	}

}
