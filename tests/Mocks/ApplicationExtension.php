<?php

declare(strict_types=1);

namespace ContributteTests\NewRelic\Mocks;

use Nette;

final class ApplicationExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$builder->addDefinition('application')
			->setFactory(Application::class);

		$this->compiler->addExportedType(Application::class);
	}

}
