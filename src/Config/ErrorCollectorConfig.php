<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Config;

use Nette\PhpGenerator\Method;

final class ErrorCollectorConfig
{

	/**
	 * newrelic.error_collector.enabled
	 *
	 * @var bool
	 *
	 * Enable the New Relic error collector. This will record the 20 most
	 * severe errors per harvest cycle. It is rare to want to disable this.
	 * Please also note that your New Relic subscription level may force
	 * this to be disabled regardless of any value you set for it.
	 */
	public $enabled = true;

	/**
	 * newrelic.error_collector.record_database_errors
	 *
	 * @var bool
	 *
	 * Currently only supported for MySQL database functions. If enabled,
	 * this will cause errors returned by various MySQL functions to be
	 * treated as if they were PHP errors, and thus subject to error
	 * collection. This is only obeyed if the error collector is enabled
	 * above and the account subscription level permits error trapping.
	 */
	public $recordDatabaseErrors = true;

	public function addInitCode(Method $method): void
	{
		$method->addBody("ini_set('newrelic.error_collector.enabled', ?);", [
			(string) $this->enabled,
		]);
		$method->addBody("ini_set('newrelic.error_collector.record_database_errors', ?);", [
			(string) $this->recordDatabaseErrors,
		]);
	}

}
