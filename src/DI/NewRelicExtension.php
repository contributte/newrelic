<?php

declare(strict_types=1);

namespace Contributte\NewRelic\DI;

use Contributte\NewRelic\Agent\ProductionAgent;
use Contributte\NewRelic\Callbacks\OnErrorCallback;
use Contributte\NewRelic\Callbacks\OnRequestCallback;
use Contributte\NewRelic\Config\ErrorCollectorConfig;
use Contributte\NewRelic\Config\ParametersConfig;
use Contributte\NewRelic\Config\TransactionTracerConfig;
use Contributte\NewRelic\Environment;
use Contributte\NewRelic\Formatters\DefaultWebTransactionNameFormatter;
use Contributte\NewRelic\RUM\FooterControl;
use Contributte\NewRelic\RUM\HeaderControl;
use Contributte\NewRelic\RUM\User;
use Contributte\NewRelic\Tracy\Logger;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Tracy\Debugger;
use Tracy\ILogger;

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
			'appName' => Expect::string('PHP Application'),
			'license' => Expect::string(''),
			'logLevel' => Expect::listOf(Expect::anyOf(
				ILogger::CRITICAL,
				ILogger::EXCEPTION,
				ILogger::ERROR
			)),
			'rum' => Expect::structure([
				'enabled' => Expect::string('auto'),
			]),
			'transactionNameFormatter' => Expect::string(DefaultWebTransactionNameFormatter::class),
			'transactionTracer' => Expect::from(new TransactionTracerConfig),
			'errorCollector' => Expect::from(new ErrorCollectorConfig),
			'parameters' => Expect::from(new ParametersConfig),
			'custom' => Expect::structure([
				'parameters' => Expect::array(),
				'tracers' => Expect::array(),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var \stdClass $config */
		$config = $this->getConfig();
		$enabled = (bool) ini_get('newrelic.enabled');

		if ($this->skipIfIsDisabled && (!extension_loaded('newrelic') || !$enabled)) {
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

		if (!$enabled) {
			throw new \RuntimeException('NewRelic is not enabled');
		}

		$builder->addDefinition($this->prefix('agent'))
			->setFactory(ProductionAgent::class, [
				$this->enabled,
			]);

		$builder->addDefinition($this->prefix('logger'))
			->setFactory(Logger::class, [
				$builder->getDefinition($this->prefix('agent')),
				$builder->getDefinition('tracy.logger'),
				$config->logLevel,
			])
			->setAutowired(false);

		$this->setupApplicationOnRequest();
		$this->setupApplicationOnError();
	}

	public function afterCompile(ClassType $class): void
	{
		if (!$this->enabled) {
			return;
		}

		/** @var \stdClass $config */
		$config = $this->getConfig();
		$initialize = $class->getMethod('initialize');

		// AppName and license
		if ($config->appName) {
			$initialize->addBody('$this->getService(?)->setAppName(?, ?);', [
				$this->prefix('agent'),
				$config->appName,
				$config->license,
			]);
		}

		// Logger
		$initialize->addBody(Debugger::class . '::setLogger($this->getService(?));', [
			$this->prefix('logger'),
		]);

		$this->setupCustom($initialize);

		// Options
		if ($config->rum->enabled !== 'auto') {
			$initialize->addBody('$this->getService(?)->disableAutorum();', [
				$this->prefix('agent'),
			]);
		}

		$config->transactionTracer->addInitCode($initialize);
		$config->errorCollector->addInitCode($initialize);
		$config->parameters->addInitCode($initialize);
	}

	private function setupApplicationOnRequest(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var \stdClass $config */
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('environment'))
			->setFactory(Environment::class);

		$builder->addDefinition($this->prefix('transactionNameFormatter.web'))
			->setFactory($config->transactionNameFormatter);

		$builder->addDefinition($this->prefix('onRequestCallback'))
			->setFactory(OnRequestCallback::class);

		/** @var ServiceDefinition $application */
		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onRequest[] = ?', ['@' . $this->prefix('onRequestCallback')]);
	}

	private function setupApplicationOnError(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setFactory(OnErrorCallback::class, [
				'@' . $this->prefix('agent'),
			]);

		/** @var ServiceDefinition $application */
		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onError[] = ?', ['@' . $this->prefix('onErrorCallback')]);
	}

	private function setupCustom(Method $initialize): void
	{
		/** @var \stdClass $config */
		$config = $this->getConfig();

		foreach ($config->custom->parameters as $name => $value) {
			$initialize->addBody('$this->getService(?)->addCustomParameter(?, ?);', [
				$this->prefix('agent'),
				$name,
				$value,
			]);
		}

		foreach ($config->custom->tracers as $function) {
			$initialize->addBody('$this->getService(?)->addCustomTracer(?);', [
				$this->prefix('agent'),
				$function,
			]);
		}
	}

	private function setupRUM(): void
	{
		/** @var \stdClass $config */
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$rumEnabled = $this->enabled && $config->rum->enabled === true;

		$builder->addDefinition($this->prefix('rum.user'))
			->setFactory(User::class, [
				'@' . $this->prefix('agent'),
				$rumEnabled,
			]);

		$builder->addDefinition($this->prefix('rum.headerControl'))
			->setFactory(HeaderControl::class, [
				'@' . $this->prefix('agent'),
				$rumEnabled,
			]);

		$builder->addDefinition($this->prefix('rum.footerControl'))
			->setFactory(FooterControl::class, [
				'@' . $this->prefix('agent'),
				$rumEnabled,
			]);
	}

}
