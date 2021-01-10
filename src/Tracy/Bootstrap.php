<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy;

use Tracy\Debugger;
use Tracy\Logger;

class Bootstrap
{

	/**
	 * @param string|NULL $appName
	 * @param string|NULL $license
	 */
	public static function init($appName = null, $license = null)
	{
		static::check();

		if (!static::isEnabled()) {
			return;
		}

		if ($appName === null) {
			$appName = 'PHP Application';
		}

		static::setup($appName, $license);

		$logger = new Logger(null);
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
