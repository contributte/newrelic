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
	 * @var array
	 */
	public $defaults = [
		'logLevel' => [
			Logger::CRITICAL,
			Logger::EXCEPTION,
			Logger::ERROR,
		],
		'rum' => [
			'enabled' => 'auto',
		],
		'transactionTracer' => [
			'enabled' => true,
			'detail' => 1,
			'recordSql' => 'obfuscated',
			'slowSql' => true,
			'threshold' => 'apdex_f',
			'stackTraceThreshold' => 500,
			'explainThreshold' => 500,
		],
		'errorCollector' => [
			'enabled' => true,
			'recordDatabaseErrors' => true,
		],
		'parameters' => [
			'capture' => false,
			'ignored' => [],
		],
	];

	/**
	 * @param bool $skipIfIsDisabled
	 */
	public function __construct($skipIfIsDisabled = false)
	{
		$this->skipIfIsDisabled = $skipIfIsDisabled;
	}

	public function loadConfiguration()
	{
		$config = $this->getConfig();
		if ($this->skipIfIsDisabled && (!extension_loaded('newrelic') || !Bootstrap::isEnabled())) {
			$this->enabled = false;
		}

		if (isset($config['enabled']) && !$config['enabled']) {
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

		$config = $this->getConfig($this->defaults);
		$initialize = $class->getMethod('initialize');

		// AppName and license
		if (isset($config['appName']) && !is_array($config['appName'])) {
			$initialize->addBody('\Contributte\NewRelic\Tracy\Bootstrap::setup(?, ?);', [
				$config['appName'],
				$config['license'] ?? null,
			]);
		} elseif (isset($config['appName']) && is_array($config['appName'])) {
			if (!isset($config['appName']['*'])) {
				throw new \RuntimeException('Missing default app name as "*"');
			}

			$initialize->addBody('\Contributte\NewRelic\Tracy\Bootstrap::setup(?, ?);', [
				$config['appName']['*'],
				$config['license'] ?? null,
			]);
		}

		// Logger
		$initialize->addBody('\Tracy\Debugger::setLogger(new \Contributte\NewRelic\Tracy\Logger(?));', [
			array_unique($config['logLevel']),
		]);

		$this->setupCustom($initialize);

		// Options
		if ($config['rum']['enabled'] !== 'auto') {
			$initialize->addBody('newrelic_disable_autorum();');
		}

		$initialize->addBody("ini_set('newrelic.transaction_tracer.enabled', ?);", [
			(string) $config['transactionTracer']['enabled'],
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.detail', ?);", [
			(string) $config['transactionTracer']['detail'],
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.record_sql', ?);", [
			(string) $config['transactionTracer']['recordSql'],
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.slow_sql', ?);", [
			(string) $config['transactionTracer']['slowSql'],
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.threshold', ?);", [
			(string) $config['transactionTracer']['threshold'],
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.stack_trace_thresholdshow', ?);", [
			(string) $config['transactionTracer']['stackTraceThreshold'],
		]);
		$initialize->addBody("ini_set('newrelic.transaction_tracer.explain_threshold', ?);", [
			(string) $config['transactionTracer']['explainThreshold'],
		]);
		$initialize->addBody("ini_set('newrelic.error_collector.enabled', ?);", [
			(string) $config['errorCollector']['enabled'],
		]);
		$initialize->addBody("ini_set('newrelic.error_collector.record_database_errors', ?);", [
			(string) $config['errorCollector']['recordDatabaseErrors'],
		]);
		$initialize->addBody('newrelic_capture_params(?);', [
			$config['parameters']['capture'],
		]);
		$initialize->addBody("ini_set('newrelic.ignored_params', ?);", [
			implode(',', $config['parameters']['ignored']),
		]);
	}

	private function setupApplicationOnRequest()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$map = isset($config['appName']) && is_array($config['appName']) ? $config['appName'] : [];
		$license = $config['license'] ?? null;

		$builder->addDefinition($this->prefix('onRequestCallback'))
			->setFactory(OnRequestCallback::class, [
				$map,
				$license,
				$config['actionKey'] ?? Presenter::ACTION_KEY,
			]);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onRequest[] = ?', ['@' . $this->prefix('onRequestCallback')]);
	}

	private function setupApplicationOnError()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setClass(OnErrorCallback::class);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onError[] = ?', ['@' . $this->prefix('onErrorCallback')]);
	}

	private function setupCustom(Method $initialize)
	{
		$config = $this->getConfig();

		if (isset($config['custom']['parameters'])) {
			if (!is_array($config['custom']['parameters'])) {
				throw new \RuntimeException('Invalid custom parameters structure');
			}

			foreach ($config['custom']['parameters'] as $name => $value) {
				$initialize->addBody('\Contributte\NewRelic\Tracy\Custom\Parameters::addParameter(?, ?);', [
					$name,
					$value,
				]);
			}
		}

		if (isset($config['custom']['tracers'])) {
			if (!is_array($config['custom']['tracers'])) {
				throw new \RuntimeException('Invalid custom tracers structure');
			}

			foreach ($config['custom']['tracers'] as $function) {
				$initialize->addBody('\Contributte\NewRelic\Tracy\Custom\Tracers::addTracer(?);', [$function]);
			}
		}
	}

	private function setupRUM()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$rumEnabled = $this->enabled && $config['rum']['enabled'] === true;

		$builder->addDefinition($this->prefix('rum.user'))
			->setFactory(User::class, [$rumEnabled]);

		$builder->addDefinition($this->prefix('rum.headerControl'))
			->setFactory(HeaderControl::class, [$rumEnabled]);

		$builder->addDefinition($this->prefix('rum.footerControl'))
			->setFactory(FooterControl::class, [$rumEnabled]);
	}

}
