<?php

namespace Bazo\TemplateFactory;

use Bazo\Linker\Linker;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Nette\Bridges\CacheLatte\CacheMacro;
use Nette\Bridges\FormsLatte\FormMacros;
use Nette\Caching\IStorage;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use SystemContainer;
use Traversable;



/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class TemplateFactory
{

	/** @var SystemContainer */
	private $container;

	/** @var IStorage */
	private $cacheStorage;

	/** @var ILatteFactory */
	private $latteFactory;

	/** @var Request */
	private $httpRequest;

	/** @var Response */
	private $httpResponse;

	/** @var User */
	private $user;

	/** @var ITranslator */
	private $translator;

	/** @var Linker */
	private $linker;


	public function __construct(Container $container, IStorage $cacheStorage, /* ILatteFactory $latteFactory, */ Request $httpRequest, Response $httpResponse, User $user, Linker $linker, ITranslator $translator = NULL)
	{
		$this->container = $container;
		$this->cacheStorage = $cacheStorage;
		//$this->latteFactory = $latteFactory;
		$this->latteFactory = $container->createServiceNette__latteFactory();
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->user = $user;
		$this->linker = $linker;
		$this->translator = $translator;
	}


	public function createTemplate($class = NULL)
	{
		$latte = $this->latteFactory->create();
		$template = $class ? new $class($latte) : new Template($latte);

		$template->getLatte()->addFilter(NULL, 'Nette\\Templating\\Helpers::loader');

		if ($latte->onCompile instanceof Traversable) {
			$latte->onCompile = iterator_to_array($latte->onCompile);
		}

		array_unshift($latte->onCompile, function($latte) {
			$latte->getParser()->shortNoEscape = TRUE;
			$latte->getCompiler()->addMacro('cache', new CacheMacro($latte->getCompiler()));
			UIMacros::install($latte->getCompiler());
			FormMacros::install($latte->getCompiler());
		});

		$latte->addFilter('url', 'rawurlencode'); // back compatiblity
		foreach (array('normalize', 'toAscii', 'webalize', 'padLeft', 'padRight', 'reverse') as $name) {
			$latte->addFilter($name, 'Nette\Utils\Strings::' . $name);
		}

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
