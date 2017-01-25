<?php

namespace Ecomail;

use Nette\DI\CompilerExtension;


class Extension extends CompilerExtension
{

	/** @var array */
	private $defaults = array(
		'key' => NULL,
	);

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$service = $container->addDefinition($this->prefix('service'))
			->setClass('Ecomail\Ecomail', array(
				$config['key'],
			));
	}
}
