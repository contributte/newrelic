<?php declare(strict_types = 1);

namespace Contributte\NewRelic\Formatters;

use Nette\Application\Request;

interface WebTransactionNameFormatter
{

	public function format(Request $request): string;

	public function formatArgv(): string;

}
