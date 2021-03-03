<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Formatters;

use Symfony\Component\Console\Command\Command;

class DefaultCliTransactionNameFormatter implements CliTransactionNameFormatter
{

	public function format(Command $command): string
	{
		return $command->getName();
	}

}
