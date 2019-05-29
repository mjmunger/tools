<?php
/**
 * {CLASS SUMMARY}
 *
 * Date: 10/15/18
 * Time: 10:28 AM
 * @author Michael Munger <mj@hph.io>
 */

namespace hphio\tools;


interface CommandInterface
{
    public function shouldRun() : boolean;
}