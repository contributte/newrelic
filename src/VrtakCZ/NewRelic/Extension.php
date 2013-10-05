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
	private $disabled = FALSE;

	/** @var array */
	public $defaults = array(
		'logLevel' => array(
			\Nette\Diagnostics\Logger::ERROR,
			\Nette\Diagnostics\Logger::CRITICAL,
		),
		'rum' => array(
			'autoEnable' => TRUE,
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
			'ignored' => '',
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
			$this->disabled = TRUE;
		}

		if (isset($config['disable']) && $config['disable']) {
			$this->disabled = TRUE;
		}

		$this->setupRUM();

		if ($this->disabled) {
			return;
		}

		if (!extension_loaded('newrelic')) {
			throw new \InvalidStateException('NewRelic extension is not loaded');
		} elseif (!ini_get('newrelic.enabled')) {
			throw new \InvalidStateException('NewRelic is not enabled');
		}

		$this->setupApplicationOnRequest();
		$this->setupApplicationOnError();
		$this->setupParameters();
	}

	public function afterCompile(ClassType $class)
	{
		if ($this->disabled) {
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
		if ($config['rum']['autoEnable']) {
			$initialize->addBody('newrelic_disable_autorum();');
		}
		$initialize->addBody("ini_set('newrelic.transaction_tracer.enabled', ?);", array(
			$config['transactionTracer']['enabled'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.detail', ?);", array(
			$config['transactionTracer']['detail'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.record_sql', ?);", array(
			$config['transactionTracer']['recordSql'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.slow_sql', ?);", array(
			$config['transactionTracer']['slowSql'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.threshold', ?);", array(
			$config['transactionTracer']['threshold'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.stack_trace_thresholdshow', ?);", array(
			$config['transactionTracer']['stackTraceThreshold'],
		));
		$initialize->addBody("ini_set('newrelic.transaction_tracer.explain_threshold', ?);", array(
			$config['transactionTracer']['explainThreshold'],
		));
		$initialize->addBody("ini_set('newrelic.error_collector.enabled', ?);", array(
			$config['errorCollector']['enabled'],
		));
		$initialize->addBody("ini_set('newrelic.error_collector.record_database_errors', ?);", array(
			$config['errorCollector']['recordDatabaseErrors'],
		));
		$initialize->addBody("newrelic_capture_params(?);", array(
			$config['parameters']['capture'],
		));
		$initialize->addBody("ini_set('newrelic.ignored_params', ?);", array(
			$config['parameters']['ignored'],
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

	private function setupParameters()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		if (isset($config['parameters']['args'])) {
			if (!is_array($config['parameters']['args'])) {
				throw new \InvalidStateException('Invalid parameters structure');
			}
			if (count($config['parameters']['args']) < 1) {
				return;
			}

			$parameters = $builder->addDefinition($this->prefix('parameters'))
				->setClass('VrtakCZ\NewRelic\Parameters')
				->addTag('run', true);
			foreach ($config['parameters']['args'] as $name => $value) {
				$parameters->addSetup('setParameter', array($name, $value));
			}
		}
	}

	private function setupRUM()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('rum.user'))
			->setClass('VrtakCZ\NewRelic\RUM\User', array($this->disabled));

		$builder->addDefinition($this->prefix('rum.headerControl'))
			->setClass('VrtakCZ\NewRelic\RUM\HeaderControl', array($this->disabled));

		$builder->addDefinition($this->prefix('rum.footerControl'))
			->setClass('VrtakCZ\NewRelic\RUM\FooterControl', array($this->disabled));
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
