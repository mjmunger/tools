<?php
/**
 * {CLASS SUMMARY}
 *
 * Date: 10/15/18
 * Time: 9:28 AM
 * @author Michael Munger <mj@hph.io>
 */

namespace hphio\tools;

use \Iterator;
use \Countable;

class CommandList implements Countable, Iterator
{
    private $position     = 0;
    private $commands     = [];

    public function __construct() {
        $this->rewind();
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->commands[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
    }

    public function previous() {
        $this->position--;
    }

    public function valid() {
        return isset($this->commands[$this->position]);
    }

    public function add(AbstractorCommand $command) {
        array_push($this->commands, $command);
    }

    public function count() {
        return count($this->commands);
    }
}
