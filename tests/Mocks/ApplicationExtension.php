<?php

declare(strict_types=1);

namespace ContributteTests\NewRelic\Mocks;

use Nette\DI\CompilerExtension;

final class ApplicationExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition('application')
			->setFactory(Application::class);

		$this->compiler->addExportedType(Application::class);
	}

}
