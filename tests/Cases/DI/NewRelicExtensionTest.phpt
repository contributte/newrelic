<?php

declare(strict_types = 1);

namespace ContributteTests\NewRelic\Cases\DI;

use Contributte\NewRelic\Agent\ProductionAgent;
use Contributte\NewRelic\Callbacks\OnErrorCallback;
use Contributte\NewRelic\Callbacks\OnRequestCallback;
use Contributte\NewRelic\DI\NewRelicConsoleExtension;
use Contributte\NewRelic\DI\NewRelicExtension;
use Contributte\NewRelic\RUM\FooterControl;
use Contributte\NewRelic\RUM\HeaderControl;
use Contributte\NewRelic\RUM\User;
use Contributte\NewRelic\Tracy\Logger;
use ContributteTests\NewRelic\Mocks\ApplicationExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\TestCase;
use Tracy\Bridges\Nette\TracyExtension;
use Tracy\Debugger;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @TestCase
 */
final class NewRelicExtensionTest extends TestCase
{

	public function testExtension(): void
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addConfig([
				'newrelic' => [
					'enabled' => true,
					'appName' => 'YourApplicationName',
					'license' => 'yourLicenseCode',
					'actionKey' => 'action',
					'logLevel' => [
						'critical',
						'exception',
						'error',
					],
					'rum' => [
						'enabled' => 'true',
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
					'custom' => [
						'parameters' => [
							'paramName' => 'paramValue',
						],
						'tracers' => [],
					],
				],
			]);
			$compiler->addExtension('application', new ApplicationExtension);
			$compiler->addExtension('tracy', new TracyExtension);
			$compiler->addExtension('newrelic', new NewRelicExtension);
			$compiler->addExtension('newrelic.console', new NewRelicConsoleExtension);
		}, [getmypid(), 1]);

		/** @var Container $container */
		$container = new $class();

		Assert::type(ProductionAgent::class, $container->getService('newrelic.agent'));
		Assert::type(Logger::class, $container->getService('newrelic.logger'));
		Assert::type(OnRequestCallback::class, $container->getService('newrelic.onRequestCallback'));
		Assert::type(OnErrorCallback::class, $container->getService('newrelic.onErrorCallback'));
		Assert::type(User::class, $container->getService('newrelic.rum.user'));
		Assert::type(HeaderControl::class, $container->getService('newrelic.rum.headerControl'));
		Assert::type(FooterControl::class, $container->getService('newrelic.rum.footerControl'));
	}

}

(new NewRelicExtensionTest())->run();
