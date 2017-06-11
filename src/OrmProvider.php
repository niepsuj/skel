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
use Provider\Orm\RepositoryEvent;
use Provider\Orm\RepositoryService;
use Silex\Application;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;

class OrmProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['tmp.path'] = function(){

        };

        if(!isset($pimple['tmp.path'])){
            throw new \Exception('Missing tmp.path');
        }

        if(!isset($pimple['vendor.path'])){
            $pimple['vendor.path'] = __DIR__.'/../../../vendor';
        }

        if(!isset($pimple['src.path'])){
            $pimple['src.path'] = __DIR__ .'/..';
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
            $pimple['orm.entity.paths'] = [
                $pimple['src.path'].'/Entity'
            ];
        }

        $pimple['orm.proxy'] = function() use ($pimple){
            return $pimple['tmp.path'] . '/orm/'.$pimple['orm.proxy.namespace'];
        };

        $pimple['orm.cache'] = function(){
            return new Cache();
        };

        $pimple['orm.reader'] = function() use ($pimple){
            AnnotationRegistry::registerLoader([$pimple['loader'], 'loadClass']);
            return new AnnotationReader();
        };

        $pimple['orm.driver'] = function() use ($pimple){
            return new AnnotationDriver(
                $pimple['orm.reader'],
                $pimple['orm.entity.paths']
            );
        };

        $pimple['validator.mapping.class_metadata_factory'] = function () use ($pimple) {
            return new LazyLoadingMetadataFactory(
                new LoaderChain([
                    new AnnotationLoader($pimple['orm.reader']),
                    new StaticMethodLoader()
                ])
            );
        };

        $pimple['orm.config'] = function() use ($pimple){
            $config = new Configuration();
            $config->setQueryCacheImpl($pimple['orm.cache']);
            $config->setProxyDir($pimple['orm.proxy']);
            $config->setProxyNamespace($pimple['orm.proxy.namespace']);
            $config->setAutoGenerateProxyClasses(true);
            $config->setMetadataCacheImpl($pimple['orm.cache']);
            $config->setMetadataDriverImpl($pimple['orm.driver']);

            return $config;
        };

        $pimple['em'] = function() use ($pimple){
            return EntityManager::create(
                $pimple['dbs'][$pimple['orm.connection']],
                $pimple['orm.config']
            );
        };

        $pimple['er'] = function() use ($pimple){
            return new RepositoryService($pimple['em'], $pimple['dispatcher']);
        };

        /**
         * @var Application $pimple
         */
        $pimple->on('console.app', function($event) use ($pimple){
            $event['app']->setCatchExceptions(true);
            $event['app']->getHelperSet()
                ->set(new EntityManagerHelper($pimple['em']), 'em');
            $event['app']->getHelperSet()
                ->set(new ConnectionHelper($pimple['em']->getConnection()), 'conn');

            DBALConsoleRunner::addCommands($event['app']);
            ORMConsoleRunner::addCommands($event['app']);

            return $event;
        });

        $pimple->on('get.repository', function(RepositoryEvent $event) use ($pimple){
            $repository = $event->getRepository();
            if($repository instanceof DispatcherInterface){
                $repository->setDispatcher($pimple['dispatcher']);
            }
        });
    }
}