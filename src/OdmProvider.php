<?php

namespace Skel;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

class OdmProvider implements \Pimple\ServiceProviderInterface
{
    public function register(\Pimple\Container $pimple)
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

        if(!isset($pimple['odm.proxy.namespace'])){
            $pimple['odm.proxy.namespace'] = 'OdmProxy';
        }

        if(!isset($pimple['odm.hydrator.namespace'])){
            $pimple['odm.hydrator.namespace'] = 'OdmHydrator';
        }

        if(!isset($pimple['odm.default_repository_class'])){
            $pimple['odm.default_repository_class'] = 'Doctrine\ODM\MongoDB\DocumentRepository';
        }

        if(!isset($pimple['odm.class_metadata_factory_name'])){
            $pimple['odm.class_metadata_factory_name'] = 'Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory';
        }

        if(!isset($pimple['odm.document.paths'])){
            $pimple['odm.document.paths'] = function($app){
                return new \ArrayObject([
                    $app['src.path'] . '/Document'
                ]);
            };
        }

        $pimple['odm.proxy'] = function($app){
            return $app['tmp.path'] . '/odm/'.$app['odm.proxy.namespace'];
        };

        $pimple['odm.hydrator'] = function($app){
            return $app['tmp.path'] . '/odm/'.$app['odm.hydrator.namespace'];
        };

        $pimple['odm.config'] = function($app){
            $config = new Configuration();
            $config->setProxyDir($app['odm.proxy']);
            $config->setProxyNamespace($app['odm.proxy.namespace']);
            $config->setHydratorDir($app['odm.hydrator']);
            $config->setHydratorNamespace($app['odm.hydrator.namespace']);
            $config->setDefaultDB($app['mongodb.name']);
            $config->setAutoGenerateHydratorClasses(true);
            $config->setAutoGenerateHydratorClasses(true);
            $config->setAutoGeneratePersistentCollectionClasses(true);
            $config->setDefaultRepositoryClassName($app['odm.default_repository_class']);
            $config->setClassMetadataFactoryName($app['odm.class_metadata_factory_name']);
            $config->setMetadataDriverImpl(
                AnnotationDriver::create(
                    $app['odm.document.paths']
                )
            );

            return $config;
        };

        $pimple['dm'] = function($app){
            AnnotationDriver::registerAnnotationClasses();
            return DocumentManager::create(
                $app['mongodb.connection'],
                $app['odm.config']
            );
        };

        $pimple['dr'] = function($app){
            return new Odm\RepositoryService($app['dm'], $app['dispatcher']);
        };
    }
}