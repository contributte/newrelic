<?php

namespace VrtakCZ\NewRelic;

use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Nette\Utils\PhpGenerator\ClassType;

class Extension extends \Nette\Config\CompilerExtension
{
	public function loadConfiguration()
	{
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
		parent::afterCompile($class);

		$config = $this->getConfig();
		$initialize = $class->methods['initialize'];

		if (isset($config['appName'])) {
			$initialize->addBody(sprintf('\\%s::setupAppName(?, ?);', get_called_class()), array(
				$config['appName'], isset($config['license']) ? $config['license'] : NULL
			));
		}

		$initialize->addBody(sprintf('$newRelicLogger = new \\%s\\Logger;', __NAMESPACE__));
		$initialize->addBody('\\Nette\\Diagnostics\\Debugger::$logger = $newRelicLogger;');
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

	private function setupParameters()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		if (isset($config['parameters'])) {
			if (!is_array($config['parameters'])) {
				throw new \InvalidStateException('Invalid parameters structure');
			}
			if (count($config['parameters']) < 1) {
				return;
			}

			$parameters = $builder->addDefinition($this->prefix('parameters'))
				->setClass('VrtakCZ\Newrelic\Parameters')
				->addTag('run', true);
			foreach ($config['parameters'] as $name => $value) {
				$parameters->addSetup('setParameter', array($name, $value));
			}
		}
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
