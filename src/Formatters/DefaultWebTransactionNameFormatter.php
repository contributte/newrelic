<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Formatters;

use Contributte\NewRelic\Environment;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;

final class DefaultWebTransactionNameFormatter implements WebTransactionNameFormatter
{

	/**
	 * @var Environment
	 */
	private $environment;

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function format(Request $request): string
	{
		$presenter = $request->getPresenterName();
		$params = $request->getParameters();
		$action = $params[Presenter::ACTION_KEY] ?? Presenter::DEFAULT_ACTION;

		return sprintf('%s:%s', $presenter, $action);
	}

	public function formatArgv(): string
	{
		$argv = $this->environment->getArgv();

		return trim(basename($argv[0]) . ' ' . implode(' ', array_slice($argv, 1)));
	}

}
