<?php declare(strict_types = 1);

namespace Martinsvacinka\Ecomail\DI;

use Nette\DI\CompilerExtension;
use Ecomail\Ecomail;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class EcomailExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'key' => Expect::string()->required(),
		]);
	}

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		$config = (array) $this->getConfig();
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('service'))
			->setFactory(Ecomail::class, [$config['key']]);
	}
}
