<?php

declare(strict_types = 1);

namespace ContributteTests\NewRelic\Mocks;

final class Application
{
	public $onRequest = [];
	public $onError = [];
}
