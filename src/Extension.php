<?php

namespace VrtakCZ\NewRelic\Nette;

use Nette\Application\UI\Presenter;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use VrtakCZ\NewRelic\Tracy\Bootstrap;

class Extension extends \Nette\DI\CompilerExtension
{

	/** @var bool */
	private $skipIfIsDisabled;

	/** @var bool */
	private $enabled = TRUE;

	/** @var array */
	public $defaults = [
		'logLevel' => [
			\Tracy\Logger::CRITICAL,
			\Tracy\Logger::EXCEPTION,
			\Tracy\Logger::ERROR,
		],
		'rum' => [
			'enabled' => 'auto',
		],
		'transactionTracer' => [
			'enabled' => TRUE,
			'detail' => 1,
			'recordSql' => 'obfuscated',
			'slowSql' => TRUE,
			'threshold' => 'apdex_f',
			'stackTraceThreshold' => 500,
			'explainThreshold' => 500,
		],
		'errorCollector' => [
			'enabled' => TRUE,
			'recordDatabaseErrors' => TRUE,
		],
		'parameters' => [
			'capture' => FALSE,
			'ignored' => [],
		],
	];

	/**
	 * @param bool $skipIfIsDisabled
	 */
	public function __construct($skipIfIsDisabled = FALSE)
	{
		$this->skipIfIsDisabled = $skipIfIsDisabled;
	}

	public function loadConfiguration()
	{
		$config = $this->getConfig();
		if ($this->skipIfIsDisabled && (!extension_loaded('newrelic') || !Bootstrap::isEnabled())) {
			$this->enabled = FALSE;
		}

		if (isset($config['enabled']) && !$config['enabled']) {
			$this->enabled = FALSE;
		}

		$this->setupRUM();

		if (!$this->enabled) {
			return;
		}

		if (!extension_loaded('newrelic')) {
			throw new \RuntimeException('NewRelic extension is not loaded');
		} elseif (!Bootstrap::isEnabled()) {
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
		$initialize = $class->methods['initialize'];

		// AppName and license
		if (isset($config['appName']) && !is_array($config['appName'])) {
			$initialize->addBody('\VrtakCZ\NewRelic\Tracy\Bootstrap::setup(?, ?);', [
				$config['appName'],
				isset($config['license']) ? $config['license'] : NULL,
			]);
		} elseif (isset($config['appName']) && is_array($config['appName'])) {
			if (!isset($config['appName']['*'])) {
				throw new \RuntimeException('Missing default app name as "*"');
			}
			$initialize->addBody('\VrtakCZ\NewRelic\Tracy\Bootstrap::setup(?, ?);', [
				$config['appName']['*'],
				isset($config['license']) ? $config['license'] : NULL,
			]);
		}

		// Logger
		$initialize->addBody('\Tracy\Debugger::setLogger(new \VrtakCZ\NewRelic\Tracy\Logger(?));', [
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

		$map = (isset($config['appName']) && is_array($config['appName'])) ? $config['appName'] : [];
		$license = isset($config['license']) ? $config['license'] : NULL;

		$builder->addDefinition($this->prefix('onRequestCallback'))
			->setClass('VrtakCZ\NewRelic\Nette\Callbacks\OnRequestCallback', [
				$map,
				$license,
				isset($config['actionKey']) ? $config['actionKey'] : Presenter::ACTION_KEY,
			])
			->addSetup('register', ['@\Nette\Application\Application'])
			->addTag('run', TRUE);
	}

	private function setupApplicationOnError()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setClass('VrtakCZ\NewRelic\Nette\Callbacks\OnErrorCallback')
			->addSetup('register', ['@\Nette\Application\Application'])
			->addTag('run', TRUE);
	}

	private function setupCustom(Method $initialize)
	{
		$config = $this->getConfig();

		if (isset($config['custom']['parameters'])) {
			if (!is_array($config['custom']['parameters'])) {
				throw new \RuntimeException('Invalid custom parameters structure');
			}

			foreach ($config['custom']['parameters'] as $name => $value) {
				$initialize->addBody('\VrtakCZ\NewRelic\Tracy\Custom\Parameters::addParameter(?, ?);', [
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
				$initialize->addBody('\VrtakCZ\NewRelic\Tracy\Custom\Tracers::addTracer(?);', [$function]);
			}
		}
	}

	private function setupRUM()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$rumEnabled = $this->enabled && $config['rum']['enabled'] === TRUE;

		$builder->addDefinition($this->prefix('rum.user'))
			->setClass('VrtakCZ\NewRelic\Nette\RUM\User', [$rumEnabled]);

		$builder->addDefinition($this->prefix('rum.headerControl'))
			->setClass('VrtakCZ\NewRelic\Nette\RUM\HeaderControl', [$rumEnabled]);

		$builder->addDefinition($this->prefix('rum.footerControl'))
			->setClass('VrtakCZ\NewRelic\Nette\RUM\FooterControl', [$rumEnabled]);
	}

}
