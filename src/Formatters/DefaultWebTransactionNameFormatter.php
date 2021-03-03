<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Formatters;

use Nette\Application\Request;
use Nette\Application\UI\Presenter;

final class DefaultWebTransactionNameFormatter implements WebTransactionNameFormatter
{

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
		return trim('$ ' . basename($_SERVER['argv'][0]) . ' ' . implode(' ', array_slice($_SERVER['argv'], 1)));
	}

}
