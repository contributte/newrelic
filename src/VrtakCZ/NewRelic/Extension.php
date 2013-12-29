<?php

namespace VrtakCZ\NewRelic;

use Nette\PhpGenerator\ClassType;
use Nette\Application\UI\Presenter;

class Extension extends \Nette\DI\CompilerExtension
{
	/** @var bool */
	private $skipIfIsDisabled;
	/** @var bool */
	private $enabled = TRUE;

	/** @var array */
	public $defaults = array(
		'logLevel' => array(
			\Nette\Diagnostics\Logger::ERROR,
			\Nette\Diagnostics\Logger::CRITICAL,
		),
		'rum' => array(
			'enabled' => 'auto',
			'ratio' => 1,
		),
		'transactionTracer' => array(
			'enabled' => TRUE,
			'detail' => 1,
			'recordSql' => 'obfuscated',
			'slowSql' => TRUE,
			'threshold' => 'apdex_f',
			'stackTraceThreshold' => 500,
			'explainThreshold' => 500,
		),
		'errorCollector' => array(
			'enabled' => TRUE,
			'recordDatabaseErrors' => TRUE,
		),
		'parameters' => array(
			'capture' => FALSE,
			'ignored' => array(),
		),
	);

	/**
	 * @param bool
	 */
	public function __construct($skipIfIsDisabled = FALSE)
	{
		$this->skipIfIsDisabled = $skipIfIsDisabled;
	}

	public function loadConfiguration()
	{
		$config = $this->getConfig();
		if ($this->skipIfIsDisabled && (!extension_loaded('newrelic') || !ini_get('newrelic.enabled'))) {
			$this->enabled = FALSE;
		}

		if (isset($config['enabled']) && !$config['enabled']) {
			$this->enabled = FALSE;
		}

		$this->setupRUM();
		$this->setupCustom();

		if (!$this->enabled) {
			return;
		}

		if (!extension_loaded('newrelic')) {
			throw new \InvalidStateException('NewRelic extension is not loaded');
		} elseif (!ini_get('newrelic.enabled')) {
			throw new \InvalidStateException('NewRelic is not enabled');
		}

		$this->setupApplicationOnRequest();
		$this->setupApplicationOnError();

		if (isset($config['ratio']) && mt_rand(0, 99) > round($config['ratio'] * 100) - 1) {
			newrelic_ignore_transaction();
		}
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
			$initialize->addBody('\VrtakCZ\NewRelic\Extension::setupAppName(?, ?);', array(
				$config['appName'], isset($config['license']) ? $config['license'] : NULL
			));
		} elseif (isset($config['appName']) && is_array($config['appName'])) {
			if (!isset($config['appName']['*'])) {
				throw new \InvalidStateException('Missing default app name as "*"');
			}
			$initialize->addBody('\VrtakCZ\NewRelic\Extension::setupAppName(?, ?);', array(
				$config['appName']['*'], isset($config['license']) ? $config['license'] : NULL
			));
		}

		// Logger
		$initialize->addBody('$newRelicLogger = new \VrtakCZ\NewRelic\Logger(?);', array(
			array_unique($config['logLevel'])
		));
		$initialize->addBody('\Nette\Diagnostics\Debugger::$logger = $newRelicLogger;');

		// Options
		if ('auto' !== $config['rum']['enabled']) {
			$initialize->addBody('newrelic_disable_autorum();');
		}
		$initialize->addBody("ini_set('newrelic.transaction_tracer.enabled', ?);", array(
			(string) $config['transactionTracer']['enabled'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.detail', ?);", array(
			(string) $config['transactionTracer']['detail'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.record_sql', ?);", array(
			(string) $config['transactionTracer']['recordSql'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.slow_sql', ?);", array(
			(string) $config['transactionTracer']['slowSql'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.threshold', ?);", array(
			(string) $config['transactionTracer']['threshold'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.stack_trace_thresholdshow', ?);", array(
			(string) $config['transactionTracer']['stackTraceThreshold'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.explain_threshold', ?);", array(
			(string) $config['transactionTracer']['explainThreshold'],
		));
		$initialize->addBody("ini_set('newrelic.error_collector.enabled', ?);", array(
			(string) $config['errorCollector']['enabled'],
		));
		$initialize->addBody("ini_set('newrelic.error_collector.record_database_errors', ?);", array(
			(string) $config['errorCollector']['recordDatabaseErrors'],
		));
		$initialize->addBody("newrelic_capture_params(?);", array(
			$config['parameters']['capture'],
		));
		$initialize->addBody("ini_set('newrelic.ignored_params', ?);", array(
			implode(',', $config['parameters']['ignored']),
		));
	}

	/**
	 * @param string
	 * @param string|NULL
	 */
	public static function setupAppName($appName, $license = NULL)
	{
		if ($license === NULL) {
			newrelic_set_appname($appName);
		} else {
			newrelic_set_appname($appName, $license);
		}
	}

	private function setupApplicationOnRequest()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$map = (isset($config['appName']) && is_array($config['appName'])) ? $config['appName'] : array();
		$license = isset($config['license']) ? $config['license'] : NULL;

		$builder->addDefinition($this->prefix('onRequestCallback'))
			->setClass('VrtakCZ\NewRelic\Callbacks\OnRequestCallback', array(
				$map, $license, isset($config['actionKey']) ? $config['actionKey'] : Presenter::ACTION_KEY,
			))
			->addSetup('register', array('@\Nette\Application\Application'))
			->addTag('run', TRUE);
	}

	private function setupApplicationOnError()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setClass('VrtakCZ\NewRelic\Callbacks\OnErrorCallback')
			->addSetup('register', array('@\Nette\Application\Application'))
			->addTag('run', TRUE);
	}

	private function setupCustom()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$customParameters = $builder->addDefinition($this->prefix('custom.parameters'))
			->setClass('VrtakCZ\NewRelic\Custom\Parameters', array($this->enabled))
			->addTag('run', TRUE);

		if (isset($config['custom']['parameters'])) {
			if (!is_array($config['custom']['parameters'])) {
				throw new \InvalidStateException('Invalid custom parameters structure');
			}

			foreach ($config['custom']['parameters'] as $name => $value) {
				$customParameters->addSetup('addParameter', array($name, $value));
			}
		}

		$customTracers = $builder->addDefinition($this->prefix('custom.tracers'))
			->setClass('VrtakCZ\NewRelic\Custom\Tracers', array($this->enabled))
			->addTag('run', TRUE);

		if (isset($config['custom']['tracers'])) {
			if (!is_array($config['custom']['tracers'])) {
				throw new \InvalidStateException('Invalid custom tracers structure');
			}

			foreach ($config['custom']['tracers'] as $function) {
				$customTracers->addSetup('addTracer', array($function));
			}
		}

		$builder->addDefinition($this->prefix('custom.metrics'))
			->setClass('VrtakCZ\NewRelic\Custom\Metrics', array($this->enabled));
	}

	private function setupRUM()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$rumEnabled = $this->enabled && $config['rum']['enabled'] === TRUE && mt_rand(0, 99) <= round($config['rum']['ratio'] * 100) - 1;

		$builder->addDefinition($this->prefix('rum.user'))
			->setClass('VrtakCZ\NewRelic\RUM\User', array($rumEnabled));

		$builder->addDefinition($this->prefix('rum.headerControl'))
			->setClass('VrtakCZ\NewRelic\RUM\HeaderControl', array($rumEnabled));

		$builder->addDefinition($this->prefix('rum.footerControl'))
			->setClass('VrtakCZ\NewRelic\RUM\FooterControl', array($rumEnabled));
	}
}
