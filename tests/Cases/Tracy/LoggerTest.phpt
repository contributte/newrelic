<?php

declare(strict_types=1);

namespace ContributteTests\NewRelic\Cases\Tracy;

use Contributte\NewRelic\Agent\Agent;
use Contributte\NewRelic\Tracy\Logger;
use ContributteTests\NewRelic\Libs\TestCase;
use Mockery;
use Tester\Assert;
use Tracy\ILogger;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @TestCase
 */
final class LoggerTest extends TestCase
{

	public function testLogDefaultLevels(): void
	{
		$agent = Mockery::mock(Agent::class)
			->shouldReceive('noticeError')
			->with('My error')
			->times(3)
			->getMock();

		$netteLogger = Mockery::mock(ILogger::class)
			->shouldReceive('log')
			->with('My error', ILogger::CRITICAL)
			->shouldReceive('log')
			->with('My error', ILogger::EXCEPTION)
			->shouldReceive('log')
			->with('My error', ILogger::ERROR)
			->getMock();

		$logger = new Logger($agent, $netteLogger, [
			ILogger::CRITICAL,
			ILogger::EXCEPTION,
			ILogger::ERROR,
		]);

		$logger->log('My error', ILogger::CRITICAL);
		$logger->log('My error', ILogger::EXCEPTION);
		$logger->log('My error', ILogger::ERROR);

		Assert::true(true);
	}

	public function testLogDefaultCritical(): void
	{
		$agent = Mockery::mock(Agent::class)
			->shouldReceive('noticeError')
			->with('My error')
			->times(1)
			->getMock();

		$netteLogger = Mockery::mock(ILogger::class)
			->shouldReceive('log')
			->with('My error', ILogger::CRITICAL)
			->shouldReceive('log')
			->with('My error', ILogger::EXCEPTION)
			->shouldReceive('log')
			->with('My error', ILogger::ERROR)
			->getMock();

		$logger = new Logger($agent, $netteLogger, [
			ILogger::CRITICAL,
		]);

		$logger->log('My error', ILogger::CRITICAL);
		$logger->log('My error', ILogger::EXCEPTION);
		$logger->log('My error', ILogger::ERROR);

		Assert::true(true);
	}

}

(new LoggerTest())->run();
