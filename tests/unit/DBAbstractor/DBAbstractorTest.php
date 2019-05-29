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
use hphio\tools\AbstractorCommand;
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
        $Abstractor = new AbstractorCommand($this->getContainer());
        $Abstractor->setTable($table);
        $this->assertSame($table, $Abstractor->targetTable);
    }

    /**
     * @covers \hphio\tools\AbstractorCommand::getPrimaryKey
     */

    public function testGetPrimaryKey() {
        $table = 'employees';
        $Abstractor = new AbstractorCommand($this->getContainer());
        $Abstractor->setTable($table);

        $primaryKey = "emp_id";

        $Abstractor->getPrimaryKey();
        $this->assertSame($primaryKey, $Abstractor->primaryKey);
    }

    public function testGetTimestamps() {
        $table = 'employees';
        $Abstractor = new AbstractorCommand($this->getContainer());
        $Abstractor->setTable($table);
        $Abstractor->getTimestamps();

        $this->assertCount(2,$Abstractor->timestamps);

        $this->assertTrue(in_array('last_updated', $Abstractor->timestamps));
        $this->assertTrue(in_array('date_created', $Abstractor->timestamps));
    }

    public function testGetOnUpdateTimestamps() {
        $table = 'employees';
        $Abstractor = new AbstractorCommand($this->getContainer());
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

        $Abstractor = new AbstractorCommand($this->getContainer());

        if($throwsException) $this->expectException('Exception');

        $this->assertSame($expectedReturn, $Abstractor->databaseError($stmt));

    }

    public function providerTestDatabaseError() {

        return  [ [ 'HY123', '99999' , 'Error'    , true  , null ]
                ];

    }

    public function testPrepareExecute() {
        $Abstractor = new AbstractorCommand($this->getContainer());

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
        $Abstractor = new AbstractorCommand($this->getContainer());
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
        $Abstractor = new AbstractorCommand($this->getContainer());
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
        $Abstractor = new AbstractorCommand($this->getContainer());
        $pattern = '/[0-9a-z]{40}/m';
        $this->assertSame(1, preg_match($pattern, $Abstractor->hash));

        $openingPattern = '% {4}\/\* <generated_[0-9a-f]{40}> \*/%m';
        $closingPattern = '% {4}\/\* </generated_[0-9a-f]{40}> \*/%m';

        $this->assertSame(1,preg_match($openingPattern, $Abstractor->getOpeningMarker()));
        $this->assertSame(1,preg_match($closingPattern, $Abstractor->getClosingMarker()));
    }

    public function testSetup() {
        $Abstractor = new AbstractorCommand($this->getContainer());
        $table = 'employees';
        $classname = 'ExpectedEmployeesClass';
        $namespace = 'Test\Employee';

        $Abstractor->setup($table, $classname, $namespace);

        $this->assertSame($table, $Abstractor->targetTable);
        $this->assertSame($classname, $Abstractor->classname);
        $this->assertSame($namespace, $Abstractor->namespace);
        $this->assertCount(0, array_diff($Abstractor->timestamps,['last_updated', 'date_created']));
        $this->assertSame('emp_id', $Abstractor->primaryKey);
        $this->assertCount(0, array_diff($Abstractor->updateTimestamps, ['last_updated']));
        $this->assertCount(0, array_diff($Abstractor->fields, [ "emp_id", "birth_date", "first_name", "last_name", "gender", "hire_date", "last_updated", "date_created" ]));

    }
    /**
     * @param $expectedFileArray
     * @dataProvider providerTestClassGeneration
     */

    public function testClassGeneration($expectedFileArray) {

        $table = 'employees';
        $openingMarker = '% {4}\/\* <generated_[0-9a-f]{40}> \*/%m';
        $closingMarker = '% {4}\/\* </generated_[0-9a-f]{40}> \*/%m';
        $foundOpeningMarker = false;
        $foundClosingMarker = false;

        $Abstractor = new AbstractorCommand($this->getContainer());
        $Abstractor->setup('employees', 'ExpectedEmployeesClass', 'Test\Employee');

        $body = $Abstractor->getBody();

        for($line = 0; $line < count($expectedFileArray); $line++ ) {
            $expectedLine = rtrim($expectedFileArray[$line]);

            //Do this so that it fails and tells me which line needs work.
            if(isset($body[$line]) === false) array_push($body,'');
            $message = $this->lineDiff($expectedLine, $body[$line], $line);

            //Can't check opening or closing markers because they have random hashes in them.
            if(preg_match($openingMarker, $body[$line]) === 1) {
                $foundOpeningMarker = true;
                continue;
            }

            if(preg_match($closingMarker, $body[$line]) === 1) {
                $foundClosingMarker = true;
                continue;
            }

            $this->assertSame($expectedLine, $body[$line], $message);
        }

        $this->assertTrue($foundOpeningMarker);
        $this->assertTrue($foundClosingMarker);

    }

    private function lineDiff($expected, $actual, $line) {
        $line++;
        $expected = str_split($expected);
        $actual   = str_split($actual);

        for($x = 0; $x < count($expected); $x++ ) {
            try {
                if(strcmp($expected[$x], $actual[$x]) === 0 ) continue;
            } catch (\Exception $e) {
                echo "Something is wrong with line $line:" . PHP_EOL;
                printf("Expected (%s): '%s'" , strlen(implode($expected)), implode($expected));
                printf("Actual (%s): '%s'" , strlen(implode($actual)), implode($actual));
                echo $e->getMessage() . PHP_EOL;
                throw $e;
            }
            $format = 'Expecting "%s" (ord: %s, hex: %s) at offset %s on line %s. Got "%s" (ord: %s, hex: %s) instead.';
            return sprintf( $format,
                $expected[$x],
                ord($expected[$x]),
                dechex(ord($expected[$x])),
                $x,
                $line,
                $actual[$x],
                ord($actual[$x]),
                dechex(ord($actual[$x]))
            );
        }
        return "$line ok.";
    }

    public function providerTestClassGeneration() {
        $expectedFileArray = file('tests/unit/DBAbstractor/fixtures/expected/ExpectedEmployeesClass.php');
        return  [ [$expectedFileArray ] ];
    }

    public function testDiscoverFields() {
        $table = 'employees';
        $Abstractor = new AbstractorCommand($this->getContainer());
        $Abstractor->setTable($table);
        $Abstractor->discoverFields();

        $expectedFields = [ "emp_id", "birth_date", "first_name", "last_name", "gender", "hire_date", "last_updated", "date_created" ];

        foreach($expectedFields as $field) {
            $this->assertTrue(in_array($field, $Abstractor->fields));
        }

        for($x = 0; $x < count($expectedFields); $x++) {
            $this->assertSame($expectedFields[$x], $Abstractor->fields[$x]);
        }

        /** Technically, `last_updated` and `date_created` are both the longest.
         *  However, since `last_updated` should be discovered first, and
         *  `date_created` is not longer than `last_updated`, the "winner"
         *  is the longest field encoutered at the earliest time.
         */

        $this->assertSame('last_updated', $Abstractor->longestField);
        $this->assertSame(13, $Abstractor->fieldPaddingLength());

    }

    /**
     * @param $destinationPath
     * @dataProvider providerTestSaveFile
     */

    public function testSaveFile($destinationPath) {
        $targetPath = "$destinationPath/ExpectedEmployeesClass.php";
        if( file_exists($targetPath) ) unlink($targetPath);

        $this->assertFileNotExists($targetPath);

        $Abstractor = new AbstractorCommand($this->getContainer());
        $Abstractor->setup('employees', 'ExpectedEmployeesClass', 'Test\Employee');
        $body = $Abstractor->getBody();
        $Abstractor->saveFile($targetPath);

        $this->assertFileExists($targetPath);

    }

    public function providerTestSaveFile() {
        return  [ ['/tmp/']];
    }
}