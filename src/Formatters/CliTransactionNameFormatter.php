<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Formatters;

use Symfony\Component\Console\Command\Command;

interface CliTransactionNameFormatter
{

	public function format(Command $command): string;

}
