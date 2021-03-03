<?php

declare(strict_types = 1);

namespace ContributteTests\NewRelic\Cases\Callbacks;

use Contributte\NewRelic\Agent\Agent;
use Contributte\NewRelic\Callbacks\OnRequestCallback;
use Contributte\NewRelic\Environment;
use Contributte\NewRelic\Formatters\WebTransactionNameFormatter;
use ContributteTests\NewRelic\Libs\TestCase;
use Mockery;
use Nette\Application\Application;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @TestCase
 */
final class OnRequestCallbackTest extends TestCase
{

	public function testCli(): void
	{
		$agent = Mockery::mock(Agent::class)
			->shouldReceive('backgroundJob')
			->shouldReceive('nameTransaction')
				->with('TransactionName')
			->shouldReceive('disableAutorum')
			->getMock();

		$environment = Mockery::mock(Environment::class)
			->shouldReceive('isCli')
			->andReturn(true)
			->getMock();

		$formatter = Mockery::mock(WebTransactionNameFormatter::class)
			->shouldReceive('formatArgv')
			->andReturn('TransactionName')
			->getMock();

		$application = Mockery::mock(Application::class);
		$request = new Request('MyPresenter', 'GET', [Presenter::ACTION_KEY => 'default']);

		$callback = new OnRequestCallback($agent, $environment, $formatter);
		$callback->__invoke($application, $request);

		Assert::true(true);
	}

	public function testWeb(): void
	{
		$agent = Mockery::mock(Agent::class)
			->shouldReceive('nameTransaction')
			->with('MyPresenter:default')
			->shouldReceive('disableAutorum')
			->getMock();

		$environment = Mockery::mock(Environment::class)
			->shouldReceive('isCli')
			->andReturn(false)
			->getMock();

		$formatter = Mockery::mock(WebTransactionNameFormatter::class)
			->shouldReceive('format')
			->andReturn('MyPresenter:default')
			->getMock();

		$application = Mockery::mock(Application::class);
		$request = new Request('MyPresenter', 'GET', [Presenter::ACTION_KEY => 'default']);

		$callback = new OnRequestCallback($agent, $environment, $formatter);
		$callback->__invoke($application, $request);

		Assert::true(true);
	}

}

(new OnRequestCallbackTest())->run();
