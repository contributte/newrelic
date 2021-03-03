<?php

declare(strict_types=1);

namespace ContributteTests\NewRelic\Cases\Formatters;

use Contributte\NewRelic\Environment;
use Contributte\NewRelic\Formatters\DefaultWebTransactionNameFormatter;
use ContributteTests\NewRelic\Libs\TestCase;
use Mockery;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @TestCase
 */
final class DefaultWebTransactionNameFormatterTest extends TestCase
{

	public function testFormatWithCustomAction(): void
	{
		$environment = Mockery::mock(Environment::class);
		$request = new Request('MyPresenter', 'GET', [
			Presenter::ACTION_KEY => 'myAction',
		]);

		$formatter = new DefaultWebTransactionNameFormatter($environment);

		Assert::same('MyPresenter:myAction', $formatter->format($request));
	}

	public function testFormatWithDefaultAction(): void
	{
		$environment = Mockery::mock(Environment::class);
		$request = new Request('MyPresenter', 'GET');

		$formatter = new DefaultWebTransactionNameFormatter($environment);

		Assert::same('MyPresenter:' . Presenter::DEFAULT_ACTION, $formatter->format($request));
	}

	public function testFormatArgv(): void
	{
		$environment = Mockery::mock(Environment::class)
			->shouldReceive('getArgv')
			->andReturn([
				'./consoleCommand',
				'--param1',
				'--param2',
			])
			->getMock();

		$formatter = new DefaultWebTransactionNameFormatter($environment);

		Assert::same('consoleCommand --param1 --param2', $formatter->formatArgv());
	}

}

(new DefaultWebTransactionNameFormatterTest())->run();
