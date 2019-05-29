<?php
/**
 * {CLASS SUMMARY}
 *
 * Date: 10/15/18
 * Time: 12:33 PM
 * @author Michael Munger <mj@hph.io>
 */

namespace hphio\tools;


class CommandNotFound extends CLICommand
{

    public function run($container)
    {
        print ("Command not found!" . PHP_EOL);
    }

    public function showHelp()
    {
        // TODO: Implement showHelp() method.
    }
}