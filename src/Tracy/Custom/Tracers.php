<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy\Custom;

class Tracers
{

	/**
	 * @param string $function functionName / ClassName::functionName
	 */
	public static function addTracer($function)
	{
		if ((bool) ini_get('newrelic.enabled')) {
			newrelic_add_custom_tracer($function);
		}
	}

}
