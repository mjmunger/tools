<?php

namespace hphio\tools;


/**
 * {CLASS SUMMARY}
 *
 * Date: 10/15/18
 * Time: 7:39 AM
 * @author Michael Munger <mj@hph.io>
 */

use Exception;
use League\Container\Container;
use \DOMDocument;
use PDO;
use PDOException;

class Runner
{
    public $argc        =   [];
    public $argv        =   [];
    public $requestTime = null;
    public $pwd         = null;
    public $username    = null;
    public $db          = null;
    public $container   = null;
    public $commands    = null;

    public static function fromGlobals($server) {

        $runner = new Runner();
        $runner->argc        = $server['argc'];
        $runner->argv        = $server['argv'];
        $runner->requestTime = $server['REQUEST_TIME'];
        $runner->pwd         = $server['PWD'];
        $runner->username    = $server['USERNAME'];

        return $runner;
    }

    public function setContainer(Container $container) {
        $this->container   = $container;
    }

    public function showHelp() {
        ?>

Usage ht [command] [options] [arguments]

Available commands
abstract          Abstracts a database table into an extendable class.

<?php
    }

    /**
     * @param $filepath - the path to phpunit.xml
     * @return bool|mixed|null
     */

    public function loadDatabaseCredentials($filepath) {
        if(file_exists($filepath) === false) return false;
        $values = [];
        $values['DB_DSN']    = null;
        $values['DB_USER']   = null;
        $values['DB_PASSWD'] = null;
        $values['DB_DBNAME'] = null;

        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($filepath));
        $php = $dom->getElementsByTagName('php')[0];
        if(is_null($php)) return false;

        $vars = $php->getElementsByTagName('var');

        foreach($vars as $element) {
            $attributes = $element->attributes;
            $name = $attributes->getNamedItem('name')->value;
            $value = $attributes->getNamedItem('value')->value;
            if( in_array($name,array_keys($values)) === false ) continue;
            $values[$name] = $value;
        }

        $allValuesPresent = true;
        foreach($values as $value) {
            $allValuesPresent = ($allValuesPresent && (is_null($value) === false));
        }

        if($allValuesPresent === false) return false;

        $final = [];
        $final['dsn']      = $values['DB_DSN'];
        $final['username'] = $values['DB_USER'];
        $final['password'] = $values['DB_PASSWD'];
        $final['database'] = $values['DB_DBNAME'];

        $this->db = json_decode(json_encode($final));
        return $this->db;
    }

    public function getConnection() {
        if(is_null($this->db)) return false;

        $dsn    = sprintf('mysql:dbname=%s;host=localhost', $this->db->database);

        try {
            $dbh = new PDO($this->db->dsn, $this->db->username, $this->db->password);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage() );
        }

        return $dbh;
    }

    public function getCommand() {
        if(isset($this->argv[1]) === false ) return false;
        return $this->argv[1];
    }

    public function getCommandArgs() {
        $args = $this->argv;
        //Remove index.php
        array_shift($args);
        //Remove teh command.
        array_shift($args);

        //Return everything else:
        return $args;
    }

    /**
     * @param $container
     */
    public function loadCommands($container) {
        $this->commands[] = $container->get(AbstractorCommand::class);
    }

    public function run($container) {
        foreach($this->commands as $command) {
            if($command->shouldRun($this->getCommand()) === false ) continue;
            return $command->run($container);
        }
        echo PHP_EOL . "Command not found." . PHP_EOL;
        $this->showHelp();
    }
}