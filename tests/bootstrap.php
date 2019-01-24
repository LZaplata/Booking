<?php

require __DIR__ . "/../vendor/autoload.php";

$configurator = new Nette\Configurator;

$configurator->setDebugMode(true); // enable for your remote IP
$configurator->enableTracy(__DIR__ . "/../log");

$configurator->setTimeZone("Europe/Prague");
$configurator->setTempDirectory(__DIR__ . "/../temp");

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->addDirectory(__DIR__ . "/../src")
    ->register();

$configurator->addConfig(__DIR__ . "/config.neon");

$container = $configurator->createContainer();

return $container;