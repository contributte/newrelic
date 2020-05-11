<?php

namespace VrtakCZ\NewRelic\Tracy;

class Bootstrap
{

	/**
	 * @param string[]|array|NULL $logLevel (null for default - error and critical)
	 * @param string|NULL $appName
	 * @param string|NULL $license
	 */
	public static function init(array $logLevel = NULL, $appName = NULL, $license = NULL)
	{
		static::check();

		if (!static::isEnabled()) {
			return;
		}

		if ($appName === NULL) {
			$appName = 'PHP Application';
		}

		static::setup($appName, $license);

		if ($logLevel === NULL) {
			$logLevel = [
				\Tracy\Logger::CRITICAL,
				\Tracy\Logger::EXCEPTION,
				\Tracy\Logger::ERROR,
			];
		}

		$logger = new Logger($logLevel);
		\Tracy\Debugger::setLogger($logger);
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
	public static function setup($appName, $license = NULL)
	{
		if ($license === NULL) {
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
