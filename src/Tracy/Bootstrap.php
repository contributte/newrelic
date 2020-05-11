<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy;

use Tracy\Debugger;
use Tracy\Logger;

class Bootstrap
{

	/**
	 * @param string[]|array|NULL $logLevel (null for default - error and critical)
	 * @param string|NULL $appName
	 * @param string|NULL $license
	 */
	public static function init(?array $logLevel = null, $appName = null, $license = null)
	{
		static::check();

		if (!static::isEnabled()) {
			return;
		}

		if ($appName === null) {
			$appName = 'PHP Application';
		}

		static::setup($appName, $license);

		if ($logLevel === null) {
			$logLevel = [
				Logger::CRITICAL,
				Logger::EXCEPTION,
				Logger::ERROR,
			];
		}

		$logger = new Logger($logLevel);
		Debugger::setLogger($logger);
	}

	/**
	 * @return bool
	 */
	public static function isEnabled()
	{
		return (bool) ini_get('newrelic.enabled');
	}

	/**
	 * @param string $appName
	 * @param string|null $license
	 */
	public static function setup($appName, $license = null)
	{
		if ($license === null) {
			newrelic_set_appname($appName);
		} else {
			newrelic_set_appname($appName, $license);
		}
	}

	private static function check()
	{
		if (!extension_loaded('newrelic')) {
			throw new \RuntimeException('Missing NewRelic extension.');
		}
	}

}
