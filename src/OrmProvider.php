<?php

namespace Skel;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache as Cache;
use Doctrine\DBAL\Tools\Console\ConsoleRunner as DBALConsoleRunner;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner as ORMConsoleRunner;
use Entity\Tool\DispatcherInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Skel\Event\ConsoleApplicationEvent;
use Skel\Event\OrmRepositoryEvent;
use Skel\Orm\RepositoryService;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;

class OrmProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        if(!isset($pimple['tmp.path'])){
            throw new \Exception('Missing tmp.path');
        }

        if(!isset($pimple['vendor.path'])){
            throw new \Exception('Missing vendor.path');
        }

        if(!isset($pimple['src.path'])){
            throw new \Exception('Missing src.path');
        }

        if(!isset($pimple['loader'])){
            throw new \Exception(
                'Missing composer loader object '.PHP_EOL.
                        '$loader = require_once \'vendor/autoload.php\';'.PHP_EOL.
                        '$app = new Application([\'loader\' => $loader])'
            );
        }

        AnnotationRegistry::registerAutoloadNamespace(
            "Doctrine\\ORM\\Mapping",
            $pimple['vendor.path'].'/doctrine/orm/lib/Doctrine/ORM'
        );

        AnnotationRegistry::registerAutoloadNamespace(
            "Symfony\\Component\\Validator\\Constraints",
            $pimple['vendor.path'].'/symfony/validator'
        );

        AnnotationRegistry::registerAutoloadNamespace(
            "Validator",
            $pimple['src.path']
        );

        if(!isset($pimple['orm.connection'])){
            $pimple['orm.connection'] = 'default';
        }

        if(!isset($pimple['orm.proxy.namespace'])){
            $pimple['orm.proxy.namespace'] = 'EntityProxy';
        }

        if(!isset($pimple['orm.entity.namespace'])){
            $pimple['orm.entity.namespace'] = 'Entity';
        }

        if(!isset($pimple['orm.entity.paths'])){
            $pimple['orm.entity.paths'] = function($app){
                return new \ArrayObject([
                    $app['src.path'].'/Entity'
                ]);
            };
        }

        $pimple['orm.proxy'] = function($app){
            return $app['tmp.path'] . '/orm/'.$app['orm.proxy.namespace'];
        };

        $pimple['orm.cache'] = function(){
            return new Cache();
        };

        $pimple['orm.reader'] = function($app){
            AnnotationRegistry::registerLoader([$app['loader'], 'loadClass']);
            return new AnnotationReader();
        };

        $pimple['orm.driver'] = function($app){
            return new AnnotationDriver(
                $app['orm.reader'],
                (array) $app['orm.entity.paths']
            );
        };

        $pimple['validator.mapping.class_metadata_factory'] = function ($app){
            return new LazyLoadingMetadataFactory(
                new LoaderChain([
                    new AnnotationLoader($app['orm.reader']),
                    new StaticMethodLoader()
                ])
            );
        };

        $pimple['orm.config'] = function($app){
            $config = new Configuration();
            $config->setQueryCacheImpl($app['orm.cache']);
            $config->setProxyDir($app['orm.proxy']);
            $config->setProxyNamespace($app['orm.proxy.namespace']);
            $config->setAutoGenerateProxyClasses(true);
            $config->setMetadataCacheImpl($app['orm.cache']);
            $config->setMetadataDriverImpl($app['orm.driver']);

            return $config;
        };

        $pimple['em'] = function($app){
            return EntityManager::create(
                $app['dbs'][$app['orm.connection']],
                $app['orm.config']
            );
        };

        $pimple['er'] = function($app){
            return new RepositoryService($app['em'], $app['dispatcher']);
        };

        /**
         * @var Application $pimple
         */
        $pimple->on(ProjectEvents::CONSOLE_APPLICATION,
            function(ConsoleApplicationEvent $event) use ($pimple){
                $event
                    ->getApplication()
                    ->setCatchExceptions(true);

                $event
                    ->getApplication()
                    ->getHelperSet()
                    ->set(new EntityManagerHelper($pimple['em']), 'em');

                $event
                    ->getApplication()
                    ->getHelperSet()
                    ->set(new ConnectionHelper($pimple['em']->getConnection()), 'conn');

                DBALConsoleRunner::addCommands($event->getApplication());
                ORMConsoleRunner::addCommands($event->getApplication());

                return $event;
            }
        );

        $pimple->on( ProjectEvents::ORM_GET_REPOSITORY,
            function(OrmRepositoryEvent $event) use ($pimple){
                $repository = $event->getRepository();
                if($repository instanceof DispatcherInterface){
                    $repository->setDispatcher($pimple['dispatcher']);
                }
            }
        );
    }
}