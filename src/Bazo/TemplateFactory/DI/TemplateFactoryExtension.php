<?php

namespace Bazo\TemplateFactory\DI;

/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class TemplateFactoryExtension extends \Nette\DI\CompilerExtension
{

	/**
	 * Processes configuration data
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('factory'))
				->setFactory('Bazo\TemplateFactory\TemplateFactory');
	}


}
