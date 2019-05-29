<?php

use \League\Container\Container;
use hphio\tools\Runner;
use hphio\tools\AbstractorCommand;

include('bootstrap/bootstrap.php');

/**
 * Runs all the tools!
 *
 * Date: 10/15/18
 * Time: 7:11 AM
 * @author Michael Munger <mj@hph.io>
 */


$container = new Container();
$container->add(AbstractorCommand::class)->addArgument($container);
$runner= Runner::fromGlobals($_SERVER);
$runner->loadDatabaseCredentials($runner->pwd . '/phpunit.xml');
$container->add('pdo', $runner->getConnection());
$container->add('runner', $runner);
$runner->setContainer($container);
$runner->loadCommands($container);
$runner->run($container);