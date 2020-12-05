<?php

declare(strict_types=1);

namespace Contributte\NewRelic;

class Helpers
{

	public static function getConsoleCommand(): string
	{
		return trim('$ ' . basename($_SERVER['argv'][0]) . ' ' . implode(' ', array_slice($_SERVER['argv'], 1)));
	}

}
