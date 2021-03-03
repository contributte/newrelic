<?php

declare(strict_types=1);

namespace ContributteTests\NewRelic\Libs;

use Mockery;
use Tester\TestCase as TesterTestCase;

abstract class TestCase extends TesterTestCase
{

	protected function tearDown(): void
	{
		Mockery::close();
	}

}
