<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy\Custom;

use Contributte\NewRelic\Tracy\Bootstrap;

class Tracers
{

	/**
	 * @param string $function functionName / ClassName::functionName
	 */
	public static function addTracer($function)
	{
		if (Bootstrap::isEnabled()) {
			newrelic_add_custom_tracer($function);
		}
	}

}
