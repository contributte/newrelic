<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Config;

use Nette\PhpGenerator\Method;

final class TransactionTracerConfig
{

	/**
	 * @var bool
	 */
	public $enabled = true;

	/**
	 * @var int
	 */
	public $detail = 1;

	/**
	 * @var string
	 */
	public $recordSql = 'obfuscated';

	/**
	 * @var bool
	 */
	public $slowSql = true;

	/**
	 * @var string
	 */
	public $threshold = 'apdex_f';

	/**
	 * @var int
	 */
	public $stackTraceThreshold = 500;

	/**
	 * @var int
	 */
	public $explainThreshold = 500;

	public function addInitCode(Method $method): void
	{
		$method->addBody("ini_set('newrelic.transaction_tracer.enabled', ?);", [
			(string) $this->enabled,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.detail', ?);", [
			(string) $this->detail,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.record_sql', ?);", [
			(string) $this->recordSql,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.slow_sql', ?);", [
			(string) $this->slowSql,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.threshold', ?);", [
			(string) $this->threshold,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.stack_trace_thresholdshow', ?);", [
			(string) $this->stackTraceThreshold,
		]);
		$method->addBody("ini_set('newrelic.transaction_tracer.explain_threshold', ?);", [
			(string) $this->explainThreshold,
		]);
	}

}
