<?php
/**
 * {CLASS SUMMARY}
 *
 * Date: 10/13/18
 * Time: 4:41 PM
 * @author Michael Munger <mj@hph.io>
 */

namespace hphio\tools;


use League\Container\Container;
use \PDO;
use \PDOStatement;

class Abstractor
{

    protected $pdo               = null;
    public    $primaryKey        = null;
    public    $timestamps        = [];
    public    $targetTable       = null;
    public    $updateTimestamps  = [];
    public    $namespace         = '';
    public    $filename          = null;
    public    $classname         = null;

    /**
     * Abstractor constructor.
     * @param $pdo
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->pdo = $container->get("pdo");
        $this->hash = sha1(microtime());
    }

    public function databaseError($stmt) {
        throw new \Exception(sprintf("Database error (%s) %s", $stmt->errorCode(), $stmt->errorInfo()[2]) );
    }

    public function prepareExecute($sql, $values) {
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($values);

        if($stmt->errorCode() !== '00000') $this->databaseError($stmt);

        return $stmt;
    }

    public function setTable($table) {
        $this->targetTable = $table;
    }

    private function getTableDescription() {
        $sql = "DESCRIBE " . $this->targetTable;
        $stmt = $this->prepareExecute($sql , []);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    public function getPrimaryKey() {
        $data = $this->getTableDescription();

        foreach($data as $row) {
            if($row['Key'] == 'PRI') {
                $this->primaryKey = $row['Field'];
                return $this->primaryKey;
            }
        }

        return false;
    }

    public function getTimestamps() {
        $data = $this->getTableDescription();
        foreach($data as $row) {
            if($row['Default'] == 'CURRENT_TIMESTAMP') $this->timestamps[] = $row['Field'];
        }
    }

    public function getOnUpdateTimestamps() {
        $data = $this->getTableDescription();
        foreach($data as $row) {
            if($row['Type']  !== 'datetime') continue;
            if($row['Extra'] === 'on update CURRENT_TIMESTAMP') $this->updateTimestamps[] = $row['Field'];
        }
    }
    public function abstractTable($table) {
        $sql = "";
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setClassName($classname) {
        $classname = ucfirst($classname);
        $this->classname = $classname;
        $this->filename = "$classname.php";
    }

    public function getOpeningMarker() {
        $format = '    /* <generated_%s> */';
        return sprintf($format, $this->hash);
    }

    public function getClosingMarker() {
        $format = '    /* </generated_%s> */';
        return sprintf($format, $this->hash);
    }

    public function getBody() {
        $body = [];

        return $body;
    }
}