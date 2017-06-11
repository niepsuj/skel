<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Skel\Event\CleanupEvent;
use Skel\ProjectEvents;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // Default values
        $pimple['cache.namespace'] = function($app){
            if(!isset($app['name'])){
                throw new \Exception('Missing application name <$app[\'name\']>');
            }

            return $app['name'];
        };
        $pimple['cache.lifetime'] = 0;
        $pimple['cache.version'] = function($app){
            if(!isset($app['version'])){
                throw new \Exception('Missing application version <$app[\'version\']>');
            }
        };

        $pimple['cache.adapter'] = 'acpu';
        $pimple['cache.used'] = false;

        // ACPu Adapter
        $pimple['cache.acpu.namespace'] = function($app){ return $app['cache.namespace']; };
        $pimple['cache.acpu.lifetime'] = function($app){ return $app['cache.lifetime']; };
        $pimple['cache.acpu.version'] = function($app){ return $app['cache.version']; };
        $pimple['cache.apcu'] = function($app){
            return new ApcuAdapter(
                $app['cache.acpu.namespace'],
                $app['cache.acpu.lifetime'],
                $app['cache.acpu.version']
            );
        };

        // Array Adapter
        $pimple['cache.array.lifetime'] = function($app){ return $app['cache.lifetime']; };
        $pimple['cache.array.storeSerialized'] = true;
        $pimple['cache.array'] = function($app){
            return new ArrayAdapter(
                $app['cache.array.lifetime'],
                $app['cache.array.storeSerialized']
            );
        };

        // Filesystem Adapter
        $pimple['cache.filesystem.namespace'] = function($app){ return $app['cache.namespace']; };
        $pimple['cache.filesystem.lifetime'] = function($app){ return $app['cache.lifetime']; };
        $pimple['cache.filesystem.directory'] = function($app){
            if(!isset($app['tmp.path'])){
                throw new \Exception('tmp.path not defined');
            }

            return $app['tmp.path'];
        };

        $pimple['cache.filesystem'] = function($app){
            return new FilesystemAdapter(
                $app['cache.filesystem.namespace'],
                $app['cache.filesystem.lifetime'],
                $app['cache.filesystem.directory']
            );
        };

        // Memcached Adapter
        $pimple['cache.memcached.connection'] = 'memcached://localhost';
        $pimple['cache.memcached.namespace'] = function($app){ return $app['cache.namespace']; };
        $pimple['cache.memcached.lifetime'] = function($app){ return $app['cache.lifetime']; };
        $pimple['cache.memcached'] = function($app){
            return new MemcachedAdapter(
                MemcachedAdapter::createConnection(
                    $app['cache.memcached.connection']
                ),
                $app['cache.memcached.namespace'],
                $app['cache.memcached.lifetime']
            );
        };

        // Redis Adapter
        $pimple['cache.redis.connection'] = 'redis://localhost';
        $pimple['cache.redis.namespace'] = function($app){ return $app['cache.namespace']; };
        $pimple['cache.redis.lifetime'] = function($app){ return $app['cache.lifetime']; };
        $pimple['cache.redis'] = function($app){
            return new RedisAdapter(
                RedisAdapter::createConnection(
                    $app['cache.redis.connection']
                ),
                $app['cache.redis.namespace'],
                $app['cache.redis.lifetime']
            );
        };

        $pimple['cache'] = function($app){
            $app['cache.used'] = true;
            $name = 'cache.'.$app['cache.adapter'];
            if(!isset($app[$name])){
                throw new \Exception('Invalid adapter name <'.$app['cache.adapter'].'>');
            }

            return $app[$name];
        };

        /** @var Application $pimple */
        $pimple->on(KernelEvents::TERMINATE, function() use ($pimple){
             if($pimple['cache.used']){
                 $pimple['cache']->commit();
             }
        });

        if(isset($pimple['cleanup.scope'])){
            $pimple['cleanup.scope'][] = 'cache';

            $pimple->on(ProjectEvents::CLEANUP, function(CleanupEvent $cleanupEvent) use ($pimple){
                if($cleanupEvent->can('cache')){
                    $cleanupEvent->report(
                        'cache',
                        $pimple['cache']->clear()
                    );
                }
            });
        }
    }
}