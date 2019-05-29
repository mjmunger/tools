<?php
/**
 * {CLASS SUMMARY}
 *
 * Date: 10/15/18
 * Time: 7:26 AM
 * @author Michael Munger <mj@hph.io>
 */

namespace hphio\tools;


use League\Container\Container;
use PHPUnit\Framework\TestCase;
use \PDO;

class RunnerTest extends TestCase
{
    /**
     * @param $server
     * @dataProvider providerTestFromGlobals
     */

    public function testFromGlobals($server) {
        $runner = Runner::fromGlobals($server);

        $this->assertSame($server['argc']         , $runner->argc        );
        $this->assertSame($server['argv']         , $runner->argv        );
        $this->assertSame($server['REQUEST_TIME'] , $runner->requestTime );
        $this->assertSame($server['PWD']          , $runner->pwd         );
        $this->assertSame($server['USERNAME']     , $runner->username    );

        $this->assertSame($server['argv'][1], $runner->getCommand());
    }

    /**
     * @param $filepath
     * @param $expectedResult
     * @dataProvider providerTestLoadDatabaseCredentials
     */

    public function testLoadDatabaseCredentials($filepath, $expectedResult) {
        $runner = new Runner();
        $result = $runner->loadDatabaseCredentials($filepath);

        if($expectedResult === false) {
            $this->assertFalse($result);
            return;
        }

        $this->assertSame( $expectedResult['DB_DSN']    , $runner->db->dsn      );
        $this->assertSame( $expectedResult['DB_USER']   , $runner->db->username );
        $this->assertSame( $expectedResult['DB_PASSWD'] , $runner->db->password );
        $this->assertSame( $expectedResult['DB_DBNAME'] , $runner->db->database );
    }

    public function providerTestLoadDatabaseCredentials() {

        $creds = [];
        $creds['DB_DSN']    = "mysql:dbname=utildb;host=localhost";
        $creds['DB_USER']   = "utiltester";
        $creds['DB_PASSWD'] = "5AomV7ksqXMkHjvR";
        $creds['DB_DBNAME'] = "utildb";

        return  [ [ 'tests/unit/Runner/fixtures/phpunit.xml'         , $creds ]
                , [ 'tests/unit/Runner/fixtures/phpunit-nocreds.xml' , false  ]
                , [ 'tests/unit/Runner/fixtures/doesnotexist.xml'    , false  ]
                ];
    }

    public function providerTestFromGlobals() {
        $server = [];
        $server['PWD']                                            = '/home/michael/php/tools';
        $server['argv']                                           = [ "index.php", "command", "arg1", "arg2", "arg3" ];
        $server['argc']                                           =   5;
        $server['REQUEST_TIME_FLOAT']                             =   1539603021.8844;
        $server['REQUEST_TIME']                                   =   1539603021;
        $server['USERNAME']                                       =   'michael';
        return [ [ $server ] ];

/*        $server['LS_COLORS']                                      =   'rs=0:di=01;34:ln=01;36:mh=00:pi=40;33:so=01;35:do=01;35:bd=40;33;01:cd=40;33;01:or=40;31;01:mi=00:su=37;41:sg=30;43:ca=30;41:tw=30;42:ow=34;42:st=37;44:ex=01;32:*.tar=01;31:*.tgz=01;31:*.arc=01;31:*.arj=01;31:*.taz=01;31:*.lha=01;31:*.lz4=01;31:*.lzh=01;31:*.lzma=01;31:*.tlz=01;31:*.txz=01;31:*.tzo=01;31:*.t7z=01;31:*.zip=01;31:*.z=01;31:*.Z=01;31:*.dz=01;31:*.gz=01;31:*.lrz=01;31:*.lz=01;31:*.lzo=01;31:*.xz=01;31:*.zst=01;31:*.tzst=01;31:*.bz2=01;31:*.bz=01;31:*.tbz=01;31:*.tbz2=01;31:*.tz=01;31:*.deb=01;3';
        $server['XDG_MENU_PREFIX']                                =   'gnome-';
        $server['LANG']                                           =   'en_US.UTF-8';
        $server['GDM_LANG']                                       =   'en_US.UTF-8';
        $server['DISPLAY']                                        =   ':1';
        $server['OLDPWD']                                         =   '/home/michael';
        $server['USERNAME']                                       =   'michael';
        $server['XDG_VTNR']                                       =   '2';
        $server['GIO_LAUNCHED_DESKTOP_FILE_PID']                  =   '27893';
        $server['SSH_AUTH_SOCK']                                  =   '/run/user/1000/keyring/ssh';
        $server['XDG_SESSION_ID']                                 =   '4';
        $server['USER']                                           =   'michael';
        $server['DESKTOP_SESSION']                                =   'default';
        $server['QT_QPA_PLATFORMTHEME']                           =   'qgnomeplatform';*/
/*        $server[] = 'PWD'                                            =>   '/home/michael/php/tools';
        $server['HOME']                                           =   '/home/michael';
        $server['JOURNAL_STREAM']                                 =   '8:36000';
        $server['SSH_AGENT_PID']                                  =   '2642';
        $server['QT_ACCESSIBILITY']                               =   '1';
        $server['XDG_SESSION_TYPE']                               =   'x11';
        $server['XDG_DATA_DIRS']                                  =   '/usr/share/gnome:/usr/local/share/:/usr/share/';
        $server['XDG_SESSION_DESKTOP']                            =   'default';
        $server['GJS_DEBUG_OUTPUT']                               =   'stderr';
        $server['GTK_MODULES']                                    =   'gail:atk-bridge';
        $server['WINDOWPATH']                                     =   '2';
        $server['SHELL']                                          =   '/bin/bash';
        $server['TERM']                                           =   'xterm-256color';
        $server['XDG_CURRENT_DESKTOP']                            =   'GNOME';
        $server['GPG_AGENT_INFO']                                 =   '/run/user/1000/gnupg/S.gpg-agent:0:1';
        $server['QT_LINUX_ACCESSIBILITY_ALWAYS_ON']               =   '1';
        $server['GIO_LAUNCHED_DESKTOP_FILE']                      =   '/home/michael/.local/share/applications/jetbrains-phpstorm.desktop';
        $server['SHLVL']                                          =   '1';
        $server['XDG_SEAT']                                       =   'seat0';
        $server['GDMSESSION']                                     =   'default';
        $server['GNOME_DESKTOP_SESSION_ID']                       =   'this-is-deprecated';
        $server['LOGNAME']                                        =   'michael';
        $server['DBUS_SESSION_BUS_ADDRESS']                       =   'unix:path=/run/user/1000/bus';
        $server['XDG_RUNTIME_DIR']                                =   '/run/user/1000';
        $server['XAUTHORITY']                                     =   '/run/user/1000/gdm/Xauthority';
        $server['PATH']                                           =   '/usr/local/bin:/usr/bin:/bin:/usr/local/games:/usr/games:/home/michael/opt/PhpStorm-182.4505.42/bin/';
        $server['GJS_DEBUG_TOPICS']                               =   'JS ERROR;JS LOG';
        $server['SESSION_MANAGER']                                =   'local/d9:@/tmp/.ICE-unix/2583,unix/d9:/tmp/.ICE-unix/2583';
        $server['BASH_FUNC_generate_command_executed_sequence%%'] =   '() {  printf "\e\7\n"}';
        $server['_']                                              =   '/usr/bin/php';
        $server['PHP_SELF']                                       =   'index.php';
        $server['SCRIPT_NAME']                                    =   'index.php';
        $server['SCRIPT_FILENAME']                                =   'index.php';
        $server['PATH_TRANSLATED']                                =   'index.php';
        $server['DOCUMENT_ROOT']                                  =   '';
        $server['REQUEST_TIME_FLOAT']                             =   1539603021.8844;
        $server['REQUEST_TIME']                                   =   1539603021;
*/

        return $server;
    }

    public function testCommandList() {
        $command1 = $this->createMock(AbstractorCommand::class);
        $command2 = $this->createMock(AbstractorCommand::class);

        $list = new CommandList();
        $list->add($command1);
        $this->assertSame(1, $list->count());
        $this->assertSame($command1, $list->current());
        $this->assertInstanceOf(AbstractorCommand::class, $list->current());
        $this->assertTrue($list->valid());
        $this->assertSame(0,$list->key());
        $list->add($command2);
        $list->next();
        $this->assertSame(1,$list->key());
        $list->previous();
        $this->assertSame(0,$list->key());
    }

    /**
     * @param $server
     * @dataProvider providerTestShouldRun
     */

    public function testShouldRun($server, $argv, $expected, $expectedCommand) {
        $pdo = $this->createMock(PDO::class);
        //$pdo->method('__construct')->willReturn(true);
        $server['argv'] = $argv;

        $runner = Runner::fromGlobals($server);
        $this->assertSame($expectedCommand, $runner->getCommand());

        $container = new Container();
        $container->add('runner', $runner);
        $container->add('pdo', $pdo);

        $command = new AbstractorCommand($container);
        $this->assertSame($expected, $command->shouldRun($runner->getCommand()));

    }

    public function providerTestShouldRun() {
        $server = [];
        $server['PWD']                                            = '/home/michael/php/tools';
        $server['argc']                                           =   5;
        $server['REQUEST_TIME_FLOAT']                             =   1539603021.8844;
        $server['REQUEST_TIME']                                   =   1539603021;
        $server['USERNAME']                                       =   'michael';

        return  [ [ $server, [ "index.php", "abstract", "arg1", "arg2", "arg3" ] , true  , "abstract"]
                , [ $server, [ "index.php", "abstract" ]                         , true  , "abstract"]
                , [ $server, [ "index.php", "commandx" ]                         , false , "commandx"]
                , [ $server, [ "index.php" ]                                     , false , false     ]
                ];
    }

    /**
     * @param $server
     * @param $argv
     * @param $expectedShouldRun
     * @param $expectedArgs
     * @dataProvider providerGetCommandArgs
     */

    public function testGetCommandArgs($server, $argv, $expectedShouldRun, $expectedArgs) {
        $pdo = $this->createMock(PDO::class);
        //$pdo->method('__construct')->willReturn(true);
        $server['argv'] = $argv;

        $runner = Runner::fromGlobals($server);

        $container = new Container();
        $container->add('runner', $runner);
        $container->add('pdo', $pdo);

        $command = new AbstractorCommand($container);

        $this->assertSame($expectedShouldRun, $command->shouldRun($runner->getCommand()));

        $this->assertSame($expectedArgs, $runner->getCommandArgs());

    }

    public function providerGetCommandArgs() {
            $server = [];
            $server['PWD']                                            = '/home/michael/php/tools';
            $server['argc']                                           =   5;
            $server['REQUEST_TIME_FLOAT']                             =   1539603021.8844;
            $server['REQUEST_TIME']                                   =   1539603021;
            $server['USERNAME']                                       =   'michael';

            return  [ [ $server, [ "index.php", "abstract", "arg1", "arg2", "arg3" ]     , true  , ["arg1", "arg2", "arg3"] ]
                    , [ $server, [ "index.php", "abstract" ]                             , true  , []                       ]
                    , [ $server, [ "index.php", "commandx" ]                             , false , []                       ]
                    , [ $server, [ "index.php" ]                                         , false , []                       ]
                    ];
    }

    /**
     * @param $server
     * @param $argv
     * @param $expected
     * @param $expectedCommand
     * @dataProvider providerTestShouldRun
     */
    public function testLoadCommands($server, $argv, $expected, $expectedCommand) {
        $pdo = $this->createMock(PDO::class);
        $server['argv'] = $argv;

        $runner = Runner::fromGlobals($server);

        $container = new Container();
        $container->add('pdo', $pdo);
        $container->add(AbstractorCommand::class)->addArgument($container);

        $runner->setContainer($container);
        $this->assertInstanceOf(Container::class, $runner->container);
        $this->assertInstanceOf(AbstractorCommand::class, $runner->container->get(AbstractorCommand::class));

        $runner->loadCommands($container);

        //$this->assertCount(1, $runner->commands);
    }

    /**
     * @param $server
     * @param $argv
     * @param $expected
     * @param $expectedCommand
     * @dataProvider providerTestShouldRun
     */
    public function testRun($server, $argv, $expected, $expectedCommand) {
        $pdo = $this->createMock(PDO::class);
        $server['argv'] = $argv;
        $runner = Runner::fromGlobals($server);
        $container = new Container();
        $container->add('runner', $runner);
        $container->add('pdo', $pdo);
        $container->add(AbstractorCommand::class)->addArgument($container);
        $runner->loadCommands($container);
        $runner->run($container);
    }

    public function testSetContainer() {
        $runner = new Runner();
        $container = new Container();
        $runner->setContainer($container);
        $this->assertInstanceOf(Container::class, $runner->container);
    }
}