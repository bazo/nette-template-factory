<?php

namespace Bazo\TemplateFactory;

use Nette\Caching\IStorage;
use Nette\Security\User;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Templating\FileTemplate;
use Nette\Localization\ITranslator;
use Bazo\Linker\Linker;
use Nette\DI\Container;


/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class TemplateFactory
{

	/** @var IStorage */
	private $cacheStorage;

	/** @var IStorage */
	private $netteCacheStorage;

	/** @var Request */
	private $httpRequest;

	/** @var Response */
	private $httpResponse;

	/** @var \SystemContainer */
	private $container;

	/** @var User */
	private $user;

	/** @var ITranslator */
	private $translator;

	/** @var Linker */
	private $linker;



	public function __construct(Container $container, IStorage $cacheStorage, Request $httpRequest, Response $httpResponse, User $user, Linker $linker, ITranslator $translator = NULL)
	{
		$this->cacheStorage = $cacheStorage;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->container = $container;
		$this->netteCacheStorage = $container->getService('nette.templateCacheStorage');
		$this->user = $user;
		$this->linker = $linker;
		$this->translator = $translator;
	}


	public function createTemplate($class = NULL)
	{
		$template = $class ? new $class : new FileTemplate;
		$template->registerFilter($this->container->createServiceNette__latte());
		$template->registerHelperLoader('Nette\\Templating\\Helpers::loader');

		$template->setCacheStorage($this->netteCacheStorage);
		$template->user = $this->user;
		$template->netteHttpResponse = $this->httpResponse;
		$template->netteCacheStorage = $this->httpRequest;
		$template->baseUri = $template->baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);

		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		$template->setTranslator($this->translator);
		$template->_control = $this->linker;
		return $template;
	}


}
