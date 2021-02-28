<?php

declare(strict_types=1);

namespace Contributte\NewRelic\DI;

use Contributte\Console\Application;
use Contributte\NewRelic\Events\Listeners\ConsoleListener;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceCreationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NewRelicConsoleExtension extends CompilerExtension
{

	/**
	 * @var bool
	 */
	private $skipIfIsDisabled;

	/**
	 * @param bool $skipIfIsDisabled
	 */
	public function __construct($skipIfIsDisabled = false)
	{
		$this->skipIfIsDisabled = $skipIfIsDisabled;
	}

	public function loadConfiguration(): void
	{
		if (!class_exists(Application::class)) {
			throw new ServiceCreationException(sprintf('Missing "%s" service', Application::class));
		}

		if (!interface_exists(EventDispatcherInterface::class)) {
			throw new ServiceCreationException(sprintf('Missing "%s" service', EventDispatcherInterface::class));
		}

		$config = $this->getConfig();
		$enabled = (bool) ini_get('newrelic.enabled');

		if ($this->skipIfIsDisabled && (!extension_loaded('newrelic') || !$enabled)) {
			return;
		}

		if (isset($config['enabled']) && !$config['enabled']) {
			return;
		}

		$this->setupConsoleListener();
	}

	private function setupConsoleListener(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('consoleListener'))
			->setFactory(ConsoleListener::class);
	}

}
