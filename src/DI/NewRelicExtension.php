<?php

declare(strict_types=1);

namespace Contributte\NewRelic\DI;

use Contributte\NewRelic\Callbacks\OnErrorCallback;
use Contributte\NewRelic\Callbacks\OnRequestCallback;
use Contributte\NewRelic\RUM\FooterControl;
use Contributte\NewRelic\RUM\HeaderControl;
use Contributte\NewRelic\RUM\User;
use Contributte\NewRelic\Tracy\Bootstrap;
use Nette\Application\UI\Presenter;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Tracy\Logger;

class NewRelicExtension extends CompilerExtension
{

	/**
	 * @var bool
	 */
	private $skipIfIsDisabled;

	/**
	 * @var bool
	 */
	private $enabled = true;

	/**
	 * @param bool $skipIfIsDisabled
	 */
	public function __construct($skipIfIsDisabled = false)
	{
		$this->skipIfIsDisabled = $skipIfIsDisabled;
	}

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'enabled' => Expect::bool(true),
			'appName' => Expect::type('string|array'),
			'license' => Expect::string(),
			'actionKey' => Expect::string(),
			'logLevel' => Expect::listOf(Expect::string(Expect::anyOf([
				Logger::CRITICAL,
				Logger::EXCEPTION,
				Logger::ERROR,
			])))->default([
				Logger::CRITICAL,
				Logger::EXCEPTION,
				Logger::ERROR,
			]),
			'rum' => Expect::structure([
				'enabled' => Expect::string('auto'),
			]),
			'transactionTracer' => Expect::structure([
				'enabled' => Expect::bool(true),
				'detail' => Expect::int(1),
				'recordSql' => Expect::string('obfuscated'),
				'slowSql' => Expect::bool(true),
				'threshold' => Expect::string('apdex_f'),
				'stackTraceThreshold' => Expect::int(500),
				'explainThreshold' => Expect::int(500),
			]),
			'errorCollector' => Expect::structure([
				'enabled' => Expect::bool(true),
				'recordDatabaseErrors' => Expect::bool(true),
			]),
			'parameters' => Expect::structure([
				'capture' => Expect::bool(false),
				'ignored' => Expect::array(),
			]),
			'custom' => Expect::structure([
				'parameters' => Expect::array(),
				'tracers' => Expect::array(),
			]),
		]);
	}

	public function loadConfiguration()
	{
		$config = $this->getConfig();
		if ($this->skipIfIsDisabled && (!extension_loaded('newrelic') || !Bootstrap::isEnabled())) {
			$this->enabled = false;
		}

		if (!$config->enabled) {
			$this->enabled = false;
		}

		$this->setupRUM();

		if (!$this->enabled) {
			return;
		}

		if (!extension_loaded('newrelic')) {
			throw new \RuntimeException('NewRelic extension is not loaded');
		}

		if (!Bootstrap::isEnabled()) {
			throw new \RuntimeException('NewRelic is not enabled');
		}

		$this->setupApplicationOnRequest();
		$this->setupApplicationOnError();
	}

	public function afterCompile(ClassType $class)
	{
		if (!$this->enabled) {
			return;
		}

		$config = $this->getConfig();
		$initialize = $class->getMethod('initialize');

		// AppName and license
		if ($config->appName && !is_array($config->appName)) {
			$initialize->addBody('\Contributte\NewRelic\Tracy\Bootstrap::setup(?, ?);', [
				$config->appName,
				$config->license,
			]);
		} elseif ($config->appName && is_array($config->appName)) {
			if (!isset($config->appName['*'])) {
				throw new \RuntimeException('Missing default app name as "*"');
			}

			$initialize->addBody('\Contributte\NewRelic\Tracy\Bootstrap::setup(?, ?);', [
				$config->appName['*'],
				$config->license,
			]);
		}

		// Logger
		$initialize->addBody('\Tracy\Debugger::setLogger(new \Contributte\NewRelic\Tracy\Logger(?));', [
			$config->logLevel,
		]);

		$this->setupCustom($initialize);

		// Options
		if ($config->rum->enabled !== 'auto') {
			$initialize->addBody('newrelic_disable_autorum();');
		}

		$initialize->addBody("ini_set('newrelic.transaction_tracer.enabled', ?);", [
			(string) $config->transactionTracer->enabled,
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.detail', ?);", [
			(string) $config->transactionTracer->detail,
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.record_sql', ?);", [
			(string) $config->transactionTracer->recordSql,
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.slow_sql', ?);", [
			(string) $config->transactionTracer->slowSql,
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.threshold', ?);", [
			(string) $config->transactionTracer->threshold,
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.stack_trace_thresholdshow', ?);", [
			(string) $config->transactionTracer->stackTraceThreshold,
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.explain_threshold', ?);", [
			(string) $config->transactionTracer->explainThreshold,
		]);
		$initialize->addBody("ini_set('newrelic.error_collector.enabled', ?);", [
			(string) $config->errorCollector->enabled,
		]);
		$initialize->addBody("ini_set('newrelic.error_collector.record_database_errors', ?);", [
			(string) $config->errorCollector->recordDatabaseErrors,
		]);
		$initialize->addBody('newrelic_capture_params(?);', [
			$config->parameters->capture,
		]);
		$initialize->addBody("ini_set('newrelic.ignored_params', ?);", [
			implode(',', $config->parameters->ignored),
		]);
	}

	private function setupApplicationOnRequest()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$map = $config->appName && is_array($config->appName) ? $config->appName : [];

		$builder->addDefinition($this->prefix('onRequestCallback'))
			->setFactory(OnRequestCallback::class, [
				$map,
				$config->license,
				$config->actionKey ?? Presenter::ACTION_KEY,
			]);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onRequest[] = ?', ['@' . $this->prefix('onRequestCallback')]);
	}

	private function setupApplicationOnError()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setFactory(OnErrorCallback::class);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onError[] = ?', ['@' . $this->prefix('onErrorCallback')]);
	}

	private function setupCustom(Method $initialize)
	{
		$config = $this->getConfig();

		foreach ($config->custom->parameters as $name => $value) {
			$initialize->addBody('\Contributte\NewRelic\Tracy\Custom\Parameters::addParameter(?, ?);', [
				$name,
				$value,
			]);
		}

		foreach ($config->custom->tracers as $function) {
			$initialize->addBody('\Contributte\NewRelic\Tracy\Custom\Tracers::addTracer(?);', [$function]);
		}
	}

	private function setupRUM()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$rumEnabled = $this->enabled && $config->rum->enabled === true;

		$builder->addDefinition($this->prefix('rum.user'))
			->setFactory(User::class, [$rumEnabled]);

		$builder->addDefinition($this->prefix('rum.headerControl'))
			->setFactory(HeaderControl::class, [$rumEnabled]);

		$builder->addDefinition($this->prefix('rum.footerControl'))
			->setFactory(FooterControl::class, [$rumEnabled]);
	}

}
