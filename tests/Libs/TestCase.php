<?php declare(strict_types=1);

namespace ContributteTests\NewRelic\Libs;

use Mockery;

abstract class TestCase extends \Tester\TestCase
{
	protected function tearDown(): void
	{
		Mockery::close();
	}
}
