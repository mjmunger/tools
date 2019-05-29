<?php
/**
 * {CLASS SUMMARY}
 *
 * Date: 10/15/18
 * Time: 10:28 AM
 * @author Michael Munger <mj@hph.io>
 */

namespace hphio\tools;


use League\Container\Container;

abstract class CLICommand
{
    public $container = null;

    abstract public function run(Container $container);
    abstract public function showHelp();

    public function shouldRun($command) {
        return ($this->command === $command);
    }
}