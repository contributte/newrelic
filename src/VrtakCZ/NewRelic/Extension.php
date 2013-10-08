<?php

namespace VrtakCZ\NewRelic;

use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Nette\Utils\PhpGenerator\ClassType;

class Extension extends \Nette\Config\CompilerExtension
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
		$this->setupCustomParameters();
		$this->setupCustomTracer();

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
	}

	public function afterCompile(ClassType $class)
	{
		if (!$this->enabled) {
			return;
		}

		$config = $this->getConfig($this->defaults);
		$initialize = $class->methods['initialize'];

		// AppName and license
		if (isset($config['appName'])) {
			$initialize->addBody('\VrtakCZ\NewRelic\Extension::setupAppName(?, ?);', array(
				$config['appName'], isset($config['license']) ? $config['license'] : NULL
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

		$onRequestCallback = $builder->addDefinition($this->prefix('onRequestCallback'))
			->addSetup('register', array('@\Nette\Application\Application'))
			->addTag('run', true);
		if (isset($config['actionKey'])) {
			$onRequestCallback->setClass('VrtakCZ\NewRelic\OnRequestCallback', array($config['actionKey']));
		} else {
			$onRequestCallback->setClass('VrtakCZ\NewRelic\OnRequestCallback');
		}
	}

	private function setupApplicationOnError()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setClass('VrtakCZ\NewRelic\OnErrorCallback')
			->addSetup('register', array('@\Nette\Application\Application'))
			->addTag('run', true);
	}

	private function setupCustomParameters()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$customParameters = $builder->addDefinition($this->prefix('customParameters'))
			->setClass('VrtakCZ\NewRelic\CustomParameters')
			->addTag('run', true);

		if (isset($config['customParameters'])) {
			if (!is_array($config['customParameters'])) {
				throw new \InvalidStateException('Invalid custom parameters structure');
			}

			foreach ($config['customParameters'] as $name => $value) {
				$customParameters->addSetup('addParameter', array($name, $value));
			}
		}
	}

	private function setupCustomTracer()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$customTracer = $builder->addDefinition($this->prefix('customTracer'))
			->setClass('VrtakCZ\NewRelic\CustomTracer')
			->addTag('run', true);

		if (isset($config['customTracer'])) {
			if (!is_array($config['customTracer'])) {
				throw new \InvalidStateException('Invalid custom tracer structure');
			}

			foreach ($config['customTracer'] as $function) {
				$customTracer->addSetup('addTracer', array($function));
			}
		}
	}

	private function setupRUM()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$rumEnabled = $this->enabled && true === $config['rum']['enabled'];

		$builder->addDefinition($this->prefix('rum'))
			->setClass('Nette\DI\NestedAccessor', array('@container', $this->prefix('rum')));

		$builder->addDefinition($this->prefix('rum.user'))
			->setClass('VrtakCZ\NewRelic\RUM\User', array($rumEnabled));

		$builder->addDefinition($this->prefix('rum.headerControl'))
			->setClass('VrtakCZ\NewRelic\RUM\HeaderControl', array($rumEnabled));

		$builder->addDefinition($this->prefix('rum.footerControl'))
			->setClass('VrtakCZ\NewRelic\RUM\FooterControl', array($rumEnabled));
	}

	/**
	 * @param \Nette\Config\Configurator
	 * @param string
	 */
	public static function register(Configurator $configurator, $name = 'newrelic')
	{
		$class = get_called_class();
		$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) use ($class, $name) {
			$compiler->addExtension($name, new $class);
		};
	}
}
