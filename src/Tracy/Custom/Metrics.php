<?php

namespace VrtakCZ\NewRelic\Tracy\Custom;

class Metrics
{

	/**
	 * @param string $name
	 * @param float $value miliseconds
	 */
	public static function addMetric($name, $value)
	{
		if (\VrtakCZ\NewRelic\Tracy\Bootstrap::isEnabled()) {
			newrelic_custom_metric('Custom/' . $name, $value);
		}
	}

}
