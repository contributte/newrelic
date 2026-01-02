<?php declare(strict_types = 1);

namespace Contributte\NewRelic\Config;

use Nette\PhpGenerator\Method;

final class TransactionTracerConfig
{

	public bool $enabled = true;

	public int $detail = 1;

	public string $recordSql = 'obfuscated';

	public bool $slowSql = true;

	public string $threshold = 'apdex_f';

	public int $stackTraceThreshold = 500;

	public int $explainThreshold = 500;

	public function addInitCode(Method $method): void
	{
		$method->addBody("ini_set('newrelic.transaction_tracer.enabled', ?);", [
			(string) $this->enabled,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.detail', ?);", [
			(string) $this->detail,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.record_sql', ?);", [
			$this->recordSql,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.slow_sql', ?);", [
			(string) $this->slowSql,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.threshold', ?);", [
			$this->threshold,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.stack_trace_thresholdshow', ?);", [
			(string) $this->stackTraceThreshold,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.explain_threshold', ?);", [
			(string) $this->explainThreshold,
		]);
	}

}
