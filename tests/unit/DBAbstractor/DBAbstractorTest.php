<?php
/**
 * {CLASS SUMMARY}
 *
 * Date: 10/13/18
 * Time: 3:58 PM
 * @author Michael Munger <mj@hph.io>
 */

namespace hphio\tools;


use Exception;
use hphio\tools\Abstractor;
use League\Container\Container;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;
use \PDO;
use \PDOStatement;

class DBAbstractorTest extends TestCase
{
    use TestCaseTrait;

    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    private $container = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {

            if (self::$pdo == null) {

                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }

    private function getContainer() {
        $container = new Container();
        $container->add("pdo", self::$pdo);
        return $container;
    }

    public function getDataSet()
    {
        return $this->createMySQLXMLDataSet('tests/unit/DBAbstractor/fixtures/db/utildb.xml');
    }

    public function testSetTable() {
        $table = 'employees';
        $Abstractor = new Abstractor($this->getContainer());
        $Abstractor->setTable($table);
        $this->assertSame($table, $Abstractor->targetTable);
    }

    /**
     *
     */

    public function testGetPrimaryKey() {
        $table = 'employees';
        $Abstractor = new Abstractor($this->getContainer());
        $Abstractor->setTable($table);

        $primaryKey = "emp_id";

        $Abstractor->getPrimaryKey();
        $this->assertSame($primaryKey, $Abstractor->primaryKey);
    }

    public function testGetTimestamps() {
        $table = 'employees';
        $Abstractor = new Abstractor($this->getContainer());
        $Abstractor->setTable($table);
        $Abstractor->getTimestamps();

        $this->assertCount(2,$Abstractor->timestamps);

        $this->assertTrue(in_array('last_updated', $Abstractor->timestamps));
        $this->assertTrue(in_array('date_created', $Abstractor->timestamps));
    }

    public function testGetOnUpdateTimestamps() {
        $table = 'employees';
        $Abstractor = new Abstractor($this->getContainer());
        $Abstractor->setTable($table);
        $Abstractor->getOnUpdateTimestamps();

        $this->assertCount(1,$Abstractor->updateTimestamps);
        $this->assertSame("last_updated", array_shift($Abstractor->updateTimestamps));
    }

    /**
     * @param $stmt
     * @param $expectedResult
     * @throws Exception
     * @dataProvider providerTestDatabaseError
     */

    public function testDatabaseError($driverState, $returnCode, $returnMessage, $throwsException, $expectedReturn) {

        $container = $this->getContainer();

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('errorCode')->willReturn($returnCode);
        $stmt->method('errorInfo')->willReturn([ $driverState, $returnCode, $returnMessage ]);

        $Abstractor = new Abstractor($this->getContainer());

        if($throwsException) $this->expectException('Exception');

        $this->assertSame($expectedReturn, $Abstractor->databaseError($stmt));

    }

    public function providerTestDatabaseError() {

        return  [ [ 'HY123', '99999' , 'Error'    , true  , null ]
                ];

    }

    public function testPrepareExecute() {
        $Abstractor = new Abstractor($this->getContainer());

        $sql = "SELECT SUM(?+?) AS TOTAL";
        $values = [7,3];
        $stmt = $Abstractor->prepareExecute($sql, $values);
        $this->assertInstanceOf(PDOStatement::class, $stmt);
        $row = $stmt->fetchObject();
        $this->assertSame("10", $row->TOTAL);

        $sql = "SEECT SUM(?+?) AS TOTAL";
        $values = [7,3];

        $this->expectException("Exception");
        $stmt = $Abstractor->prepareExecute($sql, $values);
    }

    public function testSetNamespace() {
        $Abstractor = new Abstractor($this->getContainer());
        $namespace = 'Test\Namespace';
        $Abstractor->setNamespace($namespace);
        $this->assertSame($namespace, $Abstractor->namespace);
    }

    /**
     * @param $classname
     * @param $expectedClassname
     * @param $expectedFilename
     * @dataProvider providerTestSetClassName
     */
    public function testSetClassName($classname, $expectedClassname, $expectedFilename) {
        $Abstractor = new Abstractor($this->getContainer());
        $Abstractor->setClassName($classname);
        $this->assertSame( $expectedClassname, $Abstractor->classname);
        $this->assertSame( $expectedFilename,  $Abstractor->filename );
    }

    public function providerTestSetClassName() {
        return  [ [ 'foo' , 'Foo' , 'Foo.php']
                , [ 'Bar' , 'Bar' , 'Bar.php']
                ];
    }

    public function testHashMarkers() {
        $Abstractor = new Abstractor($this->getContainer());
        $pattern = '/[0-9a-z]{40}/m';
        $this->assertSame(1, preg_match($pattern, $Abstractor->hash));

        $openingPattern = '% {4}\/\* <generated_[0-9a-f]{40}> \*/%m';
        $closingPattern = '% {4}\/\* </generated_[0-9a-f]{40}> \*/%m';

        $this->assertSame(1,preg_match($openingPattern, $Abstractor->getOpeningMarker()));
        $this->assertSame(1,preg_match($closingPattern, $Abstractor->getClosingMarker()));
    }

    /**
     * @param $expectedFileArray
     * @dataProvider providerTestClassGeneration
     */

/*    public function testClassGeneration($expectedFileArray) {

        $table = 'employees';

        $Abstractor = new Abstractor($this->getContainer());

        $Abstractor->abstractTable($table);
        $body = $Abstractor->getBody();

        for($line = 0; $line < count($expectedFileArray); $line++ ) {
            $this->assertSame($expectedFileArray[$line], $body[$x]);
        }
    }

    public function providerTestClassGeneration() {
        $expectedFileArray = file('tests/unit/DBAbstractor/fixtures/expected/ExpectedEmployeesClass.php');
        return  [ [$expectedFileArray ] ];
    }*/
}