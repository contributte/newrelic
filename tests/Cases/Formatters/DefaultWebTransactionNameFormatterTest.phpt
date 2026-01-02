<?php declare(strict_types = 1);

namespace Tests\Cases\Formatters;

use Contributte\NewRelic\Environment;
use Contributte\NewRelic\Formatters\DefaultWebTransactionNameFormatter;
use Contributte\Tester\Toolkit;
use Mockery;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$environment = Mockery::mock(Environment::class);
	$request = new Request('MyPresenter', 'GET', [
		Presenter::ActionKey => 'myAction',
	]);

	$formatter = new DefaultWebTransactionNameFormatter($environment);

	Assert::same('MyPresenter:myAction', $formatter->format($request));

	Mockery::close();
});

Toolkit::test(function (): void {
	$environment = Mockery::mock(Environment::class);
	$request = new Request('MyPresenter', 'GET');

	$formatter = new DefaultWebTransactionNameFormatter($environment);

	Assert::same('MyPresenter:' . Presenter::DefaultAction, $formatter->format($request));

	Mockery::close();
});

Toolkit::test(function (): void {
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

	Mockery::close();
});
