<?php

namespace VrtakCZ\Newrelic;

use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Nette\Diagnostics\Debugger;

class Extension extends \Nette\Config\CompilerExtension
{
	public function loadConfiguration()
	{
		if (!extension_loaded('newrelic')) {
			throw new \InvalidStateException('NewRelic extension is not loaded');
		}

		$this->setupLogger();
		$this->setupApplicationOnRequest();
		$this->setupApplicationOnError();
	}

	private function setupLogger()
	{
		$config = $this->getConfig();

		if (isset($config['appName'])) {
			if (isset($config['license'])) {
				newrelic_set_appname($config['appName'], $config['license']);
			} else {
				newrelic_set_appname($config['appName']);
			}
		}

		$logger = new Logger;
		Debugger::$logger = $logger;
	}

	private function setupApplicationOnRequest()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$onRequestCallback = $builder->addDefinition($this->prefix('onRequestCallback'))
			->addSetup('register', array('@\Nette\Application\Application'));
		if (isset($config['actionKey'])) {
			$onRequestCallback->setClass('VrtakCZ\Newrelic\OnRequestCallback', array($config['actionKey']));
		} else {
			$onRequestCallback->setClass('VrtakCZ\Newrelic\OnRequestCallback');
		}
	}

	private function setupApplicationOnError()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('onErrorCallback'))
			->setClass('VrtakCZ\Newrelic\OnErrorCallback')
			->addSetup('register', array('@\Nette\Application\Application'));
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
