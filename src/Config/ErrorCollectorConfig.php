<?php declare(strict_types = 1);

namespace Contributte\NewRelic\Config;

use Nette\PhpGenerator\Method;

final class ErrorCollectorConfig
{

	public bool $enabled = true;

	public bool $recordDatabaseErrors = true;

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
