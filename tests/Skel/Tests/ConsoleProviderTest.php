<?php

namespace Skel\Tests;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Application\ConsoleTrait;
use Skel\ConfigProvider;
use Skel\Console\ApplicationHelper;
use Skel\Console\Command;
use Skel\Console\ControllerCollection;
use Skel\Console\Helper;
use Skel\ConsoleProvider;
use Skel\ProjectProvider;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ConsoleProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Application([
            'name' => 'test',
            'version' => 1
        ]);
        $app->register(new ConsoleProvider);

        $this->assertArrayHasKey('console.env', $app);
        $this->assertEquals(PHP_SAPI === 'cli', $app['console.env']);

        $this->assertArrayHasKey('console.app', $app);
        $this->assertTrue($app['console.app'] instanceof ConsoleApplication);

        $this->assertArrayHasKey('command_class', $app);
        $this->assertEquals('Skel\\Console\\Command', $app['command_class']);

        $this->assertTrue($app['controllers_factory'] instanceof ControllerCollection);

        $this->assertArrayHasKey('console', $app);
        $this->assertTrue($app['console'] instanceof Helper);
    }

    public function testCommand()
    {
        $app = new ConsoleTestApplication();
        $app->command(
            'test -flag|f --option|o {argument}',
            function(
                $flag, $option, $argument,
                OutputInterface $output
            ){
                $this->assertTrue($flag);
                $this->assertEquals('test', $option);
                $this->assertEquals('test', $argument);
                $output->writeln('ok');
            }
        );

        $app->boot();
        $app['console.app']->addCommands(
            $app['controllers']->flushCommands()
        );

        $command = $app['console.app']->find('test');
        $this->assertTrue($command instanceof Command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'   => $command->getName(),
            '--flag'    => true,
            '--option' => 'test',
            'argument'  => 'test'
        ]);

        $this->assertContains('ok', $commandTester->getDisplay());
    }

    public function testCommandCleanup()
    {
        $app = new Application();
        $app->register(new ProjectProvider);
        $app->register(new ConfigProvider);
        $app->register(new ConsoleProvider);

        $app->boot();
        $app['console.app']->addCommands(
            $app['controllers']->flushCommands()
        );

        $command = $app['console.app']->find('cleanup');
        $this->assertTrue($command instanceof Command);
        $this->assertEquals('cleanup', $command->getName());
        $this->assertContains('config', $command->getHelp());
    }

    public function testCommandCallbackResolver()
    {
        $app = new ConsoleTestApplication();
        $app['test'] = function(){
            return new ConsoleTestService();
        };

        $app->command('test', 'test:test');

        $app->boot();
        $app['console.app']->addCommands(
            $app['controllers']->flushCommands()
        );

        $command = $app['console.app']->find('test');
        $this->assertTrue($command instanceof Command);
        $this->assertEquals('test', $command->getName());
    }

    public function testApplicationHelper()
    {
        $app = new ConsoleTestApplication();
        $app->boot();

        /** @var HelperSet $helperSet */
        $helperSet = $app['console.app']->getHelperSet();

        $this->assertTrue($helperSet->has('application'), 'Has application helper in console helperset');
        $this->assertTrue(
            $helperSet->get('application') instanceof ApplicationHelper);

        $this->assertTrue($helperSet->has('app'), 'Has app alias to application helper in console helperset');

        /** @var ApplicationHelper $helper */
        $helper = $helperSet->get('app');
        $this->assertTrue($helper instanceof ApplicationHelper);
        $this->assertTrue($helper->getApplication() instanceof ConsoleTestApplication);

        $this->assertEquals($app['version'], $helper['version']);
        $helper['test_key'] = 1;
        $this->assertEquals($app['test_key'], $helper['test_key']);
    }
}

class  ConsoleTestApplication extends Application
{
    use ConsoleTrait;

    public function __construct()
    {
        parent::__construct([
            'name' => 'test',
            'version' => 1
        ]);
        $this->register(new ConsoleProvider);


    }
}

class  ConsoleTestService extends Application
{
    public function test()
    {

    }
}