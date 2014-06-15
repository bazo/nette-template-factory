<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(TRUE);

$logDir = __DIR__ . '/log';
$tempDir = __DIR__ . '/temp';

$fs = new Nette\Utils\FileSystem;
$fs->createDir($logDir);
$fs->createDir($tempDir);

$configurator->enableDebugger($logDir);
$configurator->setTempDirectory($tempDir);

$configurator->addConfig(__DIR__ . '/config.neon');

$container = $configurator->createContainer();

$templateFactory = $container->getByType(\Bazo\TemplateFactory\TemplateFactory::class);
$template = $templateFactory->createTemplate();

$template->setFile(__DIR__.'/test.latte');

$template->render();
