<?php

declare(strict_types=1);

namespace Contributte\NewRelic\DI;

use Contributte\NewRelic\Callbacks\OnErrorCallback;
use Contributte\NewRelic\Callbacks\OnRequestCallback;
use Contributte\NewRelic\Config\ErrorCollectorConfig;
use Contributte\NewRelic\Config\ParametersConfig;
use Contributte\NewRelic\Config\TransactionTracerConfig;
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
			'appName' => Expect::string(),
			'license' => Expect::string(),
			'actionKey' => Expect::string(Presenter::ACTION_KEY),
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

	public function afterCompile(ClassType $class): void
	{
		if (!$this->enabled) {
			return;
		}

		$config = $this->getConfig();
		$initialize = $class->getMethod('initialize');

		// AppName and license
		if ($config->appName) {
			$initialize->addBody('\Contributte\NewRelic\Tracy\Bootstrap::setup(?, ?);', [
				$config->appName,
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

		$config->transactionTracer->addInitCode($initialize);
		$config->errorCollector->addInitCode($initialize);
		$config->parameters->addInitCode($initialize);
	}

	private function setupApplicationOnRequest(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('onRequestCallback'))
			->setFactory(OnRequestCallback::class, [
				$config->actionKey,
			]);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onRequest[] = ?', ['@' . $this->prefix('onRequestCallback')]);
	}

	private function setupApplicationOnError(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setFactory(OnErrorCallback::class);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onError[] = ?', ['@' . $this->prefix('onErrorCallback')]);
	}

	private function setupCustom(Method $initialize): void
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

	private function setupRUM(): void
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
