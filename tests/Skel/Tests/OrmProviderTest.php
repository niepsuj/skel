<?php

namespace Skel\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Skel\ConfigProvider;
use Skel\ConsoleProvider;
use Skel\Orm\RepositoryService;
use Skel\OrmProvider;
use Skel\ProjectProvider;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;

class OrmProviderTest extends TestCase
{
    public function testRegistry()
    {
        $app = new Application([
            'tmp.path'      => TEST_ROOT_PATH.'/Fixtures/tmp',
            'vendor.path'   => realpath(TEST_ROOT_PATH.'/..').'/vendor',
            'src.path'      => TEST_ROOT_PATH.'/Fixtures/src',
            'loader'        => \TestData::$loader
        ]);

        $app->register(new DoctrineServiceProvider, [
            'db.options'    => [
                "driver"    => 	  "pdo_mysql",
                "host"      => 	  "localhost",
                "dbname"    => 	  "skel_lab",
                "user"      => 	  "skel_lab",
                "password"  =>    "skel_lab",
                "charset"   => 	  "utf8mb4"
            ]
        ]);
        $app->register(new OrmProvider);

        $this->assertArrayHasKey('orm.connection', $app);
        $this->assertEquals('default', $app['orm.connection']);

        $this->assertArrayHasKey('orm.proxy.namespace', $app);
        $this->assertEquals('EntityProxy', $app['orm.proxy.namespace']);

        $this->assertArrayHasKey('orm.entity.namespace', $app);
        $this->assertEquals('Entity', $app['orm.entity.namespace']);

        $this->assertArrayHasKey('orm.entity.paths', $app);
        $this->assertTrue($app['orm.entity.paths'] instanceof \ArrayObject);
        $this->assertEquals(TEST_ROOT_PATH.'/Fixtures/src/Entity', $app['orm.entity.paths'][0]);

        $this->assertArrayHasKey('orm.proxy', $app);
        $this->assertEquals(
            TEST_ROOT_PATH.'/Fixtures/tmp/orm/EntityProxy',
            $app['orm.proxy']
        );

        $this->assertArrayHasKey('orm.cache', $app);
        $this->assertTrue($app['orm.cache'] instanceof ArrayCache);

        $this->assertArrayHasKey('orm.reader', $app);
        $this->assertTrue($app['orm.reader'] instanceof AnnotationReader);

        $this->assertArrayHasKey('orm.driver', $app);
        $this->assertTrue($app['orm.driver'] instanceof AnnotationDriver);

        $this->assertArrayHasKey('validator.mapping.class_metadata_factory', $app);
        $this->assertTrue(
            $app['validator.mapping.class_metadata_factory']
            instanceof LazyLoadingMetadataFactory
        );

        $this->assertArrayHasKey('orm.config', $app);
        $this->assertTrue($app['orm.config'] instanceof Configuration);

        $this->assertArrayHasKey('em', $app);
        $this->assertTrue($app['em'] instanceof EntityManager);

        $this->assertArrayHasKey('er', $app);
        $this->assertTrue($app['er'] instanceof RepositoryService);
    }

    private static function createAppication()
    {
        $app = new Application([
            'loader' => \TestData::$loader
        ]);
        $app->register(new ProjectProvider, [
            'root.path' => TEST_ROOT_PATH.'/Fixtures',
            'vendor.path' => TEST_ROOT_PATH.'/../vendor'
        ]);

        $app->register(new ConfigProvider);
        $app->register(new DoctrineServiceProvider, [
            'db.options' => $app['config']->load('database')
        ]);

        $app->register(new OrmProvider);
        return $app;
    }

    public function testRegistryRepository()
    {
        $app = self::createAppication();
        $this->assertTrue($app['er']->test instanceof EntityRepository);
    }

    public function testRegistryConsole()
    {
        $app = self::createAppication();
        $app->register(new ConsoleProvider);

        $app->boot();
        $app['console.app']->addCommands(
            $app['controllers']->flushCommands()
        );

        $command = $app['console.app']->find('orm:schema-tool:create');
        $this->assertTrue($command instanceof CreateCommand);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--dump-sql'    => true
        ]);

        $this->assertContains('CREATE TABLE test', $commandTester->getDisplay());
    }
}