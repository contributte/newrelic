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
		$name = $request->getPresenterName();
		$params = $request->getParameters();

		if (isset($params[Presenter::ACTION_KEY])) {
			$name = sprintf('%s:%s', $name, $params[Presenter::ACTION_KEY]);
		}

		return $name;
	}

	public function formatArgv(): string
	{
		$argv = $this->environment->getArgv();

		return trim(basename($argv[0]) . ' ' . implode(' ', array_slice($argv, 1)));
	}

}
